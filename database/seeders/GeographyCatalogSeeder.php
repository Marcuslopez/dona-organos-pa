<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GeographyCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/panama-geography.json');
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $provinces = $data['provincia'] ?? null;

        if (! is_array($provinces)) {
            throw new RuntimeException('El archivo geográfico no contiene la colección provincia.');
        }

        DB::transaction(function () use ($provinces): void {
            foreach ($provinces as $provinceData) {
                $provinceName = $this->cleanName($provinceData['nombre']);
                DB::table('provinces')->updateOrInsert(
                    ['official_code' => $provinceData['iso_3166_2']],
                    [
                        'name' => $provinceName,
                        'type' => str_starts_with($provinceName, 'Comarca ') ? 'comarca' : 'province',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                $provinceId = DB::table('provinces')
                    ->where('official_code', $provinceData['iso_3166_2'])
                    ->value('id');

                foreach ($provinceData['distrito'] as $districtData) {
                    $districtName = $this->cleanName($districtData['nombre']);

                    DB::table('districts')->updateOrInsert(
                        ['province_id' => $provinceId, 'name' => $districtName],
                        ['is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    );

                    $districtId = DB::table('districts')
                        ->where('province_id', $provinceId)
                        ->where('name', $districtName)
                        ->value('id');

                    foreach ($districtData['corregimientos'] as $corregimiento) {
                        DB::table('corregimientos')->updateOrInsert(
                            ['district_id' => $districtId, 'name' => $this->cleanName($corregimiento)],
                            ['is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                        );
                    }
                }
            }
        });
    }

    private function cleanName(string $name): string
    {
        return trim((string) preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $name));
    }
}
