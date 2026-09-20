<?php

namespace App\Models;

use Database\Factories\StoredFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $original_name
 * @property string $mime_type
 * @property string $extension
 * @property int $size_bytes
 * @property string $storage_disk
 * @property string $storage_path
 * @property bool $is_active
 * @property string|null $checksum_sha256
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'uuid',
    'user_id',
    'original_name',
    'mime_type',
    'extension',
    'size_bytes',
    'storage_disk',
    'storage_path',
    'is_active',
    'checksum_sha256',
    'metadata',
])]
class StoredFile extends Model
{
    /** @use HasFactory<StoredFileFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'size_bytes' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to only active files.
     *
     * @param  Builder<StoredFile>  $query
     * @return Builder<StoredFile>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to a specific tenant / user.
     *
     * @param  Builder<StoredFile>  $query
     * @return Builder<StoredFile>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
