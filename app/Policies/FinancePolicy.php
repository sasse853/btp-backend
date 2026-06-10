<?php

namespace App\Policies;

use App\Models\Finance;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class FinancePolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Finance $finance): bool
    {
        return $this->chefPeutAccederChantier($user, $finance->id_chantier);
    }

   public function update(Utilisateur $user, Finance $finance): bool
    {
        return in_array($finance->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $finance->id_chantier);
    }

    public function delete(Utilisateur $user, Finance $finance): bool
    {
        return in_array($finance->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $finance->id_chantier);
    }

    /** La validation / rejet est strictement reservee a l'admin (before()). */
    public function valider(Utilisateur $user, Finance $finance): bool
    {
        return false;
    }
}
