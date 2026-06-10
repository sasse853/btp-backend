<?php

namespace App\Policies\Concerns;

use App\Models\Chantier;
use App\Models\Utilisateur;

/**
 * Logique d'autorisation partagee par les ressources rattachees a un chantier.
 * Le chef de chantier n'agit que sur ses chantiers assignes ; l'admin sur tout.
 */
trait AutoriseViaChantier
{
    /** L'admin a tous les droits (court-circuite les verifications fines). */
    public function before(Utilisateur $user, string $ability): ?bool
    {
        return $user->estAdmin() ? true : null;
    }

    protected function chefPeutAccederChantier(Utilisateur $user, ?int $idChantier): bool
    {
        if (! $user->estChef() || ! $idChantier) {
            return false;
        }

        return Chantier::where('id', $idChantier)
            ->where('id_chef_chantier', $user->id)
            ->exists();
    }
}
