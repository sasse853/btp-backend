<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contenu' => $this->contenu,
            'fichier_joint' => $this->fichier_joint,
            'fichier_joint_url' => $this->fichier_joint ? asset('storage/'.$this->fichier_joint) : null,
            'lu' => $this->lu,
            'id_chantier' => $this->id_chantier,
            'id_expediteur' => $this->id_expediteur,
            'date_envoi' => $this->date_envoi?->toIso8601String(),
            'expediteur' => new UtilisateurResource($this->whenLoaded('expediteur')),
        ];
    }
}
