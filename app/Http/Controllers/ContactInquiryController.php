<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactInquiryRequest;
use App\Mail\ContactInquiryReceivedMail;
use App\Mail\NewContactInquiryMail;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    private const PRIVACY_POLICY_VERSION = '2026-08-20';

    public function create(): View { return view('contact.create'); }

    public function store(StoreContactInquiryRequest $request): RedirectResponse
    {
        $inquiry = DB::transaction(function () use ($request) {
            $inquiry = ContactInquiry::create([
                'name' => $request->validated('name'), 'email' => $request->validated('email'), 'message' => $request->validated('message'),
                'privacy_accepted_at' => now(), 'privacy_policy_version' => self::PRIVACY_POLICY_VERSION,
                'requester_ip' => $request->ip(), 'user_agent' => $request->userAgent(),
            ]);
            $inquiry->history()->create(['action' => 'created', 'current_status' => 'nueva', 'metadata' => ['source' => 'public_contact_form']]);
            return $inquiry;
        });

        try {
            Mail::to('administrador1@admin.com')->send(new NewContactInquiryMail($inquiry));
            Mail::to($inquiry->email)->send(new ContactInquiryReceivedMail($inquiry));
        } catch (\Throwable $exception) {
            Log::error('No se pudo enviar una notificación de consulta.', ['contact_inquiry_id' => $inquiry->id, 'exception' => $exception->getMessage()]);
        }

        return redirect()->route('contact.create')->with('success', 'Tu consulta fue enviada correctamente. Recibirás una confirmación en tu correo electrónico.');
    }
}
