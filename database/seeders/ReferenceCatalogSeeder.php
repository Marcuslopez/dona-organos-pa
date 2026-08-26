<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $this->seedCatalog('genders', [
            ['female', 'Femenino'],
            ['male', 'Masculino'],
            ['other', 'Otro'],
            ['not_disclosed', 'Prefiero no indicar'],
        ], $now);

        $this->seedCatalog('relationships', [
            ['sibling', 'Hermano(a)'],
            ['parent', 'Padre/Madre'],
            ['spouse', 'Cónyuge'],
            ['child', 'Hijo(a)'],
            ['friend', 'Amistad'],
            ['other', 'Otro'],
        ], $now);

    }

    private function seedCatalog(string $table, array $options, mixed $now): void
    {
        $rows = array_map(
            fn (array $option, int $index) => [
                'code' => $option[0],
                'name' => $option[1],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $options,
            array_keys($options),
        );

        DB::table($table)->upsert($rows, ['code'], ['name', 'sort_order', 'is_active', 'updated_at']);
    }
}
