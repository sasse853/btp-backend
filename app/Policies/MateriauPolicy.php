<?php

namespace App\Policies;

use App\Models\Materiau;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class MateriauPolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Materiau $materiau): bool
    {
        return $this->chefPeutAccederChantier($user, $materiau->id_chantier);
    }

    public function update(Utilisateur $user, Materiau $materiau): bool
    {
        return $this->chefPeutAccederChantier($user, $materiau->id_chantier);
    }

    public function delete(Utilisateur $user, Materiau $materiau): bool
    {
        return $this->chefPeutAccederChantier($user, $materiau->id_chantier);
    }
}
