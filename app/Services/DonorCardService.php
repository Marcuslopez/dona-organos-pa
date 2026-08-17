<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\DB;

class DonorCardService
{
    public function find(int $donorId): ?array
    {
        $card = DB::table('donor_cards')
            ->join('donors', 'donors.id', '=', 'donor_cards.donor_id')
            ->select('donor_cards.*', 'donors.full_name', 'donors.first_name', 'donors.middle_name', 'donors.first_last_name', 'donors.second_last_name', 'donors.document_number', 'donors.registered_at', 'donors.status')
            ->where('donors.id', $donorId)
            ->orderByDesc('donor_cards.issued_at')
            ->orderByDesc('donor_cards.id')
            ->first();

        if (! $card) {
            return null;
        }

        $contacts = DB::table('donor_contacts')
            ->where('donor_id', $donorId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->limit(2)
            ->get(['full_name', 'first_name', 'middle_name', 'first_last_name', 'second_last_name', 'phone', 'is_informed']);
        $card->card_name = $card->full_name;
        $contacts->each(function (object $contact): void {
            $contact->card_name = $this->cardName($contact);
        });
        $token = $this->publicToken($donorId, $card->folio);
        $verificationUrl = rtrim((string) config('app.url'), '/')
            .route('cards.verify', ['token' => $token], false);

        $qrCode = new QrCode(
            data: $verificationUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 260,
            margin: 12,
            foregroundColor: new Color(21, 53, 109),
            backgroundColor: new Color(255, 255, 255),
        );

        return [
            'record' => $card,
            'contacts' => $contacts,
            'qr' => (new SvgWriter)->write($qrCode)->getDataUri(),
            'verification_url' => $verificationUrl,
            'is_active' => $card->status === 'active' && $card->revoked_at === null,
        ];
    }

    public function publicToken(int $donorId, string $folio): string
    {
        return hash_hmac('sha256', $donorId.'|'.$folio, (string) config('app.key'));
    }

    private function cardName(object $person): string
    {
        if (empty($person->first_name) || empty($person->first_last_name)) {
            return $person->full_name;
        }

        return collect([
            $person->first_name,
            $person->middle_name ? mb_substr($person->middle_name, 0, 1).'.' : null,
            $person->first_last_name,
            $person->second_last_name ? mb_substr($person->second_last_name, 0, 1).'.' : null,
        ])->filter()->implode(' ');
    }
}
