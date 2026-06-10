<?php

namespace App\Events;

use App\Models\Chantier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangementStatut implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Chantier $chantier,
        public string $ancienStatut,
        public string $nouveauStatut,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin'),
            new PrivateChannel('chantier.'.$this->chantier->id),
            new PrivateChannel('user.'.$this->chantier->id_chef_chantier),
        ];
    }

    public function broadcastAs(): string
    {
        return 'changement-statut';
    }

    public function broadcastWith(): array
    {
        return [
            'id_chantier' => $this->chantier->id,
            'nom' => $this->chantier->nom,
            'ancien_statut' => $this->ancienStatut,
            'nouveau_statut' => $this->nouveauStatut,
        ];
    }
}
