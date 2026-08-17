<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('donor_cards')->orderBy('id')->each(function (object $card): void {
            $token = hash_hmac('sha256', $card->donor_id.'|'.$card->folio, (string) config('app.key'));
            DB::table('donor_cards')->where('id', $card->id)->update(['public_token_hash' => hash('sha256', $token)]);
        });
    }

    public function down(): void
    {
        // Los tokens anteriores no son recuperables; una reversión conserva los tokens vigentes.
    }
};
