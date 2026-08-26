<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactInquiryAssignedMail;
use App\Mail\ContactInquiryResponseMail;
use App\Models\ContactInquiry;
use App\Models\ContactInquiryReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactInquiry::query()->with('assignee')->latest();
        $search = trim((string) $request->input('q'));

        if (!$request->user()->isMaster()) $query->where(fn ($q) => $q->whereNull('assigned_to')->orWhere('assigned_to', $request->user()->id));
        if ($search !== '') $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        if (in_array($request->input('status'), ContactInquiry::STATUSES, true)) $query->where('status', $request->input('status'));
        return view('admin.contact-inquiries.index', ['inquiries' => $query->paginate(10)->withQueryString(), 'search' => $search, 'status' => (string) $request->input('status')]);
    }

    public function show(Request $request, ContactInquiry $inquiry): View
    {
        $this->ensureVisible($request, $inquiry);
        $inquiry->load(['assignee', 'replies.author', 'history.actor']);
        return view('admin.contact-inquiries.show', ['inquiry' => $inquiry, 'administrators' => $request->user()->isMaster() ? User::query()->where('is_active', true)->whereIn('role', ['administrator', 'master'])->orderBy('name')->get() : collect()]);
    }

    public function take(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $actor = $request->user();
        $taken = DB::transaction(function () use ($inquiry, $actor) {
            $record = ContactInquiry::lockForUpdate()->findOrFail($inquiry->id);
            if ($record->assigned_to && $record->assigned_to !== $actor->id) return false;
            $before = $record->status;
            $record->update(['assigned_to' => $actor->id, 'assigned_at' => now(), 'status' => 'en_proceso']);
            $record->history()->create(['actor_id' => $actor->id, 'action' => 'taken', 'previous_status' => $before, 'current_status' => 'en_proceso']);
            return true;
        });
        return back()->with($taken ? 'success' : 'error', $taken ? 'La consulta fue asignada a tu cuenta.' : 'La consulta ya fue tomada por otro administrador.');
    }

    public function assign(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        abort_unless($request->user()->isMaster(), 403);
        $data = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);
        abort_unless(User::query()->whereKey($data['assigned_to'])->whereIn('role', ['administrator', 'master'])->where('is_active', true)->exists(), 422, 'Selecciona un administrador activo.');
        $actor = $request->user();
        $changed = DB::transaction(function () use ($inquiry, $data, $actor) {
            $record = ContactInquiry::lockForUpdate()->findOrFail($inquiry->id);
            if ((int) $record->assigned_to === (int) $data['assigned_to']) return null;
            $before = $record->status;
            $record->update(['assigned_to' => $data['assigned_to'], 'assigned_at' => now(), 'status' => $record->status === 'nueva' ? 'en_proceso' : $record->status]);
            $record->history()->create(['actor_id' => $actor->id, 'action' => 'assigned', 'previous_status' => $before, 'current_status' => $record->status, 'metadata' => ['assigned_to' => $data['assigned_to']]]);
            return $record->fresh('assignee');
        });
        if ($changed) {
            try { Mail::to($changed->assignee->email)->send(new ContactInquiryAssignedMail($changed)); } catch (\Throwable $e) { Log::warning('No se pudo notificar asignación de consulta.', ['id' => $changed->id]); }
        }
        return back()->with($changed ? 'success' : 'error', $changed ? 'La consulta fue asignada y el administrador fue notificado.' : 'La consulta ya estaba asignada a ese administrador.');
    }

    public function respond(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['response' => ['required', 'string', 'max:4000']]);
        $actor = $request->user();
        $reply = DB::transaction(function () use ($inquiry, $actor, $data) {
            $record = ContactInquiry::lockForUpdate()->findOrFail($inquiry->id);
            $this->ensureCanRespond($actor, $record);
            if (in_array($record->status, ['respondida', 'cerrada'], true)) abort(422, 'Esta consulta ya fue respondida o cerrada.');
            $before = $record->status;
            $reply = $record->replies()->create(['author_id' => $actor->id, 'body' => trim($data['response']), 'sent_at' => now()]);
            $record->update(['status' => 'respondida', 'responded_by' => $actor->id, 'responded_at' => now()]);
            $record->history()->create(['actor_id' => $actor->id, 'action' => 'responded', 'previous_status' => $before, 'current_status' => 'respondida']);
            return [$record->fresh(), $reply];
        });
        try { Mail::to($reply[0]->email)->send(new ContactInquiryResponseMail($reply[0], $reply[1])); } catch (\Throwable $e) { Log::warning('No se pudo enviar respuesta de consulta.', ['id' => $inquiry->id]); }
        return back()->with('success', 'La respuesta fue registrada y enviada al remitente.');
    }

    public function close(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $actor = $request->user();
        $closed = DB::transaction(function () use ($inquiry, $actor) {
            $record = ContactInquiry::lockForUpdate()->findOrFail($inquiry->id);
            $this->ensureCanHandle($actor, $record);
            if ($record->status !== 'respondida') return false;
            $record->update(['status' => 'cerrada', 'closed_by' => $actor->id, 'closed_at' => now()]);
            $record->history()->create(['actor_id' => $actor->id, 'action' => 'closed', 'previous_status' => 'respondida', 'current_status' => 'cerrada']);
            return true;
        });
        return back()->with($closed ? 'success' : 'error', $closed ? 'La consulta fue cerrada.' : 'Solo se pueden cerrar consultas respondidas.');
    }

    private function ensureVisible(Request $request, ContactInquiry $inquiry): void { if (!$request->user()->isMaster() && $inquiry->assigned_to && $inquiry->assigned_to !== $request->user()->id) abort(403); }
    private function ensureCanHandle(User $user, ContactInquiry $inquiry): void { if (!$user->isMaster() && $inquiry->assigned_to !== $user->id) abort(403); }
    private function ensureCanRespond(User $user, ContactInquiry $inquiry): void { if ($inquiry->assigned_to !== $user->id) abort(403, 'Solo el administrador responsable puede responder esta consulta.'); }
}
