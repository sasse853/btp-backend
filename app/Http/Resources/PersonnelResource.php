<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonnelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->nom_complet,
            'telephone' => $this->telephone,
            'poste' => $this->poste,
            'type_contrat' => $this->type_contrat,
            'taux_journalier' => $this->taux_journalier,
            'date_entree' => $this->date_entree?->toDateString(),
            'date_sortie_prevue' => $this->date_sortie_prevue?->toDateString(),
            'numero_cni' => $this->numero_cni,
            'id_chantier' => $this->id_chantier,
            'observations' => $this->observations,
            'chantier' => new ChantierResource($this->whenLoaded('chantier')),
        ];
    }
}
