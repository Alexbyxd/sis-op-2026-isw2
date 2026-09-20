<?php

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('local');

    $this->system1 = User::factory()->create([
        'name' => 'Sistema de Correspondencia',
        'username' => 'sistema1',
        'system_code' => 'sistema1',
    ]);

    $this->system2 = User::factory()->create([
        'name' => 'Sistema de Manejo de Oficiales',
        'username' => 'sistema2',
        'system_code' => 'sistema2',
    ]);
});

test('system2 cannot see or access files uploaded by system1 via show endpoint', function () {
    $file = UploadedFile::fake()->create('correspondencia_secreta.pdf', 100, 'application/pdf');

    Sanctum::actingAs($this->system1);
    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // System 1 CAN see it
    Sanctum::actingAs($this->system1);
    $this->getJson("/api/v1/files/{$uuid}")
        ->assertOk()
        ->assertJsonPath('data.uuid', $uuid);

    // System 2 CANNOT see it (returns 404)
    Sanctum::actingAs($this->system2);
    $this->getJson("/api/v1/files/{$uuid}")
        ->assertNotFound();
});

test('system2 cannot view or download file uploaded by system1', function () {
    $file = UploadedFile::fake()->create('legajo_oficial.pdf', 100, 'application/pdf');

    Sanctum::actingAs($this->system1);
    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // System 2 view request is rejected with 404
    Sanctum::actingAs($this->system2);
    $this->getJson("/api/v1/files/{$uuid}/view")
        ->assertNotFound();

    // System 2 download request is rejected with 404
    Sanctum::actingAs($this->system2);
    $this->getJson("/api/v1/files/{$uuid}/download")
        ->assertNotFound();
});

test('system2 cannot update status or delete file uploaded by system1', function () {
    $file = UploadedFile::fake()->create('documento_policial.docx', 100);

    Sanctum::actingAs($this->system1);
    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // System 2 tries to update status -> 404
    Sanctum::actingAs($this->system2);
    $this->patchJson("/api/v1/files/{$uuid}/status", [
        'is_active' => false,
    ])->assertNotFound();

    // System 2 tries to delete -> 404
    Sanctum::actingAs($this->system2);
    $this->deleteJson("/api/v1/files/{$uuid}")
        ->assertNotFound();

    // Ensure file remains active and not deleted
    $this->assertDatabaseHas('stored_files', [
        'uuid' => $uuid,
        'is_active' => true,
    ]);
});

test('file listing only returns files belonging to the authenticated system', function () {
    // System 1 uploads 3 files
    StoredFile::factory()->count(3)->create(['user_id' => $this->system1->id]);

    // System 2 uploads 2 files
    StoredFile::factory()->count(2)->create(['user_id' => $this->system2->id]);

    // System 1 listings
    Sanctum::actingAs($this->system1);
    $response1 = $this->getJson('/api/v1/files');
    $response1->assertOk()
        ->assertJsonPath('meta.total', 3);

    // System 2 listings
    Sanctum::actingAs($this->system2);
    $response2 = $this->getJson('/api/v1/files');
    $response2->assertOk()
        ->assertJsonPath('meta.total', 2);
});
