<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MateriauRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'designation' => [$req, 'string', 'max:150'],
            'quantite_commandee' => [$req, 'numeric', 'min:0'],
            'unite' => [$req, 'string', 'max:20'],
            'quantite_recue' => ['nullable', 'numeric', 'min:0'],
            'quantite_utilisee' => ['nullable', 'numeric', 'min:0'],
            'prix_unitaire' => [$req, 'numeric', 'min:0'],
            'fournisseur' => ['nullable', 'string', 'max:150'],
            'date_livraison' => ['nullable', 'date'],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
            'observations' => ['nullable', 'string'],
        ];
    }
}
