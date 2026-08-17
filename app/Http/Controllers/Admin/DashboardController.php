<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DonorCardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $name = trim((string) $request->query('nombre'));
        $document = trim((string) $request->query('cedula'));
        $province = (string) $request->query('provincia');
        $status = $request->query->has('estado')
            ? (string) $request->query('estado')
            : 'active';
        $dateFrom = (string) $request->query('desde');
        $dateTo = (string) $request->query('hasta');
        $perPage = in_array((int) $request->query('por_pagina'), [5, 10, 15, 20], true) ? (int) $request->query('por_pagina') : 5;

        $donors = $this->filteredDonors($name, $document, $province, $status, $dateFrom, $dateTo)
            ->orderByDesc('donors.registered_at')
            ->paginate($perPage)
            ->withQueryString();

        $provinces = DB::table('provinces')->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.dashboard', compact('donors', 'provinces', 'name', 'document', 'province', 'status', 'dateFrom', 'dateTo', 'perPage'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $name = trim((string) $request->query('nombre'));
        $document = trim((string) $request->query('cedula'));
        $province = (string) $request->query('provincia');
        $status = $request->query->has('estado') ? (string) $request->query('estado') : 'active';
        $dateFrom = (string) $request->query('desde');
        $dateTo = (string) $request->query('hasta');
        $filename = 'donantes-'.now()->timezone('America/Panama')->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($name, $document, $province, $status, $dateFrom, $dateTo): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nombre', 'Cédula', 'Correo', 'Provincia', 'Contacto', 'Correo contacto', 'Fecha de registro', 'Estado']);

            foreach ($this->filteredDonors($name, $document, $province, $status, $dateFrom, $dateTo)
                ->orderByDesc('donors.registered_at')->orderByDesc('donors.id')->cursor() as $donor) {
                fputcsv($output, array_map([$this, 'safeCsvValue'], [
                    $donor->full_name, $donor->document_number, $donor->email, $donor->province_name,
                    $donor->contact_name, $donor->contact_email,
                    Carbon::parse($donor->registered_at)->timezone('America/Panama')->format('d/m/Y'),
                    $donor->status === 'active' ? 'Activo' : 'Baja',
                ]));
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(int $donor, DonorCardService $cardService): View
    {
        $record = DB::table('donors')
            ->join('genders', 'genders.id', '=', 'donors.gender_id')
            ->join('provinces', 'provinces.id', '=', 'donors.province_id')
            ->join('districts', 'districts.id', '=', 'donors.district_id')
            ->join('corregimientos', 'corregimientos.id', '=', 'donors.corregimiento_id')
            ->select('donors.*', 'genders.name as gender_name', 'provinces.name as province_name', 'districts.name as district_name', 'corregimientos.name as corregimiento_name')
            ->where('donors.id', $donor)
            ->first();

        abort_if($record === null, 404);

        $contacts = DB::table('donor_contacts')
            ->join('relationships', 'relationships.id', '=', 'donor_contacts.relationship_id')
            ->select('donor_contacts.*', 'relationships.name as relationship_name')
            ->where('donor_id', $donor)->orderByDesc('is_primary')->get();
        $preference = DB::table('donation_preferences')
            ->join('donation_scopes', 'donation_scopes.id', '=', 'donation_preferences.donation_scope_id')
            ->select('donation_preferences.*', 'donation_scopes.name as scope_name')
            ->where('donor_id', $donor)->first();
        $healthAnswers = DB::table('donor_health_answers')
            ->join('health_questions', 'health_questions.id', '=', 'donor_health_answers.health_question_id')
            ->join('health_answer_options', 'health_answer_options.id', '=', 'donor_health_answers.health_answer_option_id')
            ->select('health_questions.text as question', 'health_answer_options.name as answer')
            ->where('donor_id', $donor)->orderBy('health_questions.sort_order')->get();
        $consent = DB::table('consents')->where('donor_id', $donor)->latest('accepted_at')->first();
        $card = DB::table('donor_cards')->where('donor_id', $donor)->orderByDesc('issued_at')->orderByDesc('id')->first();
        $cardView = $cardService->find($donor);

        return view('admin.donors.show', compact('record', 'contacts', 'preference', 'healthAnswers', 'consent', 'card', 'cardView'));
    }

    private function filteredDonors(string $name, string $document, string $province, string $status, string $dateFrom, string $dateTo): mixed
    {
        return DB::table('donors')
            ->leftJoin('provinces', 'provinces.id', '=', 'donors.province_id')
            ->leftJoin('donor_contacts', function ($join): void {
                $join->on('donor_contacts.donor_id', '=', 'donors.id')->where('donor_contacts.is_primary', true);
            })
            ->select([
                'donors.id', 'donors.full_name', 'donors.document_number', 'donors.email',
                'donors.status', 'donors.registered_at', 'provinces.name as province_name',
                'donor_contacts.full_name as contact_name', 'donor_contacts.email as contact_email',
            ])
            ->when($name !== '', fn ($query) => $query->where('donors.full_name', 'like', "%{$name}%"))
            ->when($document !== '', fn ($query) => $query->where('donors.document_number', 'like', "%{$document}%"))
            ->when(ctype_digit($province), fn ($query) => $query->where('donors.province_id', (int) $province))
            ->when(in_array($status, ['active', 'withdrawn'], true), fn ($query) => $query->where('donors.status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('donors.registered_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('donors.registered_at', '<=', $dateTo));
    }

    private function safeCsvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
