<?php

namespace App\Events;

use App\Models\Avenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvenantApprouve implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Avenant $avenant, public float $budgetConsolide)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chantier.'.$this->avenant->id_chantier),
            new PrivateChannel('user.'.$this->avenant->id_demandeur),
        ];
    }

    public function broadcastAs(): string
    {
        return 'avenant-approuve';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->avenant->id,
            'montant_demande' => $this->avenant->montant_demande,
            'statut' => $this->avenant->statut,
            'id_chantier' => $this->avenant->id_chantier,
            'budget_consolide' => $this->budgetConsolide,
        ];
    }
}
