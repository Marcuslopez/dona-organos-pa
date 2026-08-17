<?php

namespace Database\Seeders;

use App\Models\SimulatedIdentity;
use Illuminate\Database\Seeder;
use RuntimeException;

class SimulatedIdentitySeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') || ! config('identity.allow_test_identities')) {
            throw new RuntimeException('Las identidades simuladas no están habilitadas en este ambiente.');
        }

        SimulatedIdentity::query()->updateOrCreate(
            ['document_number' => '8-123-1234'],
            [
                'document_type' => 'cedula',
                'document_code_hash' => '88NNNN00012',
                'is_active' => true,
            ],
        );
    }
}
