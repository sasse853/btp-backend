<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChantierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'reference' => $this->reference,
            'adresse' => $this->adresse,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'date_debut_prevue' => $this->date_debut_prevue?->toDateString(),
            'date_fin_prevue' => $this->date_fin_prevue?->toDateString(),
            'budget_initial' => $this->budget_initial,
            'budget_consolide' => $this->budget_consolide,
            'maitre_ouvrage' => $this->maitre_ouvrage,
            'statut' => $this->statut,
            'description' => $this->description,
            'id_chef_chantier' => $this->id_chef_chantier,
            'date_creation' => $this->date_creation?->toIso8601String(),

            // Indicateurs calcules (source unique de verite)
            'depenses_engagees' => $this->depenses_engagees,
            'solde' => $this->solde,
            'pourcentage_consomme' => $this->pourcentage_consomme,
            'taux_avancement' => $this->taux_avancement,

            'chef' => new UtilisateurResource($this->whenLoaded('chef')),
            'nb_personnel' => $this->whenCounted('personnel'),
        ];
    }
}
