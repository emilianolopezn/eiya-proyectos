<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'photo_authorized' => ['exclude_without:photo', 'required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nos gustaría saber cómo se llama esta persona.',
            'email.email' => 'Revisa el correo para que podamos encontrarnos cuando haga falta.',
            'photo.image' => 'Elige una imagen para reconocer a esta persona.',
            'photo.mimes' => 'La foto puede ser JPG, PNG o WebP.',
            'photo.max' => 'La foto debe pesar 3 MB o menos.',
            'photo_authorized.*' => 'Confirma que tienes autorización de la persona para guardar su foto.',
            '*.max' => 'Este texto es un poco largo. ¿Nos compartes una versión más breve?',
        ];
    }
}
