<?php

namespace App\Observers;

use App\Models\Finance;
use App\Models\Presence;

class PresenceObserver
{
    /** Calcule automatiquement le montant du a partir du taux et du statut. */
    public function saving(Presence $presence): void
    {
        $presence->loadMissing('personnel');
        $presence->montant_du = $presence->calculerMontant();
    }

    /** Cree/met a jour la depense main d'oeuvre du jour pour le chantier. */
    public function created(Presence $presence): void
    {
        $this->synchroniserDepenseMainOeuvre($presence);
    }

    public function updated(Presence $presence): void
    {
        if ($presence->wasChanged('montant_du')) {
            $this->synchroniserDepenseMainOeuvre($presence);
        }
    }

    /**
     * Agrege le total des presences du jour en une operation financiere
     * "main_oeuvre" (une ligne par chantier et par date).
     */
    private function synchroniserDepenseMainOeuvre(Presence $presence): void
    {
        $date = $presence->date_presence?->toDateString()
            ?? (string) $presence->date_presence;

        $total = Presence::where('id_chantier', $presence->id_chantier)
            ->whereDate('date_presence', $date)
            ->sum('montant_du');

        if ((float) $total <= 0) {
            return;
        }

        $libelle = "Main d'oeuvre journaliere du {$date}";

        Finance::updateOrCreate(
            [
                'id_chantier' => $presence->id_chantier,
                'categorie' => 'main_oeuvre',
                'date_operation' => $date,
                'type_operation' => 'depense',
            ],
            [
                'libelle' => $libelle,
                'montant' => $total,
                'statut' => 'valide', // paie deja engagee : consideree comme depense effective
                'id_utilisateur' => optional($presence->chantier)->id_chef_chantier
                    ?? $presence->chantier()->value('id_chef_chantier'),
            ]
        );
    }
}
