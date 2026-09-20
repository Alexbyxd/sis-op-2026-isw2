<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->system1 = User::create([
        'name' => 'Sistema de Correspondencia',
        'username' => 'sistema1',
        'email' => 'sistema1@policia.local',
        'system_code' => 'sistema1',
        'password' => Hash::make('Correspondencia2026!'),
    ]);

    $this->system2 = User::create([
        'name' => 'Sistema de Manejo de Oficiales',
        'username' => 'sistema2',
        'email' => 'sistema2@policia.local',
        'system_code' => 'sistema2',
        'password' => Hash::make('Oficiales2026!'),
    ]);
});

test('sistema1 can login with valid credentials and obtain a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'sistema1',
        'password' => 'Correspondencia2026!',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'token_type',
                'system' => ['id', 'name', 'username', 'system_code'],
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'token_type' => 'Bearer',
                'system' => [
                    'username' => 'sistema1',
                    'system_code' => 'sistema1',
                ],
            ],
        ]);
});

test('sistema2 can login with valid credentials and obtain a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'sistema2',
        'password' => 'Oficiales2026!',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'system' => [
                    'username' => 'sistema2',
                    'system_code' => 'sistema2',
                ],
            ],
        ]);
});

test('login fails with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'sistema1',
        'password' => 'WrongPassword123!',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Credenciales inválidas para el sistema.',
        ]);
});

test('authenticated system can access /api/v1/auth/me', function () {
    $token = $this->system1->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'username' => 'sistema1',
                'system_code' => 'sistema1',
            ],
        ]);
});

test('unauthenticated request to protected endpoint returns 401', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized();
});

test('authenticated system can logout and revoke token', function () {
    $token = $this->system1->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Token revocado correctamente.',
        ]);

    expect($this->system1->tokens()->count())->toBe(0);
});
