<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChantierRequest;
use App\Http\Resources\ChantierResource;
use App\Models\Chantier;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    /** Liste des chefs de chantier actifs (affectation lors de la creation). */
    public function chefs(): JsonResponse
    {
        $chefs = Utilisateur::where('role', 'chef_chantier')
            ->where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom'])
            ->map(fn (Utilisateur $u) => [
                'id' => $u->id,
                'nom_complet' => $u->nom_complet,
            ]);

        return $this->success($chefs, 'Liste des chefs de chantier.');
    }

    /** Liste paginee : l'admin voit tout, le chef uniquement ses chantiers. */
    public function index(Request $request): JsonResponse
    {
        $query = Chantier::query()
            ->with('chef')
            ->withCount('personnel');

        if ($request->user()->estChef()) {
            $query->where('id_chef_chantier', $request->user()->id);
        }

        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }

        $chantiers = $query->orderByDesc('date_creation')
            ->paginate(15)
            ->through(fn (Chantier $c) => new ChantierResource($c));

        return $this->success($chantiers, 'Liste des chantiers.');
    }

    /** Creation reservee a l'admin (Policy create). */
    public function store(ChantierRequest $request): JsonResponse
    {
        $this->authorize('create', Chantier::class);

        $chantier = Chantier::create($request->validated());

        return $this->success(
            new ChantierResource($chantier->load('chef')),
            'Chantier cree avec succes.',
            201
        );
    }

    public function show(Chantier $chantier): JsonResponse
    {
        $this->authorize('view', $chantier);

        $chantier->load(['chef', 'personnel', 'materiaux', 'equipements'])
            ->loadCount(['personnel', 'finances', 'avenants', 'documents']);

        return $this->success(new ChantierResource($chantier));
    }

    public function update(ChantierRequest $request, Chantier $chantier): JsonResponse
    {
        $this->authorize('update', $chantier);

        $chantier->update($request->validated());

        return $this->success(
            new ChantierResource($chantier->load('chef')),
            'Chantier mis a jour.'
        );
    }

    /** Suppression reservee a l'admin (Policy delete). */
    public function destroy(Chantier $chantier): JsonResponse
    {
        $this->authorize('delete', $chantier);

        $chantier->delete();

        return $this->success(null, 'Chantier supprime.');
    }
}
