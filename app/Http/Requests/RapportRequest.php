<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RapportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string'],
            'observations' => ['nullable', 'string'],
            'archiver' => ['nullable', 'boolean'],
        ];
    }
}
