<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PresenceRequest;
use App\Http\Resources\PresenceResource;
use App\Models\Presence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Presence::query()->with(['personnel', 'chantier']);

        $this->scopeChantierAccessible($query, $request->user());

        if ($idChantier = $request->query('id_chantier')) {
            $query->where('id_chantier', $idChantier);
        }

        if ($date = $request->query('date_presence')) {
            $query->whereDate('date_presence', $date);
        }

        $presences = $query->orderByDesc('date_presence')
            ->paginate(15)
            ->through(fn (Presence $p) => new PresenceResource($p));

        return $this->success($presences, 'Liste des presences.');
    }

    public function store(PresenceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        // L'observer calcule montant_du et synchronise la depense main d'oeuvre.
        $presence = Presence::updateOrCreate(
            [
                'id_personnel' => $data['id_personnel'],
                'date_presence' => $data['date_presence'],
            ],
            $data
        );

        return $this->success(
            new PresenceResource($presence->load(['personnel', 'chantier'])),
            'Presence enregistree.',
            201
        );
    }

    /** Saisie en lot : une feuille de presence complete pour un chantier / une date. */
    public function batch(PresenceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        $resultats = DB::transaction(function () use ($data) {
            $presences = [];

            foreach ($data['presences'] as $ligne) {
                $presences[] = Presence::updateOrCreate(
                    [
                        'id_personnel' => $ligne['id_personnel'],
                        'date_presence' => $data['date_presence'],
                    ],
                    [
                        'id_chantier' => $data['id_chantier'],
                        'statut' => $ligne['statut'],
                    ]
                );
            }

            return $presences;
        });

        $collection = collect($resultats)
            ->map(fn (Presence $p) => new PresenceResource($p->load('personnel')));

        return $this->success(
            $collection,
            'Feuille de presence enregistree ('.count($resultats).' lignes).',
            201
        );
    }

    public function show(Presence $presence): JsonResponse
    {
        $this->authorize('view', $presence);

        return $this->success(new PresenceResource($presence->load(['personnel', 'chantier'])));
    }

    public function update(PresenceRequest $request, Presence $presence): JsonResponse
    {
        $this->authorize('update', $presence);

        $presence->update($request->validated());

        return $this->success(
            new PresenceResource($presence->load(['personnel', 'chantier'])),
            'Presence mise a jour.'
        );
    }

    public function destroy(Presence $presence): JsonResponse
    {
        $this->authorize('delete', $presence);

        $presence->delete();

        return $this->success(null, 'Presence supprimee.');
    }
}
