<?php

namespace App\Http\Resources\Api\V1;

use App\Models\StoredFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin StoredFile
 */
class StoredFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $signedExpirationMinutes = (int) config('storage_service.signed_url_expiration_minutes', 30);
        $expiresAt = now()->addMinutes($signedExpirationMinutes);

        return [
            'uuid' => $this->uuid,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size_bytes' => $this->size_bytes,
            'size_human' => $this->formatBytes($this->size_bytes),
            'is_active' => $this->is_active,
            'checksum_sha256' => $this->checksum_sha256,
            'metadata' => $this->metadata,
            'urls' => [
                'direct_view' => url("/api/v1/files/{$this->uuid}/view"),
                'direct_download' => url("/api/v1/files/{$this->uuid}/download"),
                'signed_view' => URL::temporarySignedRoute(
                    'signed.files.view',
                    $expiresAt,
                    ['uuid' => $this->uuid]
                ),
                'signed_download' => URL::temporarySignedRoute(
                    'signed.files.download',
                    $expiresAt,
                    ['uuid' => $this->uuid]
                ),
                'signed_expires_at' => $expiresAt->toIso8601String(),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Helper to format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = max(0, min($pow, count($units) - 1));
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
