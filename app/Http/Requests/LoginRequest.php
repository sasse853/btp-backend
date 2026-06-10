<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'mot_de_passe' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => "L'adresse email est obligatoire.",
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}
