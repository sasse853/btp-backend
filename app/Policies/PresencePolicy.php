<?php

namespace App\Policies;

use App\Models\Presence;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class PresencePolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Presence $presence): bool
    {
        return $this->chefPeutAccederChantier($user, $presence->id_chantier);
    }

    public function update(Utilisateur $user, Presence $presence): bool
    {
        return $this->chefPeutAccederChantier($user, $presence->id_chantier);
    }

    public function delete(Utilisateur $user, Presence $presence): bool
    {
        return $this->chefPeutAccederChantier($user, $presence->id_chantier);
    }
}
