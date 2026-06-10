<?php

namespace App\Observers;

use App\Events\NouveauDocument;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Utilisateur;

class DocumentObserver
{
    /** Notifie l'admin a l'upload d'un document en attente de validation. */
    public function created(Document $document): void
    {
        if ($document->statut === 'en_attente') {
            foreach ($this->admins() as $adminId) {
                Notification::create([
                    'message' => "Nouveau document \"{$document->titre}\" en attente de validation.",
                    'type' => 'validation',
                    'id_destinataire' => $adminId,
                    'id_chantier' => $document->id_chantier,
                ]);
            }

            broadcast(new NouveauDocument($document));
        }
    }

    /** Notifie l'auteur lors de la validation/rejet du document. */
    public function updated(Document $document): void
    {
        if (! $document->wasChanged('statut')) {
            return;
        }

        if ($document->statut === 'valide') {
            Notification::create([
                'message' => "Votre document \"{$document->titre}\" a ete valide.",
                'type' => 'validation',
                'id_destinataire' => $document->id_utilisateur,
                'id_chantier' => $document->id_chantier,
            ]);
        }

        if ($document->statut === 'rejete') {
            Notification::create([
                'message' => "Votre document \"{$document->titre}\" a ete rejete."
                    .($document->commentaire_admin ? " Motif : {$document->commentaire_admin}" : ''),
                'type' => 'rejet',
                'id_destinataire' => $document->id_utilisateur,
                'id_chantier' => $document->id_chantier,
            ]);
        }
    }

    private function admins(): array
    {
        return Utilisateur::where('role', 'admin')->where('actif', true)->pluck('id')->all();
    }
}
