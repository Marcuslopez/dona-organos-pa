<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterDonorRequest;
use App\Mail\DonorCardMail;
use App\Services\DonorCardService;
use App\Services\DonorRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DonorRegistrationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        if (DB::table('donors')->where('document_number', $verification['document_number'])->exists()) {
            return redirect()->route('registration.identity.verified');
        }

        $catalogs = $this->catalogs();
        $defaults = [];
        $isReactivation = false;
        $isUpdate = false;

        return view('registration.form', compact('verification', 'catalogs', 'defaults', 'isReactivation', 'isUpdate'));
    }

    public function reactivationForm(Request $request): View|RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $donor = DB::table('donors')->where('document_number', $verification['document_number'])->first();

        if (! $donor || $donor->status !== 'withdrawn') {
            return redirect()->route('registration.identity.verified');
        }

        $defaults = $this->donorDefaults($donor);
        $catalogs = $this->catalogs();
        $isReactivation = true;
        $isUpdate = false;

        return view('registration.form', compact('verification', 'catalogs', 'defaults', 'isReactivation', 'isUpdate'));
    }

    public function updateForm(Request $request): View|RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $donor = DB::table('donors')->where('document_number', $verification['document_number'])->first();

        if (! $donor || $donor->status !== 'active') {
            return redirect()->route('registration.identity.verified');
        }

        // Once the verified donor starts this potentially lengthy form, its
        // submission remains available without extending access to other flows.
        $request->session()->put('identity_verification.active_form_flow', 'update');

        $defaults = $this->donorDefaults($donor);
        $catalogs = $this->catalogs();
        $isReactivation = false;
        $isUpdate = true;

        return view('registration.form', compact('verification', 'catalogs', 'defaults', 'isReactivation', 'isUpdate'));
    }

    public function update(RegisterDonorRequest $request, DonorRegistrationService $service): RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $result = $service->update($request->validated(), $verification['document_number'], $request);

        $result['email_sent'] = false;
        $result['masked_email'] = Str::mask($request->validated('email'), '*', 2, max(strlen($request->validated('email')) - 6, 1));
        if ($result['card_reissued']) {
            try {
                $card = app(DonorCardService::class)->find((int) $result['donor_id']);
                if ($card) {
                    Mail::to($request->validated('email'))->send(new DonorCardMail($card));
                    $result['email_sent'] = true;
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $result['completion_type'] = 'update';
        $request->session()->forget('identity_verification');
        $request->session()->put('completed_registration', $result);

        return redirect()->route('registration.completed');
    }

    public function reactivate(RegisterDonorRequest $request, DonorRegistrationService $service): RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $result = $service->reactivate($request->validated(), $verification['document_number'], $request);

        $result['email_sent'] = false;
        $result['masked_email'] = Str::mask($request->validated('email'), '*', 2, max(strlen($request->validated('email')) - 6, 1));
        try {
            $card = app(DonorCardService::class)->find((int) $result['donor_id']);
            if ($card) {
                Mail::to($request->validated('email'))->send(new DonorCardMail($card));
                $result['email_sent'] = true;
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $request->session()->forget('identity_verification');
        $request->session()->put('completed_registration', $result);

        return redirect()->route('registration.completed');
    }

    public function store(RegisterDonorRequest $request, DonorRegistrationService $service): RedirectResponse
    {
        $verification = $request->session()->get('identity_verification');
        $result = $service->register(
            $request->validated(),
            $verification['document_number'],
            $verification['document_code_hash'],
            $verification['document_code_fingerprint'],
            $request,
        );

        $result['email_sent'] = false;
        $result['masked_email'] = Str::mask($request->validated('email'), '*', 2, max(strlen($request->validated('email')) - 6, 1));
        try {
            $card = app(DonorCardService::class)->find((int) $result['donor_id']);
            if ($card) {
                Mail::to($request->validated('email'))->send(new DonorCardMail($card));
                $result['email_sent'] = true;
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $request->session()->forget('identity_verification');
        $request->session()->put('completed_registration', $result);

        return redirect()->route('registration.completed');
    }

    public function completed(Request $request, DonorCardService $cardService): View|RedirectResponse
    {
        $registration = $request->session()->pull('completed_registration');
        if (! is_array($registration)) {
            return redirect()->route('registration.identity');
        }

        $card = $cardService->find((int) $registration['donor_id']);
        $cardPrintUrl = URL::temporarySignedRoute(
            'registration.card.print',
            now()->addMinutes(30),
            ['donor' => (int) $registration['donor_id']],
        );

        return view('registration.completed', compact('registration', 'card', 'cardPrintUrl'));
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $request->validate(['confirm_withdrawal' => ['accepted']]);
        $verification = $request->session()->get('identity_verification');

        $withdrawn = DB::transaction(function () use ($verification, $request): bool {
            $donor = DB::table('donors')->where('document_number', $verification['document_number'])->lockForUpdate()->first();
            if (! $donor || $donor->status !== 'active') {
                return false;
            }

            $now = now();
            DB::table('donors')->where('id', $donor->id)->update(['status' => 'withdrawn', 'withdrawn_at' => $now, 'updated_at' => $now]);
            DB::table('consents')->where('donor_id', $donor->id)->whereNull('revoked_at')->update([
                'revoked_at' => $now,
                'revocation_reason' => 'Baja voluntaria solicitada por el donante.',
                'updated_at' => $now,
            ]);
            DB::table('donor_cards')->where('donor_id', $donor->id)->whereNull('revoked_at')->update(['revoked_at' => $now, 'updated_at' => $now]);
            DB::table('donor_status_history')->insert([
                'donor_id' => $donor->id,
                'previous_status' => 'active',
                'new_status' => 'withdrawn',
                'reason' => 'Baja voluntaria solicitada por el donante.',
                'source' => 'donor',
                'changed_by_user_id' => null,
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return true;
        });

        $request->session()->forget('identity_verification');

        return $withdrawn
            ? redirect()->route('registration.withdrawn')
            : redirect()->route('registration.identity')->withErrors(['document_number' => 'No existe un registro activo para dar de baja.']);
    }

    private function activeCatalog(string $table): mixed
    {
        return DB::table($table)->where('is_active', true)->orderBy('sort_order')->get();
    }

    private function catalogs(): array
    {
        return [
            'genders' => $this->activeCatalog('genders'),
            'relationships' => $this->activeCatalog('relationships'),
            'provinces' => DB::table('provinces')->where('is_active', true)->orderBy('name')->get(),
            'districts' => DB::table('districts')->where('is_active', true)->orderBy('name')->get(),
            'corregimientos' => DB::table('corregimientos')->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function donorDefaults(object $donor): array
    {
        $contacts = DB::table('donor_contacts')->where('donor_id', $donor->id)->orderByDesc('is_primary')->orderBy('id')->get();
        $defaults = (array) $donor;
        $defaults['contacts'] = $contacts->map(fn (object $contact): array => (array) $contact)->all();

        return $defaults;
    }
}
