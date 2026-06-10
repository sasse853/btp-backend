<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilisateurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->nom_complet,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'role' => $this->role,
            'actif' => $this->actif,
            'date_creation' => $this->date_creation?->toIso8601String(),
        ];
    }
}
