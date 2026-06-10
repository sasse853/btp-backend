<?php

namespace App\Observers;

use App\Events\AlerteBudget;
use App\Events\NouvelleDepense;
use App\Models\Finance;
use App\Models\Notification;
use App\Models\Utilisateur;

class FinanceObserver
{
    /** Seuil d'alerte budgetaire (80 % du budget consolide). */
    private const SEUIL_ALERTE = 80.0;

    /** Notifie l'admin a la creation d'une operation en attente de validation. */
    public function created(Finance $finance): void
    {
        if ($finance->statut === 'en_attente') {
            foreach ($this->admins() as $adminId) {
                Notification::create([
                    'message' => "Nouvelle operation \"{$finance->libelle}\" ("
                        .number_format((float) $finance->montant, 0, ',', ' ')
                        ." FCFA) en attente de validation.",
                    'type' => 'validation',
                    'id_destinataire' => $adminId,
                    'id_chantier' => $finance->id_chantier,
                ]);
            }

            broadcast(new NouvelleDepense($finance));
        }

        // Une depense deja validee (ex: paie) impacte directement le budget.
        if ($finance->statut === 'valide') {
            $this->verifierSeuilBudget($finance);
        }
    }

    /** Apres validation par l'admin : verifie le seuil de 80 % du budget. */
    public function updated(Finance $finance): void
    {
        if ($finance->wasChanged('statut') && $finance->statut === 'valide') {
            $this->verifierSeuilBudget($finance);

            Notification::create([
                'message' => "Votre operation \"{$finance->libelle}\" a ete validee.",
                'type' => 'validation',
                'id_destinataire' => $finance->id_utilisateur,
                'id_chantier' => $finance->id_chantier,
            ]);
        }

        if ($finance->wasChanged('statut') && $finance->statut === 'rejete') {
            Notification::create([
                'message' => "Votre operation \"{$finance->libelle}\" a ete rejetee."
                    .($finance->commentaire_admin ? " Motif : {$finance->commentaire_admin}" : ''),
                'type' => 'rejet',
                'id_destinataire' => $finance->id_utilisateur,
                'id_chantier' => $finance->id_chantier,
            ]);
        }
    }

    /** Cree une alerte si les depenses validees atteignent 80 % du budget. */
    private function verifierSeuilBudget(Finance $finance): void
    {
        $chantier = $finance->chantier()->first();

        if (! $chantier) {
            return;
        }

        $pourcentage = $chantier->pourcentage_consomme;

        if ($pourcentage < self::SEUIL_ALERTE) {
            return;
        }

        foreach ($this->admins() as $adminId) {
            Notification::create([
                'message' => "ALERTE BUDGET : le chantier \"{$chantier->nom}\" a consomme "
                    .round($pourcentage, 1)." % de son budget.",
                'type' => 'alerte',
                'id_destinataire' => $adminId,
                'id_chantier' => $chantier->id,
            ]);
        }

        broadcast(new AlerteBudget($chantier, $pourcentage));
    }

    private function admins(): array
    {
        return Utilisateur::where('role', 'admin')->where('actif', true)->pluck('id')->all();
    }
}
