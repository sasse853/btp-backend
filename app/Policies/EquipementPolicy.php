<?php

namespace App\Policies;

use App\Models\Equipement;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class EquipementPolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Equipement $equipement): bool
    {
        return $this->chefPeutAccederChantier($user, $equipement->id_chantier);
    }

    public function update(Utilisateur $user, Equipement $equipement): bool
    {
        return $this->chefPeutAccederChantier($user, $equipement->id_chantier);
    }

    public function delete(Utilisateur $user, Equipement $equipement): bool
    {
        return $this->chefPeutAccederChantier($user, $equipement->id_chantier);
    }
}
