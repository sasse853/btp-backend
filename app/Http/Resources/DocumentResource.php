<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'type_document' => $this->type_document,
            'fichier' => $this->fichier,
            'fichier_url' => $this->fichier ? asset('storage/'.$this->fichier) : null,
            'statut' => $this->statut,
            'commentaire_admin' => $this->commentaire_admin,
            'id_chantier' => $this->id_chantier,
            'id_utilisateur' => $this->id_utilisateur,
            'date_upload' => $this->date_upload?->toIso8601String(),
            'chantier' => new ChantierResource($this->whenLoaded('chantier')),
            'utilisateur' => new UtilisateurResource($this->whenLoaded('utilisateur')),
        ];
    }
}
