<?php

namespace App\Observers;

use App\Events\AvenantApprouve;
use App\Events\NouvelAvenant;
use App\Models\Avenant;
use App\Models\Notification;
use App\Models\Utilisateur;

class AvenantObserver
{
    /** Notifie l'admin a la soumission d'une demande d'avenant. */
    public function created(Avenant $avenant): void
    {
        foreach ($this->admins() as $adminId) {
            Notification::create([
                'message' => "Nouvelle demande d'avenant de "
                    .number_format((float) $avenant->montant_demande, 0, ',', ' ')
                    ." FCFA a valider.",
                'type' => 'avenant',
                'id_destinataire' => $adminId,
                'id_chantier' => $avenant->id_chantier,
            ]);
        }

        broadcast(new NouvelAvenant($avenant));
    }

    /** A l'approbation : recalcule le budget consolide du chantier + notifie le chef. */
    public function updated(Avenant $avenant): void
    {
        if (! $avenant->wasChanged('statut')) {
            return;
        }

        if ($avenant->statut === 'approuve') {
            $chantier = $avenant->chantier;
            $budget = $chantier ? $chantier->recalculerBudget() : 0.0;

            Notification::create([
                'message' => "Votre avenant de "
                    .number_format((float) $avenant->montant_demande, 0, ',', ' ')
                    ." FCFA a ete approuve. Nouveau budget : "
                    .number_format($budget, 0, ',', ' ')." FCFA.",
                'type' => 'validation',
                'id_destinataire' => $avenant->id_demandeur,
                'id_chantier' => $avenant->id_chantier,
            ]);

            broadcast(new AvenantApprouve($avenant, $budget));
        }

        if ($avenant->statut === 'rejete') {
            Notification::create([
                'message' => "Votre demande d'avenant a ete rejetee."
                    .($avenant->commentaire_admin ? " Motif : {$avenant->commentaire_admin}" : ''),
                'type' => 'rejet',
                'id_destinataire' => $avenant->id_demandeur,
                'id_chantier' => $avenant->id_chantier,
            ]);
        }
    }

    private function admins(): array
    {
        return Utilisateur::where('role', 'admin')->where('actif', true)->pluck('id')->all();
    }
}
