<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'reference' => $this->reference,
            'type_mise_dispo' => $this->type_mise_dispo,
            'fournisseur' => $this->fournisseur,
            'cout_journalier' => $this->cout_journalier,
            'nb_jours_location' => $this->nb_jours_location,
            'cout_total_location' => $this->cout_total_location,
            'date_affectation' => $this->date_affectation?->toDateString(),
            'date_retour_prevue' => $this->date_retour_prevue?->toDateString(),
            'etat' => $this->etat,
            'justificatif' => $this->justificatif,
            'justificatif_url' => $this->justificatif ? asset('storage/'.$this->justificatif) : null,
            'id_chantier' => $this->id_chantier,
        ];
    }
}
