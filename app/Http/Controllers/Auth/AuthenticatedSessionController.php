<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSessionIsActive;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\AdminAccountLockedMail;
use App\Mail\AdminLoginCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        if (request()->boolean('reiniciar')) {
            request()->session()->forget('admin_login');
        }

        return view('auth.login');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']], [
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);
        $email = Str::lower(trim($data['email']));
        $user = User::query()->where('email', $email)->where('is_active', true)->first();

        $request->session()->forget('admin_login');

        if ($user && $this->isLocked($user)) {
            $seconds = now()->diffInSeconds($user->login_locked_until, false);

            return redirect()->route('login')->withErrors([
                'email' => "Esta cuenta está bloqueada temporalmente. Intenta nuevamente en {$seconds} segundos.",
            ]);
        }

        if ($user) {
            $this->sendLoginCode($user);
            $request->session()->put('admin_login', ['user_id' => $user->id, 'email' => $user->email, 'code_sent_at' => now()->timestamp]);
        }

        return redirect()->route('login')->with('status', 'Si el correo está autorizado, enviamos un código de verificación.');
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']], [
            'code.digits' => 'El código debe contener exactamente seis dígitos.',
        ]);
        $challenge = $request->session()->get('admin_login');
        $user = is_array($challenge) ? User::find($challenge['user_id'] ?? null) : null;
        $record = $user ? DB::table('admin_login_codes')->where('user_id', $user->id)->first() : null;

        if (! $user || ! $record || $record->consumed_at || $record->attempts >= config('access_security.admin.code_max_attempts')) {
            $request->session()->forget('admin_login');

            return back()->withErrors(['code' => 'El código no es válido o ya no puede utilizarse. Solicita uno nuevo.']);
        }

        if (! Hash::check($data['code'], $record->code_hash)) {
            $attempts = $record->attempts + 1;
            DB::table('admin_login_codes')->where('id', $record->id)->update([
                'attempts' => $attempts,
                'updated_at' => now(),
            ]);

            if ($attempts >= (int) config('access_security.admin.code_max_attempts')) {
                $this->lockAccount($user, 'código de verificación', $request);
                $request->session()->forget('admin_login');

                return redirect()->route('login')->withErrors([
                    'email' => 'El acceso fue bloqueado temporalmente por intentos fallidos. Intenta nuevamente en '.config('access_security.admin.lockout_seconds').' segundos.',
                ]);
            }

            $remaining = (int) config('access_security.admin.code_max_attempts') - $attempts;

            return back()->withErrors(['code' => "El código no es válido. Te quedan {$remaining} intento(s)."]);
        }

        DB::table('admin_login_codes')->where('id', $record->id)->update(['consumed_at' => now(), 'updated_at' => now()]);
        $request->session()->put('admin_login.code_verified_at', now()->timestamp);

        return redirect()->route('login');
    }

    public function resendCode(Request $request): RedirectResponse
    {
        $challenge = $request->session()->get('admin_login');
        $user = is_array($challenge) ? User::find($challenge['user_id'] ?? null) : null;
        $record = $user ? DB::table('admin_login_codes')->where('user_id', $user->id)->first() : null;

        if (! $user || ! $user->is_active || $this->isLocked($user)) {
            $request->session()->forget('admin_login');

            return redirect()->route('login');
        }

        $availableAt = $record?->last_sent_at
            ? Carbon::parse($record->last_sent_at)->addSeconds((int) config('access_security.admin.code_resend_after'))
            : null;
        $wait = $availableAt && now()->lt($availableAt) ? now()->diffInSeconds($availableAt) : 0;
        if ($wait > 0) {
            return back()->withErrors(['code' => "Podrás solicitar otro código en {$wait} segundos."]);
        }

        $this->sendLoginCode($user);
        $request->session()->put('admin_login.code_sent_at', now()->timestamp);

        return back()->with('status', 'Enviamos un nuevo código. El anterior ya no es válido.');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $challenge = $request->session()->get('admin_login');
        $user = is_array($challenge) ? User::find($challenge['user_id'] ?? null) : null;
        $verifiedAt = (int) ($challenge['code_verified_at'] ?? 0);
        $withinCodeWindow = $verifiedAt > 0;

        if (! $user || ! $withinCodeWindow || $user->email !== $request->validated('email') || ! $user->is_active) {
            $request->session()->forget('admin_login');
            throw ValidationException::withMessages(['email' => 'Confirma el código de acceso antes de ingresar la contraseña.']);
        }

        if ($this->isLocked($user)) {
            $seconds = now()->diffInSeconds($user->login_locked_until, false);
            $request->session()->forget('admin_login');

            return redirect()->route('login')->withErrors([
                'email' => "Esta cuenta está bloqueada temporalmente. Intenta nuevamente en {$seconds} segundos.",
            ]);
        }

        if (! Hash::check($request->validated('password'), $user->password)) {
            $attempts = $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];
            if ($attempts >= (int) config('access_security.admin.password_max_attempts')) {
                $this->lockAccount($user, 'contraseña', $request);
                $request->session()->forget('admin_login');

                return redirect()->route('login')->withErrors([
                    'email' => 'El acceso fue bloqueado temporalmente por intentos fallidos. Intenta nuevamente en '.config('access_security.admin.lockout_seconds').' segundos.',
                ]);
            } else {
                $user->forceFill($updates)->save();
            }
            throw ValidationException::withMessages(['password' => 'Las credenciales proporcionadas no son correctas.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill([
            'failed_login_attempts' => 0,
            'login_locked_until' => null,
            'active_session_id' => $request->session()->getId(),
            'last_login_at' => now(),
        ])->save();
        $request->session()->forget('admin_login');
        $request->session()->put([
            EnsureAdminSessionIsActive::STARTED_AT_KEY => now()->timestamp,
            EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => now()->timestamp,
        ]);
        $fallback = $request->user()->isMaster()
            ? route('admin.users.index')
            : route('admin.dashboard');

        return redirect()->intended($fallback);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user && $user->active_session_id === $request->session()->getId()) {
            $user->forceFill(['active_session_id' => null])->save();
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function isLocked(User $user): bool
    {
        return $user->login_locked_until?->isFuture() ?? false;
    }

    private function lockAccount(User $user, string $reason, Request $request): void
    {
        $lockedAt = now();
        $lockedUntil = $lockedAt->copy()->addSeconds(max(1, (int) config('access_security.admin.lockout_seconds')));
        $user->forceFill([
            'failed_login_attempts' => 0,
            'login_locked_at' => $lockedAt,
            'login_locked_until' => $lockedUntil,
            'login_lock_reason' => $reason,
        ])->save();

        User::query()->where('role', 'master')->where('is_active', true)->get()
            ->each(fn (User $master) => Mail::to($master->email)->send(new AdminAccountLockedMail($user, $reason)));

        DB::table('admin_login_codes')
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now(), 'updated_at' => now()]);

        DB::table('admin_user_audits')->insert([
            'actor_user_id' => null,
            'target_user_id' => $user->id,
            'action' => 'account_locked',
            'changes' => json_encode(['reason' => $reason, 'locked_until' => $lockedUntil->toDateTimeString()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => $lockedAt,
        ]);
    }

    private function sendLoginCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        DB::table('admin_login_codes')->updateOrInsert(
            ['user_id' => $user->id],
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
        Mail::to($user->email)->send(new AdminLoginCodeMail($user, $code));
    }
}
