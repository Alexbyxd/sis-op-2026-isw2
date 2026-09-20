<?php

test('scalar documentation route is accessible and loads local assets', function () {
    $response = $this->get('/scalar');

    $response->assertOk()
        ->assertSee('/vendor/scalar/scalar.js')
        ->assertSee('Scalar.createApiReference');
});

test('openapi specification file exists and is valid JSON', function () {
    $path = storage_path('app/openapi.json');

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->not()->toBeEmpty();

    $json = json_decode($content, true);
    expect($json)->toBeArray()
        ->and($json['openapi'])->toBe('3.1.0')
        ->and($json['info']['title'])->toBe('Police Storage Interoperability API')
        ->and($json['paths'])->toHaveKeys([
            '/api/v1/auth/login',
            '/api/v1/auth/me',
            '/api/v1/files',
            '/api/v1/files/{uuid}',
            '/api/v1/files/{uuid}/view',
            '/api/v1/files/{uuid}/download',
            '/api/v1/files/{uuid}/status',
            '/api/signed/files/{uuid}/view',
            '/api/signed/files/{uuid}/download',
        ]);
});

test('local scalar standalone js bundle file exists and is readable', function () {
    $bundlePath = public_path('vendor/scalar/scalar.js');

    expect(file_exists($bundlePath))->toBeTrue()
        ->and(filesize($bundlePath))->toBeGreaterThan(100000);
});
