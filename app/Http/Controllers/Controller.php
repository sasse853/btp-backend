<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\Utilisateur;
use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use ApiResponse, AuthorizesRequests, ValidatesRequests;

    /**
     * Verifie qu'un utilisateur peut agir sur un chantier donne :
     * l'admin partout, le chef uniquement sur ses chantiers assignes.
     *
     * @throws AuthorizationException
     */
    protected function assertAccesChantier(Utilisateur $user, ?int $idChantier): void
    {
        if ($user->estAdmin()) {
            return;
        }

        $autorise = $idChantier && Chantier::where('id', $idChantier)
            ->where('id_chef_chantier', $user->id)
            ->exists();

        if (! $autorise) {
            throw new AuthorizationException("Acces non autorise a ce chantier.");
        }
    }

    /**
     * Applique le filtrage par chantier accessible sur une requete de
     * ressource enfant (chef = ses chantiers ; admin = pas de filtre).
     */
    protected function scopeChantierAccessible($query, Utilisateur $user, string $relation = 'chantier')
    {
        if ($user->estChef()) {
            $query->whereHas($relation, fn ($q) => $q->where('id_chef_chantier', $user->id));
        }

        return $query;
    }
}
