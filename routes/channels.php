<?php

use App\Models\Chantier;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Broadcast;

// Canal prive personnel : chaque utilisateur recoit ses notifications.
Broadcast::channel('user.{id}', function (Utilisateur $user, int $id) {
    return (int) $user->id === $id;
});

// Canal prive admin : reserve aux administrateurs (nouvelles depenses, alertes, documents...).
Broadcast::channel('admin', function (Utilisateur $user) {
    return $user->role === 'admin';
});

// Canal prive par chantier : l'admin a acces a tout, le chef seulement a ses chantiers.
Broadcast::channel('chantier.{id}', function (Utilisateur $user, int $id) {
    if ($user->role === 'admin') {
        return true;
    }

    return Chantier::where('id', $id)
        ->where('id_chef_chantier', $user->id)
        ->exists();
});
