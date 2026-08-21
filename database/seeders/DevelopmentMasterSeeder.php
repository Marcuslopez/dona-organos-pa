<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DevelopmentMasterSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El usuario master de prueba solo puede crearse en ambientes local o testing.');
        }

        User::query()->updateOrCreate(
            ['email' => env('DEVELOPMENT_MASTER_EMAIL', 'master1@admin.com')],
            [
                'name' => env('DEVELOPMENT_MASTER_NAME', 'Master1'),
                'password' => env('DEVELOPMENT_MASTER_PASSWORD', 'MasterAdministrator1'),
                'role' => 'master',
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
