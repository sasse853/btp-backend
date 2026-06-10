<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nom' => [$req, 'string', 'max:100'],
            'prenom' => [$req, 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'poste' => [$req, 'string', 'max:100'],
            'type_contrat' => [$req, Rule::in(['cdi', 'cdd', 'journalier', 'prestataire'])],
            'taux_journalier' => ['nullable', 'numeric', 'min:0'],
            'date_entree' => [$req, 'date'],
            'date_sortie_prevue' => ['nullable', 'date', 'after_or_equal:date_entree'],
            'numero_cni' => ['nullable', 'string', 'max:50'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
            'observations' => ['nullable', 'string'],
        ];
    }
}
