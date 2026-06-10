<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'libelle' => [$req, 'string', 'max:255'],
            'type_operation' => [$req, Rule::in(['depense', 'devis', 'facture', 'bon_livraison', 'avance_acompte'])],
            'montant' => [$req, 'numeric', 'gt:0'],
            'date_operation' => [$req, 'date'],
            'categorie' => [$req, Rule::in(['main_oeuvre', 'materiaux', 'equipements', 'divers'])],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'montant.gt' => 'Le montant doit etre strictement superieur a 0.',
        ];
    }
}
