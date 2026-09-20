<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('local');

    $this->system1 = User::factory()->create([
        'username' => 'sistema1',
        'system_code' => 'sistema1',
    ]);
    Sanctum::actingAs($this->system1);
});

test('system can stream file inline via authenticated endpoint', function () {
    $file = UploadedFile::fake()->create('circular_01.pdf', 200, 'application/pdf');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    $response = $this->get("/api/v1/files/{$uuid}/view");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'inline; filename="circular_01.pdf"');
});

test('system can download file attachment via authenticated endpoint', function () {
    $file = UploadedFile::fake()->create('inventario.xlsx', 150, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    $response = $this->get("/api/v1/files/{$uuid}/download");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->assertHeader('Content-Disposition', 'attachment; filename="inventario.xlsx"');
});

test('inactive file cannot be streamed or downloaded', function () {
    $file = UploadedFile::fake()->create('doc_inactivo.pdf', 100, 'application/pdf');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // Deactivate file
    $this->patchJson("/api/v1/files/{$uuid}/status", [
        'is_active' => false,
    ])->assertOk();

    // Try view
    $this->getJson("/api/v1/files/{$uuid}/view")
        ->assertStatus(403)
        ->assertJson([
            'status' => 'error',
            'message' => 'El archivo se encuentra inactivo.',
        ]);

    // Try download
    $this->getJson("/api/v1/files/{$uuid}/download")
        ->assertStatus(403)
        ->assertJson([
            'status' => 'error',
            'message' => 'El archivo se encuentra inactivo.',
        ]);
});

test('file can be viewed using temporary HMAC signed URL without authorization header', function () {
    $file = UploadedFile::fake()->create('fotografia_sospechoso.jpg', 200, 'image/jpeg');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $signedViewUrl = $uploadResponse->json('data.urls.signed_view');

    // Request without any token / fresh unauthenticated request
    $response = $this->get($signedViewUrl);

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'inline; filename="fotografia_sospechoso.jpg"');
});

test('file can be downloaded using temporary HMAC signed URL without authorization header', function () {
    $file = UploadedFile::fake()->create('archivo_descarga.pdf', 100, 'application/pdf');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $signedDownloadUrl = $uploadResponse->json('data.urls.signed_download');

    // Request without any token
    $response = $this->get($signedDownloadUrl);

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment; filename="archivo_descarga.pdf"');
});

test('invalid or tampered signed URL returns 403 / Invalid Signature', function () {
    $file = UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $signedViewUrl = $uploadResponse->json('data.urls.signed_view');

    // Tamper signature
    $tamperedUrl = $signedViewUrl.'tampered';

    $response = $this->get($tamperedUrl);
    $response->assertForbidden();
});

test('expired signed URL returns 403', function () {
    $file = UploadedFile::fake()->create('expirado.pdf', 100, 'application/pdf');

    $uploadResponse = $this->postJson('/api/v1/files', [
        'file' => $file,
    ]);

    $uuid = $uploadResponse->json('data.uuid');

    // Create an already expired signed route (10 minutes ago)
    $expiredUrl = URL::temporarySignedRoute(
        'signed.files.view',
        now()->subMinutes(10),
        ['uuid' => $uuid]
    );

    $response = $this->get($expiredUrl);
    $response->assertForbidden();
});
