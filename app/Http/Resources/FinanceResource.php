<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'type_operation' => $this->type_operation,
            'montant' => $this->montant,
            'date_operation' => $this->date_operation?->toDateString(),
            'categorie' => $this->categorie,
            'justificatif' => $this->justificatif,
            'justificatif_url' => $this->justificatif ? asset('storage/'.$this->justificatif) : null,
            'statut' => $this->statut,
            'commentaire_admin' => $this->commentaire_admin,
            'id_chantier' => $this->id_chantier,
            'id_utilisateur' => $this->id_utilisateur,
            'date_creation' => $this->date_creation?->toIso8601String(),
            'chantier' => new ChantierResource($this->whenLoaded('chantier')),
            'utilisateur' => new UtilisateurResource($this->whenLoaded('utilisateur')),
        ];
    }
}
