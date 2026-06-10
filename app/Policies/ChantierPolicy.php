<?php

namespace App\Policies;

use App\Models\Chantier;
use App\Models\Utilisateur;

class ChantierPolicy
{
    /** L'admin a tous les droits sur tous les chantiers. */
    public function before(Utilisateur $user, string $ability): ?bool
    {
        return $user->estAdmin() ? true : null;
    }

    public function viewAny(Utilisateur $user): bool
    {
        return true; // Le scope (admin = tout / chef = les siens) est applique dans le controleur.
    }

    public function view(Utilisateur $user, Chantier $chantier): bool
    {
        return $this->estChefDuChantier($user, $chantier);
    }

    public function create(Utilisateur $user): bool
    {
        // Seul l'admin cree des chantiers (gere par before()). Le chef en est exclu.
        return false;
    }

    public function update(Utilisateur $user, Chantier $chantier): bool
    {
        return $this->estChefDuChantier($user, $chantier);
    }

    public function delete(Utilisateur $user, Chantier $chantier): bool
    {
        // Suppression reservee a l'admin (gere par before()).
        return false;
    }

    private function estChefDuChantier(Utilisateur $user, Chantier $chantier): bool
    {
        return $user->estChef() && (int) $chantier->id_chef_chantier === (int) $user->id;
    }
}
