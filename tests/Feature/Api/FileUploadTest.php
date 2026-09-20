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
        'email' => 'sistema1@policia.local',
        'system_code' => 'sistema1',
    ]);

    Sanctum::actingAs($this->system1);
});

test('system can upload a PDF file and receives UUID reference', function () {
    $file = UploadedFile::fake()->create('oficio_policial_001.pdf', 500, 'application/pdf');

    $response = $this->postJson('/api/v1/files', [
        'file' => $file,
        'metadata' => [
            'department' => 'Investigaciones',
            'case_id' => 'CASO-2026-99',
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'uuid',
                'original_name',
                'mime_type',
                'extension',
                'size_bytes',
                'size_human',
                'is_active',
                'checksum_sha256',
                'metadata',
                'urls' => [
                    'direct_view',
                    'direct_download',
                    'signed_view',
                    'signed_download',
                    'signed_expires_at',
                ],
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'original_name' => 'oficio_policial_001.pdf',
                'extension' => 'pdf',
                'is_active' => true,
                'metadata' => [
                    'department' => 'Investigaciones',
                    'case_id' => 'CASO-2026-99',
                ],
            ],
        ]);

    $uuid = $response->json('data.uuid');

    $this->assertDatabaseHas('stored_files', [
        'uuid' => $uuid,
        'user_id' => $this->system1->id,
        'original_name' => 'oficio_policial_001.pdf',
        'extension' => 'pdf',
        'is_active' => true,
    ]);

    $storedFile = StoredFile::where('uuid', $uuid)->first();
    Storage::disk('local')->assertExists($storedFile->storage_path);
});

test('system can upload images (.png, .jpg) and office documents (.docx, .xlsx)', function () {
    $files = [
        UploadedFile::fake()->create('fotografia_evidencia.jpg', 200, 'image/jpeg'),
        UploadedFile::fake()->create('reporte_guardias.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        UploadedFile::fake()->create('cuadro_armamento.xlsx', 400, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
    ];

    foreach ($files as $file) {
        $response = $this->postJson('/api/v1/files', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $uuid = $response->json('data.uuid');
        $this->assertDatabaseHas('stored_files', [
            'uuid' => $uuid,
            'user_id' => $this->system1->id,
        ]);
    }
});

test('upload fails when no file is provided', function () {
    $response = $this->postJson('/api/v1/files', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('upload fails when file exceeds maximum allowed size', function () {
    // 60MB file exceeds 50MB default limit (51200 KB)
    $oversizedFile = UploadedFile::fake()->create('archivo_gigante.pdf', 61440, 'application/pdf');

    $response = $this->postJson('/api/v1/files', [
        'file' => $oversizedFile,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});
