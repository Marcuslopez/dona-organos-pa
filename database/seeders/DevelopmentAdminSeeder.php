<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DevelopmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El administrador de prueba solo puede crearse en ambientes local o testing.');
        }

        User::query()->updateOrCreate(
            ['email' => 'administrador1@admin.com'],
            [
                'name' => 'Administrador1',
                'password' => 'Administrator',
                'role' => 'administrator',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
