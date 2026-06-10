<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AvenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'montant_demande' => [$req, 'numeric', 'gt:0'],
            'motif' => [$req, 'string', 'min:5'],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'montant_demande.gt' => 'Le montant demande doit etre superieur a 0.',
            'motif.required' => 'Le motif de la demande est obligatoire.',
        ];
    }
}
