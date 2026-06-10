<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Utilisateur;
use App\Policies\Concerns\AutoriseViaChantier;

class DocumentPolicy
{
    use AutoriseViaChantier;

    public function view(Utilisateur $user, Document $document): bool
    {
        return $this->chefPeutAccederChantier($user, $document->id_chantier);
    }

    public function update(Utilisateur $user, Document $document): bool
    {
        return in_array($document->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $document->id_chantier);
    }

    public function delete(Utilisateur $user, Document $document): bool
    {
        return in_array($document->statut, ['en_attente', 'rejete'])
            && $this->chefPeutAccederChantier($user, $document->id_chantier);
    }

    /** Validation / rejet reserve a l'admin (before()). */
    public function valider(Utilisateur $user, Document $document): bool
    {
        return false;
    }
}
