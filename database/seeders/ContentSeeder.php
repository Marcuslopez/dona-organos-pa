<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    private const LAW_URL = 'https://s3-legispan.asamblea.gob.pa/legispan/NORMAS/2010/2010/LEY/Administrador%20Legispan_26468-B_2010_2_10_ASAMBLEA%20NACIONAL_3.pdf';

    public function run(): void
    {
        $now = now();
        $contents = [
            $this->content('legal_001', 'legal', 'Acto altruista y gratuito', 'La donación es voluntaria, gratuita y no admite compensación económica.', 1, self::LAW_URL),
            $this->content('legal_002', 'legal', 'Consentimiento presunto', 'La normativa regula cómo se manifiesta o se rechaza la voluntad de donar.', 2, self::LAW_URL),
            $this->content('legal_003', 'legal', 'Tipos de donantes', 'Existen dos tipos de donantes: vivos y fallecidos. La viabilidad de cada órgano o tejido se determina mediante evaluación médica.', 3),
            $this->content('legal_004', 'legal', 'Muerte irreversible', 'La muerte encefálica es irreversible y solo puede ser diagnosticada por médicos certificados independientes no vinculados al equipo de trasplantes.', 4),
            $this->content('legal_005', 'legal', 'Prioridad: salvar la vida', 'El objetivo principal del personal médico es preservar la vida. Solo al confirmarse el cese irreversible de funciones se valora la opción de la donación.', 5),
            $this->content('legal_006', 'legal', 'Toda persona puede ser considerada', 'Cualquier persona puede ser considerada como donante de órganos, independientemente de su edad, género o estado de salud, sujeto a una estricta evaluación médica.', 6),

            $this->content('myth_001', 'myth', 'Mito: "Si soy donante, el equipo médico no me va a salvar la vida"', 'El personal médico de urgencias tiene como meta absoluta salvar tu vida. El equipo de trasplantes es independiente y solo interviene cuando se certifica la muerte encefálica de forma irreversible.', 1),
            $this->content('myth_002', 'myth', 'Mito: "Pueden empezar la extracción antes de estar realmente muerto"', 'La extracción jamás inicia antes del deceso legal. Se exigen rigurosas pruebas clínicas y médicos certificados para validar el cese total e irreversible de las funciones cerebrales.', 2),
            $this->content('myth_003', 'myth', 'Mito: "Hay gente que se ha despertado después de la muerte encefálica"', 'La muerte encefálica es médicamente definitiva e irreversible. No existen casos de recuperación tras este diagnóstico clínico formal.', 3),
            $this->content('myth_004', 'myth', 'Mito: "Después de la donación, el cuerpo queda desfigurado"', 'La remoción se efectúa quirúrgicamente con respeto. No deforma el rostro ni altera la anatomía externa, posibilitando un velatorio habitual.', 4),
            $this->content('myth_005', 'myth', 'Mito: "Hay personas que desaparecen y les falta un órgano"', 'Los trasplantes requieren compatibilidad biológica, quirófanos, preservación controlada y equipos especializados. Es impracticable realizarlos clandestinamente.', 5),
            $this->content('myth_006', 'myth', 'Mito: "Ser famoso o tener dinero permite trasplantarte más rápido"', 'La asignación se rige por criterios médicos objetivos: urgencia, compatibilidad y antigüedad en la lista de espera. El nivel económico no influye.', 6),
            $this->content('myth_007', 'myth', 'Mito: "Las religiones se oponen a la donación"', 'La mayoría de las religiones apoya la donación y la considera un acto de solidaridad y amor al prójimo.', 7),
            $this->content('myth_008', 'myth', 'Mito: "Soy demasiado mayor para ser donante"', 'Cualquier persona puede ser considerada. La viabilidad se determina tras una evaluación médica individual.', 8),
            $this->content('myth_009', 'myth', 'Mito: "Mi estado de salud no es bueno para donar"', 'Pocas condiciones inhabilitan toda donación. La decisión sobre la viabilidad corresponde exclusivamente al equipo médico.', 9),
            $this->content('myth_010', 'myth', 'Mito: "Se extraen órganos aunque la familia se oponga"', 'La normativa contempla mecanismos para comunicar formalmente la oposición. Conversar previamente con la familia ayuda a que se respete la decisión.', 10, self::LAW_URL),

            $this->content('faq_001', 'faq', '¿Qué órganos y tejidos se pueden donar?', 'Se pueden donar riñones, hígado, corazón, pulmones y páncreas, así como córneas, piel, válvulas cardíacas y tejido óseo. La viabilidad se determina médicamente.', 1),
            $this->content('faq_002', 'faq', '¿Qué pasa si tengo alguna enfermedad?', 'Cualquier persona puede manifestar su voluntad. Algunas patologías pueden afectar la viabilidad, pero esa decisión corresponde al equipo médico.', 2),
            $this->content('faq_003', 'faq', '¿La donación tiene algún costo para mi familia?', 'No. La donación es voluntaria, gratuita y solidaria; los costos de procuración no recaen sobre la familia donante.', 3),
            $this->content('faq_004', 'faq', '¿Se puede donar córnea si usaba lentes?', 'Sí. Haber utilizado anteojos, tener cataratas, cirugías previas o edad avanzada no impide automáticamente la donación de tejido corneal.', 4),
            $this->content('faq_005', 'faq', '¿Y si cambio de opinión?', 'La voluntad de donar puede modificarse o revocarse conforme al procedimiento establecido.', 5, self::LAW_URL),
            $this->content('faq_006', 'faq', '¿Qué pasa si mi familia no está de acuerdo?', 'Es importante hablar con los seres queridos para que conozcan y respeten la decisión. La normativa establece los mecanismos aplicables.', 6, self::LAW_URL),
            $this->content('faq_007', 'faq', '¿Puedo decidir qué órganos donar y cuáles no?', 'La manifestación de voluntad puede indicar los órganos o tejidos autorizados para donación.', 7, self::LAW_URL),
            $this->content('faq_008', 'faq', '¿Qué enfermedades impiden la donación de córnea?', 'Algunas infecciones activas y enfermedades transmisibles pueden impedirla. La viabilidad siempre debe ser certificada por especialistas.', 8),

            $this->content('story_001', 'story', 'M. P.', 'Gracias a un donante hoy puedo volver a ver. No tengo palabras para agradecer.', 1),
            $this->content('story_002', 'story', 'Laura Gómez', "Mi hija tiene una segunda oportunidad gracias a un donante anónimo.\n\nCuando nos dijeron que mi hija necesitaba un trasplante de corazón, sentí que el mundo se detenía. Fueron semanas de mucha incertidumbre, rezando por un milagro. Y ese milagro llegó: una familia, en medio de su propio dolor, decidió decir sí a la donación.\n\nHoy, mi hija corre, ríe y va al colegio como cualquier niña de su edad. Siempre estaremos agradecidos con esa persona y su familia. Donar salva vidas. No hay gesto más generoso y humano.", 2, null, 'Madre de paciente trasplantada'),
        ];

        $rows = array_map(fn (array $content) => [
            ...$content,
            'is_visible' => true,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $contents);

        DB::table('contents')->insertOrIgnore($rows);
    }

    private function content(
        string $seedKey,
        string $type,
        string $title,
        string $body,
        int $sortOrder,
        ?string $relatedUrl = null,
        ?string $subtitle = null,
    ): array {
        if ($type !== 'story' && $relatedUrl) {
            $url = htmlspecialchars($relatedUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $body .= '<div><a href="'.$url.'" target="_blank" rel="noopener noreferrer">Consultar información relacionada</a></div>';
            $relatedUrl = null;
        }

        return [
            'seed_key' => $seedKey,
            'type' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'body' => $body,
            'sort_order' => $sortOrder,
        ];
    }
}
