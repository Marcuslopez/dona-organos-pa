<?php

namespace App\Http\Controllers;

use App\Contracts\IdentityProvider;
use App\Http\Middleware\EnsureDonorSessionIsActive;
use App\Http\Requests\VerifyIdentityRequest;
use App\Mail\DonorAccessCodeMail;
use App\Services\DonorCardService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IdentityVerificationController extends Controller
{
    public function create(): View
    {
        $this->ensureCaptchaExists();

        return view('registration.identity');
    }

    public function captchaImage(): Response
    {
        $this->ensureCaptchaExists();
        $code = (string) session('identity_captcha_code');
        $characters = str_split($code);
        $text = '';

        foreach ($characters as $index => $character) {
            $x = 26 + ($index * 34);
            $y = 43 + (($index % 2) * 5);
            $rotation = ($index % 2 === 0 ? -7 : 8);
            $text .= "<text x=\"{$x}\" y=\"{$y}\" transform=\"rotate({$rotation} {$x} {$y})\">{$character}</text>";
        }

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="235" height="65" viewBox="0 0 235 65" role="img" aria-label="Código CAPTCHA">
            <rect width="235" height="65" rx="12" fill="#eef4ff"/>
            <path d="M8 47 C55 8, 108 68, 227 18 M5 19 C63 56, 151 2, 230 46" fill="none" stroke="#88a9e8" stroke-width="2" opacity=".55"/>
            <g fill="#1d1b84" font-family="monospace" font-size="31" font-weight="700">{$text}</g>
            <circle cx="42" cy="14" r="2" fill="#087f7d"/><circle cx="151" cy="52" r="2" fill="#087f7d"/><circle cx="205" cy="13" r="2" fill="#087f7d"/>
        </svg>
        SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function refreshCaptcha(Request $request): JsonResponse
    {
        $this->generateCaptcha();

        return response()->json([
            'image_url' => route('registration.captcha.image', ['v' => now()->timestamp]),
        ]);
    }

    public function store(VerifyIdentityRequest $request, IdentityProvider $provider): RedirectResponse
    {
        $request->session()->forget(['identity_captcha_code', 'identity_captcha_hash']);
        $key = $this->throttleKey($request->string('document_number')->toString(), $request->ip());
        $this->ensureIsNotRateLimited($request, $key);

        $documentNumber = $request->string('document_number')->toString();

        $documentCode = $request->string('document_code')->toString();
        $documentCodeFingerprint = hash_hmac('sha256', $documentCode, (string) config('app.key'));
        $donor = DB::table('donors')->where('document_number', $documentNumber)->first(['id', 'status', 'document_code_hash', 'email', 'first_name']);
        $donorCodeKey = $donor ? 'donor-email-code|'.$donor->id.'|'.$request->ip() : null;
        if ($donorCodeKey && RateLimiter::tooManyAttempts($donorCodeKey, 1)) {
            $seconds = RateLimiter::availableIn($donorCodeKey);
            throw ValidationException::withMessages(['document_code' => "Por seguridad, espera {$seconds} segundos antes de volver a intentarlo."]);
        }
        $identityMatches = $provider->verify($documentNumber, $documentCode);

        $codeBelongsToAnotherDocument = DB::table('donors')
            ->where('document_code_fingerprint', $documentCodeFingerprint)
            ->where('document_number', '!=', $documentNumber)
            ->exists();
        $identityMatches = $identityMatches && ! $codeBelongsToAnotherDocument;

        if ($donor !== null) {
            $identityMatches = $identityMatches
                && filled($donor->document_code_hash)
                && Hash::check($documentCode, $donor->document_code_hash);
        }

        if (! $identityMatches) {
            RateLimiter::hit($key, config('identity.lockout_seconds'));

            if (RateLimiter::attempts($key) >= config('identity.max_attempts')) {
                $this->throwRateLimitedException($request, $key);
            }

            throw ValidationException::withMessages([
                'document_code' => 'No fue posible validar los datos ingresados.',
            ]);
        }

        RateLimiter::clear($key);

        $donorStatus = $donor?->status;
        $request->session()->put('identity_verification', [
            'document_number' => $documentNumber,
            'verified_at' => now()->timestamp,
            'donor_status' => $donorStatus,
            'donor_id' => $donor?->id,
            'donor_name' => $donor?->first_name,
            'document_code_hash' => $donor === null ? Hash::make($documentCode) : null,
            'document_code_fingerprint' => $donor === null ? $documentCodeFingerprint : null,
        ]);
        $request->session()->put([
            EnsureDonorSessionIsActive::STARTED_AT_KEY => now()->timestamp,
            EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY => now()->timestamp,
        ]);
        $request->session()->regenerate();

        if ($donor !== null) {
            $accessToken = Str::random(80);
            DB::table('donors')->where('id', $donor->id)->update(['active_access_token' => $accessToken]);
            $request->session()->put('identity_verification.active_access_token', $accessToken);
            $this->issueDonorAccessCode($donor);
        }

        return redirect()->route($donorStatus === null
            ? 'registration.form'
            : 'registration.email-code.create');
    }

    public function createEmailCode(Request $request): View|RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        if (! is_array($verification) || ! filled($verification['donor_id'] ?? null)) {
            return redirect()->route('registration.identity');
        }

        $maskedEmail = Str::mask((string) DB::table('donors')->where('id', $verification['donor_id'])->value('email'), '*', 2, 99);

        return view('registration.email-code', compact('verification', 'maskedEmail'));
    }

    public function verifyEmailCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']], [
            'code.digits' => 'El código debe contener exactamente seis dígitos.',
        ]);
        $verification = $request->session()->get('identity_verification');
        $donorId = is_array($verification) ? (int) ($verification['donor_id'] ?? 0) : 0;
        $record = $donorId ? DB::table('donor_access_codes')->where('donor_id', $donorId)->first() : null;
        $valid = $record && ! $record->consumed_at && $record->attempts < config('access_security.donor.code_max_attempts') && Hash::check($data['code'], $record->code_hash);

        if (! $valid) {
            if ($record && ! $record->consumed_at) {
                DB::table('donor_access_codes')->where('id', $record->id)->increment('attempts');
                $attempts = $record->attempts + 1;
                if ($attempts >= (int) config('access_security.donor.code_max_attempts')) {
                    $request->session()->forget('identity_verification');
                    RateLimiter::hit('donor-email-code|'.$donorId.'|'.$request->ip(), max(1, (int) config('access_security.donor.lockout_seconds')));
                    return redirect()->route('registration.identity')->withErrors(['document_code' => 'No fue posible verificar el código. Intenta nuevamente en 30 segundos.']);
                }
            }

            return back()->withErrors(['code' => 'El código no es válido o ya no puede utilizarse.']);
        }

        DB::table('donor_access_codes')->where('id', $record->id)->update(['consumed_at' => now(), 'updated_at' => now()]);
        $request->session()->put('identity_verification.email_verified_at', now()->timestamp);

        return redirect()->route('registration.identity.verified');
    }

    public function resendEmailCode(Request $request): RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $donor = is_array($verification) && filled($verification['donor_id'] ?? null)
            ? DB::table('donors')->where('id', $verification['donor_id'])->first(['id', 'email', 'first_name'])
            : null;

        if (! $donor) return redirect()->route('registration.identity');

        $record = DB::table('donor_access_codes')->where('donor_id', $donor->id)->first();
        $availableAt = $record?->last_sent_at
            ? Carbon::parse($record->last_sent_at)->addSeconds((int) config('access_security.donor.code_resend_after'))
            : null;
        $wait = $availableAt && now()->lt($availableAt) ? now()->diffInSeconds($availableAt) : 0;
        if ($wait > 0) return back()->withErrors(['code' => "Podrás solicitar otro código en {$wait} segundos."]);

        $this->issueDonorAccessCode($donor);

        return back()->with('status', 'Enviamos un nuevo código. El anterior ya no es válido.');
    }

    public function verified(DonorCardService $cardService): View|RedirectResponse
    {
        $verification = session('identity_verification');

        if (! is_array($verification)) {
            session()->forget('identity_verification');

            return redirect()->route('registration.identity')->withErrors([
                'document_number' => 'La validación expiró. Ingresa nuevamente los datos.',
            ]);
        }

        $donor = DB::table('donors')
            ->where('document_number', $verification['document_number'])
            ->first(['id', 'first_name', 'status', 'withdrawn_at']);
        $verification['donor_status'] = $donor?->status;
        session()->put('identity_verification.donor_status', $verification['donor_status']);

        $card = null;
        $cardPrintUrl = null;
        $withdrawnCard = null;

        if ($donor?->status === 'active') {
            $card = $cardService->find((int) $donor->id);
            if ($card && $card['is_active']) {
                $cardPrintUrl = URL::temporarySignedRoute(
                    'registration.card.print',
                    now()->addMinutes(15),
                    ['donor' => (int) $donor->id],
                );
            }
        } elseif ($donor?->status === 'withdrawn') {
            $withdrawnCard = DB::table('donor_cards')
                ->where('donor_id', $donor->id)
                ->whereNotNull('revoked_at')
                ->orderByDesc('revoked_at')
                ->orderByDesc('id')
                ->first(['folio', 'revoked_at']);
        }

        return view('registration.identity-verified', compact('verification', 'donor', 'card', 'cardPrintUrl', 'withdrawnCard'));
    }

    private function ensureIsNotRateLimited(VerifyIdentityRequest $request, string $key): void
    {
        if (! RateLimiter::tooManyAttempts($key, config('identity.max_attempts'))) {
            return;
        }

        event(new Lockout($request));
        $this->throwRateLimitedException($request, $key);
    }

    private function issueDonorAccessCode(object $donor): void
    {
        $code = (string) random_int(100000, 999999);
        DB::table('donor_access_codes')->updateOrInsert(
            ['donor_id' => $donor->id],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => null,
                'consumed_at' => null,
                'last_sent_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        Mail::to($donor->email)->send(new DonorAccessCodeMail((string) ($donor->first_name ?? ''), $code));
    }

    private function throwRateLimitedException(VerifyIdentityRequest $request, string $key): never
    {
        $seconds = RateLimiter::availableIn($key);
        $request->session()->flash('identity_retry_after', $seconds);

        throw ValidationException::withMessages([
            'document_code' => "Demasiados intentos. Intenta nuevamente en {$seconds} segundos.",
        ]);
    }

    private function throttleKey(string $documentNumber, ?string $ip): string
    {
        return 'identity|'.Str::lower($documentNumber).'|'.$ip;
    }

    private function ensureCaptchaExists(): void
    {
        if (! session()->has('identity_captcha_code')) {
            $this->generateCaptcha();
        }
    }

    private function generateCaptcha(): void
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $code = collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');

        session()->put([
            'identity_captcha_code' => $code,
            'identity_captcha_hash' => hash('sha256', $code),
        ]);
    }
}
