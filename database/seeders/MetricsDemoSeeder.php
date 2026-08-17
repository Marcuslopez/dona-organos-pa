<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class MetricsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->assertSafeToRun();

        $count = (int) config('demo-data.records');
        $maximum = (int) config('demo-data.maximum_records');
        $months = (int) config('demo-data.months');

        if ($count < 1 || $count > $maximum) {
            throw new RuntimeException("DEMO_DATA_RECORDS debe estar entre 1 y {$maximum}.");
        }
        if ($months < 1 || $months > 12) {
            throw new RuntimeException('DEMO_DATA_MONTHS debe estar entre 1 y 12.');
        }

        $catalogs = $this->catalogs();
        $faker = FakerFactory::create('es_ES');
        $faker->seed((int) config('demo-data.seed'));
        mt_srand((int) config('demo-data.seed'));

        DB::transaction(function () use ($count, $catalogs, $faker): void {
            $sequence = DB::table('folio_sequences')->lockForUpdate()->first();
            if (! $sequence) {
                $sequenceId = DB::table('folio_sequences')->insertGetId(['last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
                $sequence = DB::table('folio_sequences')->where('id', $sequenceId)->first();
            }

            $lastFolio = (int) $sequence->last_number;
            $currentMoment = CarbonImmutable::now(config('app.timezone'));
            $donorPlans = $this->donorPlans($currentMoment);

            if (count($donorPlans) !== $count) {
                throw new RuntimeException('DEMO_DATA_RECORDS debe ser '.count($donorPlans).' para la distribución demostrativa configurada.');
            }

            for ($index = 1; $index <= $count; $index++) {
                $registeredAt = $donorPlans[$index]['registered_at'];
                $age = mt_rand(18, 100);
                $birthDate = $registeredAt->subYears($age)->subDays(mt_rand(0, 364))->toDateString();
                $gender = $catalogs['genders'][array_rand($catalogs['genders'])];
                $place = $catalogs['places'][array_rand($catalogs['places'])];
                $withdrawnAt = $donorPlans[$index]['withdrawn_at'];
                $withdrawn = $withdrawnAt !== null;
                $firstName = $gender->code === 'female' ? $faker->firstNameFemale() : $faker->firstNameMale();
                $middleName = $index % 3 === 0 ? $faker->firstName() : null;
                $firstLastName = $faker->lastName();
                $secondLastName = $index % 2 === 0 ? $faker->lastName() : null;
                $fullName = implode(' ', array_filter([$firstName, $middleName, $firstLastName, $secondLastName]));
                $document = sprintf('D-%04d-%05d', (int) config('demo-data.seed') % 10000, $index);
                $documentCode = strtoupper(substr(hash('sha256', config('demo-data.seed').'|'.$index), 0, 12));
                $now = now();

                $donorId = DB::table('donors')->insertGetId([
                    'document_type' => 'cedula_demo', 'document_number' => $document,
                    'document_code_hash' => Hash::make($documentCode),
                    'document_code_fingerprint' => hash_hmac('sha256', $documentCode, (string) config('app.key')),
                    'full_name' => $fullName, 'first_name' => $firstName, 'middle_name' => $middleName,
                    'first_last_name' => $firstLastName, 'second_last_name' => $secondLastName,
                    'birth_date' => $birthDate, 'gender_id' => $gender->id,
                    'email' => sprintf('metricas-%04d@example.test', $index),
                    'phone' => sprintf('%04d-%04d', 6000 + ($index % 3000), 1000 + ($index % 8000)),
                    'province_id' => $place->province_id, 'district_id' => $place->district_id,
                    'corregimiento_id' => $place->corregimiento_id,
                    'status' => $withdrawn ? 'withdrawn' : 'active', 'is_demo' => true,
                    'registered_at' => $registeredAt, 'withdrawn_at' => $withdrawnAt,
                    'created_at' => $registeredAt, 'updated_at' => $withdrawnAt ?? $registeredAt,
                ]);

                $this->insertContacts($donorId, $index, $registeredAt, $catalogs['relationships'], $faker);
                DB::table('donation_preferences')->insert([
                    'donor_id' => $donorId,
                    'donation_scope_id' => $catalogs['scopes'][$index % count($catalogs['scopes'])]->id,
                    'research_authorized' => $index % 3 !== 0,
                    'created_at' => $registeredAt, 'updated_at' => $registeredAt,
                ]);
                DB::table('consents')->insert([
                    'donor_id' => $donorId, 'version' => '1.0', 'signed_name' => $fullName,
                    'voluntary_accepted' => true, 'electronically_accepted' => true,
                    'sensitive_data_authorized' => true, 'institutional_query_authorized' => true,
                    'cornea_information_acknowledged' => true, 'accepted_at' => $registeredAt,
                    'request_id' => (string) Str::uuid(), 'ip_address' => '127.0.0.1',
                    'user_agent' => 'MetricsDemoSeeder', 'revoked_at' => $withdrawnAt,
                    'created_at' => $registeredAt, 'updated_at' => $withdrawnAt ?? $registeredAt,
                ]);

                foreach ($catalogs['questions'] as $question) {
                    DB::table('donor_health_answers')->insert([
                        'donor_id' => $donorId, 'health_question_id' => $question->id,
                        'health_answer_option_id' => $catalogs['answers'][($index + $question->id) % count($catalogs['answers'])]->id,
                        'created_at' => $registeredAt, 'updated_at' => $registeredAt,
                    ]);
                }

                $lastFolio++;
                $folio = 'CD-'.str_pad((string) $lastFolio, 7, '0', STR_PAD_LEFT);
                DB::table('donor_cards')->insert([
                    'donor_id' => $donorId, 'folio' => $folio,
                    'public_token_hash' => hash('sha256', "demo|{$donorId}|{$folio}|".config('demo-data.seed')),
                    'issued_at' => $registeredAt, 'revoked_at' => $withdrawnAt,
                    'created_at' => $registeredAt, 'updated_at' => $withdrawnAt ?? $registeredAt,
                ]);
                DB::table('donor_status_history')->insert([
                    'donor_id' => $donorId, 'previous_status' => null, 'new_status' => 'active',
                    'reason' => 'Registro demostrativo inicial.', 'source' => 'system',
                    'request_id' => (string) Str::uuid(), 'changed_at' => $registeredAt,
                    'created_at' => $registeredAt, 'updated_at' => $registeredAt,
                ]);
                if ($withdrawn) {
                    DB::table('donor_status_history')->insert([
                        'donor_id' => $donorId, 'previous_status' => 'active', 'new_status' => 'withdrawn',
                        'reason' => 'Baja demostrativa.', 'source' => 'system',
                        'request_id' => (string) Str::uuid(), 'changed_at' => $withdrawnAt,
                        'created_at' => $withdrawnAt, 'updated_at' => $withdrawnAt,
                    ]);
                }
            }

            DB::table('folio_sequences')->where('id', $sequence->id)->update(['last_number' => $lastFolio, 'updated_at' => now()]);
        }, 3);
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function donorPlans(CarbonImmutable $currentMoment): array
    {
        $activeDaysByMonthOffset = [
            11 => [3 => 1, 7 => 5, 11 => 3, 14 => 2, 18 => 6, 21 => 1, 24 => 4, 27 => 8, 30 => 12],
            10 => [],
            9 => [4 => 1, 9 => 4, 13 => 2, 19 => 6, 23 => 3, 28 => 5],
            8 => [2 => 3, 5 => 1, 8 => 7, 11 => 2, 14 => 6, 17 => 4, 20 => 1, 23 => 9, 26 => 5, 28 => 8, 31 => 12],
            7 => [3 => 1, 7 => 5, 12 => 2, 16 => 7, 20 => 3, 24 => 1, 28 => 4, 31 => 6],
            6 => [],
            5 => [2 => 4, 5 => 1, 8 => 6, 12 => 3, 15 => 8, 18 => 2, 21 => 1, 24 => 7, 27 => 5, 29 => 4, 31 => 12],
            4 => [4 => 1, 9 => 3, 13 => 2, 18 => 5, 23 => 1, 29 => 2],
            3 => [],
            2 => [1 => 2, 4 => 1, 8 => 6, 11 => 4, 15 => 8, 18 => 3, 21 => 1, 24 => 5, 27 => 7, 30 => 11],
            1 => [],
            0 => [1 => 1, 3 => 4, 5 => 2, 7 => 7, 9 => 3, 10 => 1, 11 => 5, 12 => 4, 13 => 2, 14 => 6],
        ];
        $withdrawalDaysByMonthOffset = [
            11 => [5, 12, 19, 27], // Septiembre 2025
            10 => [],              // Octubre 2025
            9 => [8, 21],          // Noviembre 2025
            8 => [3, 9, 14, 20, 25, 30], // Diciembre 2025
            7 => [7, 16, 28],      // Enero 2026
            6 => [],               // Febrero 2026
            5 => [4, 10, 17, 23, 29], // Marzo 2026
            4 => [18],             // Abril 2026
            3 => [],               // Mayo 2026
            2 => [5, 12, 19, 26], // Junio 2026
            1 => [],               // Julio 2026
            0 => [3, 11],          // Agosto 2026
        ];
        $plans = [];

        foreach ($activeDaysByMonthOffset as $monthOffset => $dailyTotals) {
            $month = $currentMoment->startOfMonth()->subMonths($monthOffset);
            foreach ($dailyTotals as $day => $total) {
                for ($position = 0; $position < $total; $position++) {
                    $registeredAt = $month->day($day)->setTime(7 + ($position % 12), ($position * 7) % 60, ($position * 13) % 60);
                    if ($registeredAt->greaterThan($currentMoment)) {
                        $registeredAt = $currentMoment->subMinutes($position + 1);
                    }
                    $plans[] = ['registered_at' => $registeredAt, 'withdrawn_at' => null];
                }
            }
        }

        $usedPlanIndexes = [];
        foreach ($withdrawalDaysByMonthOffset as $monthOffset => $days) {
            $month = $currentMoment->startOfMonth()->subMonths($monthOffset);
            foreach ($days as $position => $day) {
                $target = $month->day($day)->setTime(18, $position, 0);
                if ($target->greaterThan($currentMoment)) {
                    throw new RuntimeException("La fecha de baja {$target->format('d/m/Y')} todavía no ha ocurrido.");
                }

                $selectedIndex = collect($plans)
                    ->keys()
                    ->reject(fn (int $index): bool => isset($usedPlanIndexes[$index]))
                    ->filter(fn (int $index): bool => $plans[$index]['registered_at']->format('Y-m') === $month->format('Y-m'))
                    ->sortBy(fn (int $index): int => (int) abs($plans[$index]['registered_at']->startOfDay()->diffInSeconds($target->startOfDay(), false)))
                    ->first();

                if ($selectedIndex === null) {
                    throw new RuntimeException("No existe un alta demo cercana para la baja del {$target->format('d/m/Y')}.");
                }

                $usedPlanIndexes[$selectedIndex] = true;
                $plans[$selectedIndex]['withdrawn_at'] = $target->greaterThan($plans[$selectedIndex]['registered_at'])
                    ? $target
                    : $plans[$selectedIndex]['registered_at']->addHour();
            }
        }

        return collect($plans)->values()->mapWithKeys(
            fn (array $plan, int $index): array => [$index + 1 => $plan]
        )->all();
    }

    private function assertSafeToRun(): void
    {
        if (! config('demo-data.enabled')) {
            throw new RuntimeException('La carga demo está deshabilitada. Configure DEMO_DATA_ENABLED=true de forma explícita.');
        }
        if (! Schema::hasColumn('donors', 'is_demo')) {
            throw new RuntimeException('La migración donors.is_demo no está aplicada.');
        }
        if (DB::table('donors')->where('is_demo', true)->exists()) {
            throw new RuntimeException('Ya existen datos demostrativos. Ejecute demo:status antes de continuar.');
        }
        if (! in_array(config('mail.default'), ['array', 'log'], true) && app()->environment('production')) {
            throw new RuntimeException('En producción la carga demo exige bloquear el correo saliente.');
        }
    }

    private function catalogs(): array
    {
        $catalogs = [
            'genders' => DB::table('genders')->where('is_active', true)->get()->all(),
            'relationships' => DB::table('relationships')->where('is_active', true)->get()->all(),
            'scopes' => DB::table('donation_scopes')->where('is_active', true)->get()->all(),
            'answers' => DB::table('health_answer_options')->where('is_active', true)->get()->all(),
            'questions' => DB::table('health_questions')->where('is_active', true)->get()->all(),
            'places' => DB::table('corregimientos')->join('districts', 'districts.id', '=', 'corregimientos.district_id')
                ->where('corregimientos.is_active', true)->where('districts.is_active', true)
                ->select('corregimientos.id as corregimiento_id', 'districts.id as district_id', 'districts.province_id')->get()->all(),
        ];

        foreach ($catalogs as $name => $items) {
            if ($items === []) {
                throw new RuntimeException("El catálogo {$name} no tiene datos.");
            }
        }

        return $catalogs;
    }

    private function insertContacts(int $donorId, int $index, CarbonImmutable $registeredAt, array $relationships, mixed $faker): void
    {
        $contactCount = $index % 3 === 0 ? 2 : 1;
        for ($position = 1; $position <= $contactCount; $position++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            DB::table('donor_contacts')->insert([
                'donor_id' => $donorId, 'relationship_id' => $relationships[($index + $position) % count($relationships)]->id,
                'full_name' => "{$firstName} {$lastName}", 'first_name' => $firstName,
                'middle_name' => null, 'first_last_name' => $lastName, 'second_last_name' => null,
                'email' => sprintf('contacto-%04d-%d@example.test', $index, $position),
                'phone' => sprintf('%04d-%04d', 6100 + ($index % 2000), 2000 + $position + ($index % 7000)),
                'is_informed' => $index % 2 === 0, 'is_primary' => $position === 1,
                'created_at' => $registeredAt, 'updated_at' => $registeredAt,
            ]);
        }
    }
}
