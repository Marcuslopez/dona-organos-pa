<?php

namespace Tests\Feature;

use App\Mail\ContactInquiryAssignedMail;
use App\Mail\ContactInquiryReceivedMail;
use App\Mail\ContactInquiryResponseMail;
use App\Mail\NewContactInquiryMail;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_a_contact_inquiry_and_both_notifications_are_sent(): void
    {
        Mail::fake();

        $response = $this->post(route('contact.store'), [
            'name' => 'María González',
            'email' => 'maria@example.com',
            'message' => 'Necesito orientación sobre el registro como donante.',
            'privacy_accepted' => '1',
        ]);

        $response->assertRedirect(route('contact.create'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'María González',
            'email' => 'maria@example.com',
            'status' => 'nueva',
        ]);
        $this->assertDatabaseHas('contact_inquiry_histories', [
            'action' => 'created',
            'current_status' => 'nueva',
        ]);
        Mail::assertSent(NewContactInquiryMail::class, fn (NewContactInquiryMail $mail) => $mail->inquiry->email === 'maria@example.com');
        Mail::assertSent(ContactInquiryReceivedMail::class, fn (ContactInquiryReceivedMail $mail) => $mail->inquiry->email === 'maria@example.com');
    }

    public function test_contact_inquiry_requires_valid_contact_data_and_privacy_consent(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'maria gonzalez',
            'email' => 'correo-invalido',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'message', 'privacy_accepted']);

        $this->assertDatabaseCount('contact_inquiries', 0);
    }

    public function test_master_can_assign_an_inquiry_to_an_active_administrator_and_notify_them(): void
    {
        Mail::fake();
        $master = User::factory()->create(['role' => 'master', 'is_active' => true, 'must_change_password' => false]);
        $administrator = User::factory()->create(['role' => 'administrator', 'is_active' => true, 'must_change_password' => false]);
        $inquiry = $this->inquiry();

        $this->actingAs($master)
            ->post(route('admin.contact-inquiries.assign', $inquiry), ['assigned_to' => $administrator->id])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'assigned_to' => $administrator->id,
            'status' => 'en_proceso',
        ]);
        $this->assertDatabaseHas('contact_inquiry_histories', [
            'contact_inquiry_id' => $inquiry->id,
            'actor_id' => $master->id,
            'action' => 'assigned',
        ]);
        Mail::assertSent(ContactInquiryAssignedMail::class, fn (ContactInquiryAssignedMail $mail) => $mail->inquiry->assigned_to === $administrator->id);
    }

    public function test_administrator_can_take_and_reply_to_an_available_inquiry(): void
    {
        Mail::fake();
        $administrator = User::factory()->create(['role' => 'administrator', 'is_active' => true, 'must_change_password' => false]);
        $inquiry = $this->inquiry();

        $this->actingAs($administrator)
            ->post(route('admin.contact-inquiries.take', $inquiry))
            ->assertRedirect();

        $this->actingAs($administrator)
            ->post(route('admin.contact-inquiries.respond', $inquiry), [
                'response' => 'Gracias por escribirnos. Nuestro equipo revisará tu consulta.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'assigned_to' => $administrator->id,
            'status' => 'respondida',
            'responded_by' => $administrator->id,
        ]);
        $this->assertDatabaseHas('contact_inquiry_replies', [
            'contact_inquiry_id' => $inquiry->id,
            'author_id' => $administrator->id,
            'body' => 'Gracias por escribirnos. Nuestro equipo revisará tu consulta.',
        ]);
        Mail::assertSent(ContactInquiryResponseMail::class);
    }

    private function inquiry(): ContactInquiry
    {
        return ContactInquiry::create([
            'name' => 'Persona Consulta',
            'email' => 'persona@example.com',
            'message' => 'Consulta de prueba para mantenimiento administrativo.',
            'privacy_accepted_at' => now(),
            'privacy_policy_version' => '2026-08-20',
        ]);
    }
}
