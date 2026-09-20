<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxKb = (int) config('storage_service.max_file_size_kb', 51200);

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxKb = (int) config('storage_service.max_file_size_kb', 51200);
        $maxMb = (int) ($maxKb / 1024);

        return [
            'file.required' => 'Es obligatorio adjuntar un archivo.',
            'file.file' => 'El recurso proporcionado debe ser un archivo válido.',
            'file.max' => "El archivo supera el tamaño máximo permitido de {$maxMb}MB.",
        ];
    }
}
