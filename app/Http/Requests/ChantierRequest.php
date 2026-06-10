<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChantierRequest extends FormRequest
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
            'adresse' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'date_debut_prevue' => ['nullable', 'date'],
            'date_fin_prevue' => ['nullable', 'date', 'after_or_equal:date_debut_prevue'],
            'budget_initial' => [$req, 'numeric', 'min:0'],
            'maitre_ouvrage' => ['nullable', 'string', 'max:150'],
            'statut' => [$req, Rule::in(['en_attente', 'en_cours', 'en_pause', 'termine', 'archive'])],
            'description' => ['nullable', 'string'],
            'id_chef_chantier' => [$req, 'integer', Rule::exists('utilisateurs', 'id')->where('role', 'chef_chantier')],
        ];
    }
}
