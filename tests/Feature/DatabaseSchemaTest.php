<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_tables_and_catalogs_are_created(): void
    {
        $this->seed();

        foreach ([
            'genders', 'relationships', 'donation_scopes', 'health_answer_options',
            'provinces', 'districts', 'corregimientos', 'donors', 'donor_contacts',
            'donation_preferences', 'consents', 'health_questions',
            'donor_health_answers', 'donor_cards', 'contents', 'content_media',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }

        $this->assertDatabaseCount('genders', 4);
        $this->assertDatabaseCount('relationships', 6);
        $this->assertDatabaseCount('donation_scopes', 2);
        $this->assertDatabaseCount('health_answer_options', 3);
        $this->assertDatabaseCount('health_questions', 4);
        $this->assertDatabaseCount('provinces', 14);
        $this->assertDatabaseCount('districts', 83);
        $this->assertDatabaseCount('corregimientos', 702);
        $this->assertDatabaseHas('provinces', [
            'official_code' => 'PA-NT',
            'name' => 'Comarca Naso Tjër Di',
            'type' => 'comarca',
        ]);
        $this->assertDatabaseCount('contents', 26);
        foreach (['donors', 'donor_contacts'] as $table) {
            foreach (['first_name', 'middle_name', 'first_last_name', 'second_last_name'] as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Missing column: {$table}.{$column}");
            }
        }
        $this->assertTrue(Schema::hasColumn('donors', 'is_demo'));
        $this->assertTrue(Schema::hasColumn('contents', 'subtitle'));
        $this->assertFalse(Schema::hasColumn('contents', 'related_url'));
        foreach (['content_id', 'media_type', 'disk', 'path', 'mime_type', 'size_bytes', 'width', 'height', 'alt_text'] as $column) {
            $this->assertTrue(Schema::hasColumn('content_media', $column), "Missing column: content_media.{$column}");
        }
        $this->assertSame(6, DB::table('contents')->where('type', 'legal')->count());
        $this->assertSame(10, DB::table('contents')->where('type', 'myth')->count());
        $this->assertSame(8, DB::table('contents')->where('type', 'faq')->count());
        $this->assertSame(2, DB::table('contents')->where('type', 'story')->count());
    }

    public function test_donor_status_has_no_approval_workflow_column(): void
    {
        $this->assertTrue(Schema::hasColumn('donors', 'status'));
        $this->assertFalse(Schema::hasColumn('donors', 'approval_status'));
    }
}
