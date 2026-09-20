<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->string('extension', 50);
            $table->unsignedBigInteger('size_bytes');
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->boolean('is_active')->default(true)->index();
            $table->string('checksum_sha256', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
