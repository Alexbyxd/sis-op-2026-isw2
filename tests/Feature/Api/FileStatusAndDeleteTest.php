<?php

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('local');

    $this->system1 = User::factory()->create([
        'username' => 'sistema1',
        'system_code' => 'sistema1',
    ]);
    Sanctum::actingAs($this->system1);
});

test('system can update file status to active and inactive', function () {
    $file = UploadedFile::fake()->create('documento.pdf', 100);

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // Deactivate
    $response = $this->patchJson("/api/v1/files/{$uuid}/status", [
        'is_active' => false,
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'uuid' => $uuid,
                'is_active' => false,
            ],
        ]);

    $this->assertDatabaseHas('stored_files', [
        'uuid' => $uuid,
        'is_active' => false,
    ]);

    // Reactivate
    $response = $this->patchJson("/api/v1/files/{$uuid}/status", [
        'is_active' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'uuid' => $uuid,
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('stored_files', [
        'uuid' => $uuid,
        'is_active' => true,
    ]);
});

test('system can physically delete a file from storage disk and database', function () {
    $file = UploadedFile::fake()->create('documento_para_borrar.pdf', 100);

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');
    $storedFile = StoredFile::where('uuid', $uuid)->first();
    $storagePath = $storedFile->storage_path;

    // Verify physical file exists
    Storage::disk('local')->assertExists($storagePath);

    // Call physical delete endpoint
    $response = $this->deleteJson("/api/v1/files/{$uuid}");

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Archivo eliminado físicamente de forma exitosa.',
            'data' => [
                'uuid' => $uuid,
                'deleted' => true,
            ],
        ]);

    // Verify physical file is erased from disk
    Storage::disk('local')->assertMissing($storagePath);

    // Verify record is erased from database
    $this->assertDatabaseMissing('stored_files', [
        'uuid' => $uuid,
    ]);
});
