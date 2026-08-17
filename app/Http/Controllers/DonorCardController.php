<?php

namespace App\Http\Controllers;

use App\Services\DonorCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DonorCardController extends Controller
{
    public function registrationPrint(int $donor, DonorCardService $service): View
    {
        abort_unless(request()->hasValidSignature(), 403);
        $card = $this->activeCard($donor, $service);
        $pdfUrl = URL::temporarySignedRoute('registration.card.pdf', now()->addMinutes(10), ['donor' => $donor]);

        return view('cards.print-dialog', compact('card', 'pdfUrl'));
    }

    public function registrationPdf(int $donor, DonorCardService $service): Response
    {
        abort_unless(request()->hasValidSignature(), 403);

        return $this->pdf($donor, $service);
    }

    public function adminPrint(int $donor, DonorCardService $service): View
    {
        $card = $this->activeCard($donor, $service);
        $pdfUrl = route('admin.donors.card.pdf', $donor);

        return view('cards.print-dialog', compact('card', 'pdfUrl'));
    }

    public function adminPdf(int $donor, DonorCardService $service): Response
    {
        return $this->pdf($donor, $service);
    }

    public function verify(string $token): View
    {
        $card = DB::table('donor_cards')
            ->join('donors', 'donors.id', '=', 'donor_cards.donor_id')
            ->select('donor_cards.folio', 'donor_cards.issued_at', 'donor_cards.revoked_at', 'donors.status')
            ->where('donor_cards.public_token_hash', hash('sha256', $token))
            ->first();

        return view('cards.verify', ['card' => $card, 'valid' => $card && $card->status === 'active' && $card->revoked_at === null]);
    }

    private function pdf(int $donor, DonorCardService $service): Response
    {
        $card = $this->activeCard($donor, $service);
        $filename = 'carnet-'.$card['record']->folio.'-'.Str::slug($card['record']->full_name).'.pdf';

        $response = Pdf::loadView('cards.pdf', compact('card'))
            ->setPaper('letter', 'landscape')
            ->stream($filename);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function activeCard(int $donor, DonorCardService $service): array
    {
        $card = $service->find($donor);
        abort_if(! $card || ! $card['is_active'], 404);

        return $card;
    }
}
