<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = $this->isMethod('post');
        $req = $store ? 'required' : 'sometimes';

        return [
            'titre' => [$req, 'string', 'max:200'],
            'type_document' => [$req, Rule::in(['plan', 'contrat', 'rapport', 'pv', 'fiche_securite', 'autre'])],
            // Fichier obligatoire a la creation uniquement.
            'fichier' => [$store ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'id_chantier' => [$req, 'integer', Rule::exists('chantiers', 'id')],
        ];
    }
}
