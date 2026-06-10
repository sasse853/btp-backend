<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileUpload
{
    /**
     * Stocke un fichier uploade sous un nom uuid sur le disque public et
     * retourne le chemin relatif a enregistrer en base.
     */
    protected function stockerFichier(?UploadedFile $fichier, string $dossier = 'justificatifs'): ?string
    {
        if (! $fichier) {
            return null;
        }

        $nom = Str::uuid()->toString().'.'.$fichier->getClientOriginalExtension();

        return $fichier->storeAs($dossier, $nom, 'public');
    }

    /** Supprime un fichier du disque public s'il existe. */
    protected function supprimerFichier(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}
