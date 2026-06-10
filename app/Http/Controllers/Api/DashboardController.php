<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChantierResource;
use App\Models\Avenant;
use App\Models\Chantier;
use App\Models\Document;
use App\Models\Finance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return $user->estAdmin()
            ? $this->success($this->dashboardAdmin(), 'Tableau de bord administrateur.')
            : $this->success($this->dashboardChef($user->id), 'Tableau de bord chef de chantier.');
    }

    private function dashboardAdmin(): array
    {
        $chantiers = Chantier::with('chef')->get();

        $alertes = $chantiers
            ->filter(fn (Chantier $c) => $c->pourcentage_consomme >= 80)
            ->map(fn (Chantier $c) => [
                'id' => $c->id,
                'nom' => $c->nom,
                'reference' => $c->reference,
                'pourcentage_consomme' => $c->pourcentage_consomme,
                'budget' => (float) ($c->budget_consolide ?? $c->budget_initial),
                'depenses_engagees' => $c->depenses_engagees,
            ])
            ->values();

        return [
            'kpis' => [
                'nb_chantiers' => $chantiers->count(),
                'budget_global' => round($chantiers->sum(fn ($c) => (float) ($c->budget_consolide ?? $c->budget_initial)), 2),
                'depenses_globales' => round($chantiers->sum->depenses_engagees, 2),
                'nb_chantiers_actifs' => $chantiers->where('statut', 'en_cours')->count(),
            ],
            'repartition_statuts' => $chantiers->countBy('statut'),
            'validations_en_attente' => [
                'finances' => Finance::where('statut', 'en_attente')->count(),
                'documents' => Document::where('statut', 'en_attente')->count(),
                'avenants' => Avenant::where('statut', 'en_attente')->count(),
            ],
            'alertes_budget' => $alertes,
            'depenses_par_categorie' => $this->depensesParCategorie(),
            'depenses_par_mois' => $this->depensesParMois(),
            'chantiers' => ChantierResource::collection(
                $chantiers->sortByDesc('date_creation')->take(10)->values()
            ),
        ];
    }

    private function dashboardChef(int $idChef): array
    {
        $chantiers = Chantier::where('id_chef_chantier', $idChef)->get();
        $idsChantiers = $chantiers->pluck('id');

        return [
            'kpis' => [
                'nb_chantiers' => $chantiers->count(),
                'budget_global' => round($chantiers->sum(fn ($c) => (float) ($c->budget_consolide ?? $c->budget_initial)), 2),
                'depenses_globales' => round($chantiers->sum->depenses_engagees, 2),
                'nb_chantiers_actifs' => $chantiers->where('statut', 'en_cours')->count(),
            ],
            'mes_demandes' => [
                'finances_en_attente' => Finance::whereIn('id_chantier', $idsChantiers)->where('statut', 'en_attente')->count(),
                'documents_en_attente' => Document::whereIn('id_chantier', $idsChantiers)->where('statut', 'en_attente')->count(),
                'avenants_en_attente' => Avenant::whereIn('id_chantier', $idsChantiers)->where('statut', 'en_attente')->count(),
            ],
            'depenses_par_categorie' => $this->depensesParCategorie($idsChantiers->all()),
            'chantiers' => ChantierResource::collection($chantiers),
        ];
    }

    /** Total des depenses validees regroupees par categorie. */
    private function depensesParCategorie(?array $idsChantiers = null): array
    {
        $query = Finance::where('statut', 'valide')
            ->whereIn('type_operation', ['depense', 'facture', 'avance_acompte'])
            ->select('categorie', DB::raw('SUM(montant) as total'))
            ->groupBy('categorie');

        if ($idsChantiers !== null) {
            $query->whereIn('id_chantier', $idsChantiers);
        }

        return $query->pluck('total', 'categorie')
            ->map(fn ($t) => round((float) $t, 2))
            ->all();
    }

    /** Evolution des depenses validees sur les 6 derniers mois. */
    private function depensesParMois(): array
    {
        return Finance::where('statut', 'valide')
            ->whereIn('type_operation', ['depense', 'facture', 'avance_acompte'])
            ->where('date_operation', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("TO_CHAR(date_operation, 'YYYY-MM') as mois"),
                DB::raw('SUM(montant) as total')
            )
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($ligne) => [
                'mois' => $ligne->mois,
                'total' => round((float) $ligne->total, 2),
            ])
            ->all();
    }
}
