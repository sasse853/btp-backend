<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nom' => [$req, 'string', 'max:150'],
            'reference' => ['nullable', 'string', 'max:100'],
            'type_mise_dispo' => [$req, Rule::in(['propriete', 'location'])],
            'fournisseur' => ['nullable', 'string', 'max:150'],
            'cout_journalier' => ['nullable', 'numeric', 'min:0', 'required_if:type_mise_dispo,location'],
            'date_affectation' => [$req, 'date'],
            'date_retour_prevue' => ['nullable', 'date', 'after_or_equal:date_affectation'],
            'etat' => [$req, Rule::in(['bon_etat', 'en_maintenance', 'defectueux'])],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
        ];
    }
}
