<?php

namespace App\Observers;

use App\Events\ChangementStatut;
use App\Models\Chantier;
use App\Models\Notification;
use App\Models\Utilisateur;

class ChantierObserver
{
    /** Genere la reference CH-AAAA-NNN et initialise le budget consolide. */
    public function creating(Chantier $chantier): void
    {
        if (empty($chantier->budget_consolide)) {
            $chantier->budget_consolide = $chantier->budget_initial;
        }

        if (empty($chantier->reference)) {
            $chantier->reference = $this->genererReference();
        }
    }

    /** Notifie le chef et l'admin lorsque le statut du chantier change. */
    public function updated(Chantier $chantier): void
    {
        if (! $chantier->wasChanged('statut')) {
            return;
        }

        $ancien = $chantier->getOriginal('statut');
        $nouveau = $chantier->statut;

        Notification::create([
            'message' => "Le chantier \"{$chantier->nom}\" est passe de \"{$ancien}\" a \"{$nouveau}\".",
            'type' => 'alerte',
            'id_destinataire' => $chantier->id_chef_chantier,
            'id_chantier' => $chantier->id,
        ]);

        broadcast(new ChangementStatut($chantier, (string) $ancien, (string) $nouveau));
    }

    private function genererReference(): string
    {
        $annee = now()->year;

        $dernier = Chantier::whereYear('date_creation', $annee)
            ->orWhere('reference', 'like', "CH-{$annee}-%")
            ->count();

        $sequence = str_pad((string) ($dernier + 1), 3, '0', STR_PAD_LEFT);

        return "CH-{$annee}-{$sequence}";
    }
}
