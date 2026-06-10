<?php

namespace App\Policies;

use App\Models\Avenant;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class AvenantPolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Avenant $avenant): bool
    {
        return $this->chefPeutAccederChantier($user, $avenant->id_chantier);
    }

   public function update(Utilisateur $user, Avenant $avenant): bool
    {
        return in_array($avenant->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $avenant->id_chantier);
    }

    public function delete(Utilisateur $user, Avenant $avenant): bool
    {
        return in_array($avenant->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $avenant->id_chantier);
    }

    /** Approbation / rejet reserve a l'admin (before()). */
    public function valider(Utilisateur $user, Avenant $avenant): bool
    {
        return false;
    }
}
