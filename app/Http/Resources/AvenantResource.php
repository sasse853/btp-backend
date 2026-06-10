<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'montant_demande' => $this->montant_demande,
            'motif' => $this->motif,
            'justificatif' => $this->justificatif,
            'justificatif_url' => $this->justificatif ? asset('storage/'.$this->justificatif) : null,
            'statut' => $this->statut,
            'commentaire_admin' => $this->commentaire_admin,
            'id_chantier' => $this->id_chantier,
            'id_demandeur' => $this->id_demandeur,
            'date_demande' => $this->date_demande?->toIso8601String(),
            'date_traitement' => $this->date_traitement?->toIso8601String(),
            'chantier' => new ChantierResource($this->whenLoaded('chantier')),
            'demandeur' => new UtilisateurResource($this->whenLoaded('demandeur')),
        ];
    }
}
