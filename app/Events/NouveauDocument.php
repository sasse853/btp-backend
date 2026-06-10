<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NouveauDocument implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Document $document)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin'),
            new PrivateChannel('chantier.'.$this->document->id_chantier),
        ];
    }

    public function broadcastAs(): string
    {
        return 'nouveau-document';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->document->id,
            'titre' => $this->document->titre,
            'type_document' => $this->document->type_document,
            'statut' => $this->document->statut,
            'id_chantier' => $this->document->id_chantier,
        ];
    }
}
