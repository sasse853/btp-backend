<?php

namespace App\Events;

use App\Models\Chantier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlerteBudget implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chantier $chantier, public float $pourcentage)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin'),
            new PrivateChannel('chantier.'.$this->chantier->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'alerte-budget';
    }

    public function broadcastWith(): array
    {
        return [
            'id_chantier' => $this->chantier->id,
            'nom' => $this->chantier->nom,
            'reference' => $this->chantier->reference,
            'pourcentage_consomme' => round($this->pourcentage, 2),
            'budget_consolide' => $this->chantier->budget_consolide,
        ];
    }
}
