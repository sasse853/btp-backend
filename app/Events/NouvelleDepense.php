<?php

namespace App\Events;

use App\Models\Finance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NouvelleDepense implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Finance $finance)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin'),
            new PrivateChannel('chantier.'.$this->finance->id_chantier),
        ];
    }

    public function broadcastAs(): string
    {
        return 'nouvelle-depense';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->finance->id,
            'libelle' => $this->finance->libelle,
            'montant' => $this->finance->montant,
            'categorie' => $this->finance->categorie,
            'statut' => $this->finance->statut,
            'id_chantier' => $this->finance->id_chantier,
        ];
    }
}
