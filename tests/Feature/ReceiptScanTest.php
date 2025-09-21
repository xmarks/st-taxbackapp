<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_scan_page_loads_for_authenticated_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/receipts/scan');

        $response->assertStatus(200)
                 ->assertSee('Scan Receipt')
                 ->assertSee('Camera QR Scanner')
                 ->assertSee('Manual Entry');
    }

    public function test_receipt_scan_page_redirects_for_guest()
    {
        $response = $this->get('/receipts/scan');

        $response->assertRedirect('/login');
    }

    public function test_receipt_scan_form_validation()
    {
        $user = User::factory()->create();

        // Test empty QR data
        $response = $this->actingAs($user)->post('/receipts/scan', [
            'qr_data' => ''
        ]);

        $response->assertSessionHasErrors(['qr_data']);
    }

    public function test_receipt_scan_with_invalid_format()
    {
        $user = User::factory()->create();

        // Test invalid QR data - should fail validation
        $response = $this->actingAs($user)->post('/receipts/scan', [
            'qr_data' => 'invalid-qr-data'
        ]);

        // Should have validation errors
        $response->assertSessionHasErrors(['qr_data']);
    }

    public function test_receipt_scan_validation_works()
    {
        $user = User::factory()->create();

        // Test with a malformed URL that should fail our validation
        $response = $this->actingAs($user)->post('/receipts/scan', [
            'qr_data' => 'https://example.com/bad-url'
        ]);

        // Should have validation errors for malformed QR data
        $response->assertSessionHasErrors(['qr_data']);
    }

    public function test_dashboard_displays_stats()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200)
                 ->assertSee('Total Receipts')
                 ->assertSee('Total VAT Amount')
                 ->assertSee('ALL') // Albanian Lek currency
                 ->assertSee('Quick Actions')
                 ->assertSee('Scan Receipt');
    }

    public function test_receipts_index_page_loads()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/receipts');

        $response->assertStatus(200)
                 ->assertSee('My Receipts')
                 ->assertSee('No receipts found');
    }
}