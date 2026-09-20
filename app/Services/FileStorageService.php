<?php

namespace App\Services;

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Store an uploaded file on disk and create a database record for the tenant.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function store(User $user, UploadedFile $file, ?array $metadata = null, string $disk = 'local'): StoredFile
    {
        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        $sizeBytes = $file->getSize();

        // Calculate sha256 checksum
        $realPath = $file->getRealPath();
        $checksum = $realPath ? hash_file('sha256', $realPath) : null;

        // Structured directory by tenant and date
        $year = now()->format('Y');
        $month = now()->format('m');
        $directory = "tenants/{$user->id}/{$year}/{$month}";
        $filename = "{$uuid}.{$extension}";

        $storagePath = $file->storeAs($directory, $filename, $disk);

        if (! $storagePath) {
            throw new \RuntimeException('No se pudo guardar el archivo en el almacenamiento.');
        }

        return DB::transaction(function () use ($user, $uuid, $originalName, $mimeType, $extension, $sizeBytes, $disk, $storagePath, $checksum, $metadata) {
            return StoredFile::create([
                'uuid' => $uuid,
                'user_id' => $user->id,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size_bytes' => $sizeBytes,
                'storage_disk' => $disk,
                'storage_path' => $storagePath,
                'is_active' => true,
                'checksum_sha256' => $checksum,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Physically delete a stored file from disk and database.
     */
    public function delete(StoredFile $storedFile): bool
    {
        return DB::transaction(function () use ($storedFile) {
            if (Storage::disk($storedFile->storage_disk)->exists($storedFile->storage_path)) {
                Storage::disk($storedFile->storage_disk)->delete($storedFile->storage_path);
            }

            return (bool) $storedFile->delete();
        });
    }

    /**
     * Toggle or update active status of a file.
     */
    public function updateStatus(StoredFile $storedFile, bool $isActive): StoredFile
    {
        $storedFile->update(['is_active' => $isActive]);

        return $storedFile->fresh();
    }
}
