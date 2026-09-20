<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\SignedFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Version 1 routes for the Police Interoperability Storage System.
|
*/

// Public Authentication
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.auth.login');
});

// Authenticated Tenant Routes
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Tenant Profile & Token Management
    Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');

    // File Operations (Strictly Tenant-Isolated)
    Route::get('files', [FileController::class, 'index'])->name('api.v1.files.index');
    Route::post('files', [FileController::class, 'store'])->name('api.v1.files.store');
    Route::get('files/{uuid}', [FileController::class, 'show'])->name('api.v1.files.show');
    Route::get('files/{uuid}/view', [FileController::class, 'view'])->name('api.v1.files.view');
    Route::get('files/{uuid}/download', [FileController::class, 'download'])->name('api.v1.files.download');
    Route::patch('files/{uuid}/status', [FileController::class, 'updateStatus'])->name('api.v1.files.status');
    Route::delete('files/{uuid}', [FileController::class, 'destroy'])->name('api.v1.files.destroy');
});

// Temporary HMAC Signed URLs (Browser embedding without Bearer token)
Route::prefix('signed')->middleware(['signed'])->group(function () {
    Route::get('files/{uuid}/view', [SignedFileController::class, 'view'])->name('signed.files.view');
    Route::get('files/{uuid}/download', [SignedFileController::class, 'download'])->name('signed.files.download');
});
