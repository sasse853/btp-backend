<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RapportRequest;
use App\Models\Chantier;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    /** Sections disponibles dans le rapport client. */
    private const SECTIONS = [
        'infos', 'avancement', 'finances', 'personnel',
        'materiaux', 'equipements', 'documents', 'observations',
    ];

    /**
     * Genere le rapport client PDF d'un chantier (DomPDF).
     * Sections selectionnables + filtre periode + archivage optionnel.
     */
    public function generer(RapportRequest $request, Chantier $chantier)
    {
        $this->authorize('view', $chantier);

        $valide = $request->validated();
        $sections = ! empty($valide['sections'])
            ? array_intersect(self::SECTIONS, $valide['sections'])
            : self::SECTIONS;

        $dateDebut = $valide['date_debut'] ?? null;
        $dateFin = $valide['date_fin'] ?? null;

        $donnees = $this->collecterDonnees($chantier, $dateDebut, $dateFin);

        $pdf = Pdf::loadView('rapports.client', [
            'chantier' => $chantier,
            'sections' => $sections,
            'donnees' => $donnees,
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'observations' => $valide['observations'] ?? null,
            'genere_le' => now(),
            'genere_par' => $request->user(),
        ])->setPaper('a4');

        $nomFichier = 'rapport-'.Str::slug($chantier->reference ?? $chantier->nom).'-'.now()->format('Ymd-His').'.pdf';

        // Archivage optionnel dans la table documents.
        if (! empty($valide['archiver'])) {
            $chemin = 'documents/'.Str::uuid()->toString().'.pdf';
            Storage::disk('public')->put($chemin, $pdf->output());

            Document::create([
                'titre' => 'Rapport client - '.$chantier->nom.' ('.now()->format('d/m/Y').')',
                'type_document' => 'rapport',
                'fichier' => $chemin,
                'statut' => 'valide',
                'id_chantier' => $chantier->id,
                'id_utilisateur' => $request->user()->id,
                'date_upload' => now(),
            ]);
        }

        return $pdf->download($nomFichier);
    }

    private function collecterDonnees(Chantier $chantier, ?string $debut, ?string $fin): array
    {
        $filtrerPeriode = function ($query, string $colonne) use ($debut, $fin) {
            if ($debut) {
                $query->whereDate($colonne, '>=', $debut);
            }
            if ($fin) {
                $query->whereDate($colonne, '<=', $fin);
            }

            return $query;
        };

        $finances = $filtrerPeriode($chantier->finances()->where('statut', 'valide'), 'date_operation')
            ->orderBy('date_operation')->get();

        return [
            'personnel' => $chantier->personnel()->orderBy('nom')->get(),
            'materiaux' => $chantier->materiaux()->orderByDesc('id')->get(),
            'equipements' => $chantier->equipements()->orderByDesc('id')->get(),
            'finances' => $finances,
            'documents' => $chantier->documents()->where('statut', 'valide')->orderByDesc('date_upload')->get(),
            'total_depenses' => round((float) $finances
                ->whereIn('type_operation', ['depense', 'facture', 'avance_acompte'])
                ->sum('montant'), 2),
            'finances_par_categorie' => $finances
                ->whereIn('type_operation', ['depense', 'facture', 'avance_acompte'])
                ->groupBy('categorie')
                ->map(fn ($g) => round((float) $g->sum('montant'), 2)),
        ];
    }
}
