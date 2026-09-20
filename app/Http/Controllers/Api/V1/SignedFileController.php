<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StoredFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignedFileController extends Controller
{
    /**
     * View file via temporary HMAC signed URL (browser inline preview).
     */
    public function view(Request $request, string $uuid): StreamedResponse
    {
        $file = StoredFile::where('uuid', $uuid)->firstOrFail();

        if (! $file->is_active) {
            abort(403, 'El archivo se encuentra inactivo.');
        }

        return $this->streamFile($file, 'inline');
    }

    /**
     * Download file via temporary HMAC signed URL (attachment).
     */
    public function download(Request $request, string $uuid): StreamedResponse
    {
        $file = StoredFile::where('uuid', $uuid)->firstOrFail();

        if (! $file->is_active) {
            abort(403, 'El archivo se encuentra inactivo.');
        }

        return $this->streamFile($file, 'attachment');
    }

    /**
     * Stream file response with proper headers.
     */
    protected function streamFile(StoredFile $file, string $disposition = 'inline'): StreamedResponse
    {
        $disk = Storage::disk($file->storage_disk);

        if (! $disk->exists($file->storage_path)) {
            abort(404, 'El archivo físico no fue encontrado en el disco de almacenamiento.');
        }

        $headers = [
            'Content-Type' => $file->mime_type,
            'Content-Length' => (string) $file->size_bytes,
            'Content-Disposition' => "{$disposition}; filename=\"{$file->original_name}\"",
            'Cache-Control' => 'private, max-age=3600',
        ];

        return response()->stream(function () use ($disk, $file) {
            $stream = $disk->readStream($file->storage_path);
            if ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, $headers);
    }
}
