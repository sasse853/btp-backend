<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class PersonnelPolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Personnel $personnel): bool
    {
        return $this->chefPeutAccederChantier($user, $personnel->id_chantier);
    }

    public function update(Utilisateur $user, Personnel $personnel): bool
    {
        return $this->chefPeutAccederChantier($user, $personnel->id_chantier);
    }

    public function delete(Utilisateur $user, Personnel $personnel): bool
    {
        return $this->chefPeutAccederChantier($user, $personnel->id_chantier);
    }
}
