<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NouveauMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chantier.'.$this->message->id_chantier),
        ];
    }

    public function broadcastAs(): string
    {
        return 'nouveau-message';
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('expediteur');

        return [
            'id' => $this->message->id,
            'contenu' => $this->message->contenu,
            'fichier_joint' => $this->message->fichier_joint,
            'id_chantier' => $this->message->id_chantier,
            'id_expediteur' => $this->message->id_expediteur,
            'expediteur' => $this->message->expediteur
                ? $this->message->expediteur->nom_complet
                : null,
            'date_envoi' => $this->message->date_envoi?->toIso8601String(),
        ];
    }
}
