<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default users
        $this->seed();

        $this->admin = User::where('email', 'admin@office.local')->first();
        $this->staff = User::where('email', 'staff1@office.local')->first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/documents');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@office.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/documents');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_authenticated_user_can_see_documents_index(): void
    {
        $response = $this->actingAs($this->staff)->get('/documents');
        $response->assertStatus(200);
        $response->assertSee('Arsip Dokumen Kantor');
    }

    public function test_staff_can_upload_document(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $file = UploadedFile::fake()->create('surat-edaran.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->staff)->post('/documents', [
            'title' => 'Surat Edaran Direksi 2026',
            'category' => 'surat_masuk',
            'description' => 'Surat edaran resmi',
            'file' => $file,
        ]);

        $response->assertRedirect('/documents');
        $this->assertDatabaseHas('documents', [
            'title' => 'Surat Edaran Direksi 2026',
            'category' => 'surat_masuk',
            'user_id' => $this->staff->id,
        ]);
    }

    public function test_staff_cannot_delete_document(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $document = Document::create([
            'user_id' => $this->admin->id,
            'title' => 'Laporan Keuangan Tahunan',
            'category' => 'invoice',
            'file_path' => 'uploads/invoice/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
        ]);

        // Staff tries to delete
        $response = $this->actingAs($this->staff)->delete("/documents/{$document->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    public function test_admin_can_delete_document(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $document = Document::create([
            'user_id' => $this->staff->id,
            'title' => 'Dokumen Sementara',
            'category' => 'lainnya',
            'file_path' => 'uploads/lainnya/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
        ]);

        // Admin deletes
        $response = $this->actingAs($this->admin)->delete("/documents/{$document->id}");
        $response->assertRedirect('/documents');

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staff)->get('/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna Kantor');
    }
}

