<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_personnel' => $this->id_personnel,
            'id_chantier' => $this->id_chantier,
            'date_presence' => $this->date_presence?->toDateString(),
            'statut' => $this->statut,
            'montant_du' => $this->montant_du,
            'statut_paiement' => $this->statut_paiement,
            'date_paiement' => $this->date_paiement?->toDateString(),
            'personnel' => new PersonnelResource($this->whenLoaded('personnel')),
        ];
    }
}
