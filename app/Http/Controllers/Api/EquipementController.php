<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipementRequest;
use App\Http\Resources\EquipementResource;
use App\Models\Equipement;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipementController extends Controller
{
    use HandlesFileUpload;

    public function index(Request $request): JsonResponse
    {
        $query = Equipement::query()->with('chantier');

        $this->scopeChantierAccessible($query, $request->user());

        if ($idChantier = $request->query('id_chantier')) {
            $query->where('id_chantier', $idChantier);
        }

        $equipements = $query->orderByDesc('id')
            ->paginate(15)
            ->through(fn (Equipement $e) => new EquipementResource($e));

        return $this->success($equipements, 'Liste des equipements.');
    }

    public function store(EquipementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        if ($request->hasFile('justificatif')) {
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'equipements');
        }

        $equipement = Equipement::create($data);

        return $this->success(
            new EquipementResource($equipement->load('chantier')),
            'Equipement enregistre.',
            201
        );
    }

    public function show(Equipement $equipement): JsonResponse
    {
        $this->authorize('view', $equipement);

        return $this->success(new EquipementResource($equipement->load('chantier')));
    }

    public function update(EquipementRequest $request, Equipement $equipement): JsonResponse
    {
        $this->authorize('update', $equipement);

        $data = $request->validated();

        if ($request->hasFile('justificatif')) {
            $this->supprimerFichier($equipement->justificatif);
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'equipements');
        }

        $equipement->update($data);

        return $this->success(
            new EquipementResource($equipement->load('chantier')),
            'Equipement mis a jour.'
        );
    }

    public function destroy(Equipement $equipement): JsonResponse
    {
        $this->authorize('delete', $equipement);

        $this->supprimerFichier($equipement->justificatif);
        $equipement->delete();

        return $this->success(null, 'Equipement supprime.');
    }
}
