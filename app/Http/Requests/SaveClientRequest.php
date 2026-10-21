<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['active', 'paused'])],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Cuéntanos cómo se llama esta organización o persona.',
            'website.url' => 'Escribe una dirección completa, por ejemplo https://ejemplo.com.',
            'logo.image' => 'Elige una imagen para reconocer su identidad.',
            'logo.mimes' => 'El logo puede ser JPG, PNG o WebP.',
            'logo.max' => 'El logo debe pesar 3 MB o menos.',
            '*.max' => 'Este texto es un poco largo. ¿Nos compartes una versión más breve?',
            'status.*' => 'Elige cómo va nuestro camino: colaborando o en pausa.',
        ];
    }
}
