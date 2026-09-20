<?php

namespace Database\Factories;

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StoredFile>
 */
class StoredFileFactory extends Factory
{
    protected $model = StoredFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = (string) Str::uuid();
        $extension = fake()->randomElement(['pdf', 'docx', 'png', 'jpg', 'xlsx']);
        $mimeMap = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return [
            'uuid' => $uuid,
            'user_id' => User::factory(),
            'original_name' => fake()->word().'.'.$extension,
            'mime_type' => $mimeMap[$extension],
            'extension' => $extension,
            'size_bytes' => fake()->numberBetween(1024, 10485760),
            'storage_disk' => 'local',
            'storage_path' => "tenants/test/{$uuid}.{$extension}",
            'is_active' => true,
            'checksum_sha256' => hash('sha256', (string) Str::random(32)),
            'metadata' => ['department' => 'Police-IT', 'tag' => 'official-record'],
        ];
    }

    /**
     * Indicate that the file is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
