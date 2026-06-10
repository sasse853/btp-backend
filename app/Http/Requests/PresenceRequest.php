<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuts = ['present', 'demi_journee', 'absent_justifie', 'absent_non_justifie', 'conge'];

        // Saisie en lot : POST /presences/batch
        if ($this->routeIs('*presences.batch') || $this->has('presences')) {
            return [
                'id_chantier' => ['required', 'integer', Rule::exists('chantiers', 'id')],
                'date_presence' => ['required', 'date'],
                'presences' => ['required', 'array', 'min:1'],
                'presences.*.id_personnel' => ['required', 'integer', Rule::exists('personnel', 'id')],
                'presences.*.statut' => ['required', Rule::in($statuts)],
            ];
        }

        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'id_personnel' => [$req, 'integer', Rule::exists('personnel', 'id')],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
            'date_presence' => [$req, 'date'],
            'statut' => [$req, Rule::in($statuts)],
            'statut_paiement' => ['sometimes', Rule::in(['en_attente', 'paye'])],
            'date_paiement' => ['nullable', 'date'],
        ];
    }
}
