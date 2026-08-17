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

        $this->seedCatalog('donation_scopes', [
            ['corneas_only', 'Solo córneas'],
            ['organs_and_tissues', 'Órganos y tejidos'],
        ], $now);

        $this->seedCatalog('health_answer_options', [
            ['yes', 'Sí'],
            ['no', 'No'],
            ['unknown', 'No sé'],
        ], $now);

        DB::table('health_questions')->upsert([
            ['code' => 'infectious_diseases', 'questionnaire_version' => '1.0', 'text' => '¿Ha sido diagnosticado con VIH, hepatitis B/C o sífilis?', 'sort_order' => 1, 'is_required' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'systemic_cancer', 'questionnaire_version' => '1.0', 'text' => '¿Padece o ha padecido algún tipo de cáncer sistémico o leucemia?', 'sort_order' => 2, 'is_required' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'corneal_conditions', 'questionnaire_version' => '1.0', 'text' => '¿Padece glaucoma avanzado, infecciones corneales previas o queratocono?', 'sort_order' => 3, 'is_required' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'eye_surgeries', 'questionnaire_version' => '1.0', 'text' => '¿Ha tenido cirugías oculares previas?', 'sort_order' => 4, 'is_required' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['code'], ['questionnaire_version', 'text', 'sort_order', 'is_required', 'is_active', 'updated_at']);
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
