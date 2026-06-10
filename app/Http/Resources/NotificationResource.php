<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'type' => $this->type,
            'lu' => $this->lu,
            'id_destinataire' => $this->id_destinataire,
            'id_chantier' => $this->id_chantier,
            'date_creation' => $this->date_creation?->toIso8601String(),
        ];
    }
}
