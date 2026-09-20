<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateFileStatusRequest;
use App\Http\Requests\Api\V1\UploadFileRequest;
use App\Http\Resources\Api\V1\StoredFileResource;
use App\Models\StoredFile;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        protected FileStorageService $storageService
    ) {}

    /**
     * List all files belonging to the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = StoredFile::query()->forUser($user->id);

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('extension')) {
            $query->where('extension', strtolower((string) $request->query('extension')));
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where('original_name', 'like', "%{$search}%");
        }

        $files = $query->latest()->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => StoredFileResource::collection($files),
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    /**
     * Store and upload a new file for the authenticated tenant.
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $uploadedFile = $request->file('file');
        $metadata = $request->input('metadata');

        if (! $uploadedFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se proporcionó ningún archivo.',
            ], 422);
        }

        $storedFile = $this->storageService->store(
            user: $user,
            file: $uploadedFile,
            metadata: is_array($metadata) ? $metadata : null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo cargado correctamente.',
            'data' => new StoredFileResource($storedFile),
        ], 201);
    }

    /**
     * Get metadata of a specific file.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $file = $this->findTenantFile($request->user(), $uuid);

        return response()->json([
            'status' => 'success',
            'data' => new StoredFileResource($file),
        ]);
    }

    /**
     * Direct authenticated binary streaming for viewing in browser (inline).
     */
    public function view(Request $request, string $uuid): StreamedResponse|JsonResponse
    {
        $file = $this->findTenantFile($request->user(), $uuid);

        if (! $file->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'El archivo se encuentra inactivo.',
            ], 403);
        }

        return $this->streamFile($file, 'inline');
    }

    /**
     * Direct authenticated binary streaming for downloading file (attachment).
     */
    public function download(Request $request, string $uuid): StreamedResponse|JsonResponse
    {
        $file = $this->findTenantFile($request->user(), $uuid);

        if (! $file->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'El archivo se encuentra inactivo.',
            ], 403);
        }

        return $this->streamFile($file, 'attachment');
    }

    /**
     * Update active status of a file.
     */
    public function updateStatus(UpdateFileStatusRequest $request, string $uuid): JsonResponse
    {
        $file = $this->findTenantFile($request->user(), $uuid);
        $updatedFile = $this->storageService->updateStatus($file, (bool) $request->input('is_active'));

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del archivo actualizado correctamente.',
            'data' => new StoredFileResource($updatedFile),
        ]);
    }

    /**
     * Physically delete a file from storage and database.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $file = $this->findTenantFile($request->user(), $uuid);
        $this->storageService->delete($file);

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo eliminado físicamente de forma exitosa.',
            'data' => [
                'uuid' => $uuid,
                'deleted' => true,
            ],
        ]);
    }

    /**
     * Helper to find file belonging strictly to the authenticated tenant.
     */
    protected function findTenantFile(User $user, string $uuid): StoredFile
    {
        return StoredFile::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();
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
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
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
