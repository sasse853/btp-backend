<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriauResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'designation' => $this->designation,
            'quantite_commandee' => $this->quantite_commandee,
            'unite' => $this->unite,
            'quantite_recue' => $this->quantite_recue,
            'quantite_utilisee' => $this->quantite_utilisee,
            'stock_restant' => $this->stock_restant,
            'prix_unitaire' => $this->prix_unitaire,
            'cout_total' => $this->cout_total,
            'fournisseur' => $this->fournisseur,
            'date_livraison' => $this->date_livraison?->toDateString(),
            'justificatif' => $this->justificatif,
            'justificatif_url' => $this->justificatif ? asset('storage/'.$this->justificatif) : null,
            'id_chantier' => $this->id_chantier,
            'observations' => $this->observations,
        ];
    }
}
