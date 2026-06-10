<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MateriauRequest;
use App\Http\Resources\MateriauResource;
use App\Models\Materiau;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MateriauController extends Controller
{
    use HandlesFileUpload;

    public function index(Request $request): JsonResponse
    {
        $query = Materiau::query()->with('chantier');

        $this->scopeChantierAccessible($query, $request->user());

        if ($idChantier = $request->query('id_chantier')) {
            $query->where('id_chantier', $idChantier);
        }

        $materiaux = $query->orderByDesc('id')
            ->paginate(15)
            ->through(fn (Materiau $m) => new MateriauResource($m));

        return $this->success($materiaux, 'Liste des materiaux.');
    }

    public function store(MateriauRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        if ($request->hasFile('justificatif')) {
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'materiaux');
        }

        $materiau = Materiau::create($data);

        return $this->success(
            new MateriauResource($materiau->load('chantier')),
            'Materiau enregistre.',
            201
        );
    }

    public function show(Materiau $materiau): JsonResponse
    {
        $this->authorize('view', $materiau);

        return $this->success(new MateriauResource($materiau->load('chantier')));
    }

    public function update(MateriauRequest $request, Materiau $materiau): JsonResponse
    {
        $this->authorize('update', $materiau);

        $data = $request->validated();

        if ($request->hasFile('justificatif')) {
            $this->supprimerFichier($materiau->justificatif);
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'materiaux');
        }

        $materiau->update($data);

        return $this->success(
            new MateriauResource($materiau->load('chantier')),
            'Materiau mis a jour.'
        );
    }

    public function destroy(Materiau $materiau): JsonResponse
    {
        $this->authorize('delete', $materiau);

        $this->supprimerFichier($materiau->justificatif);
        $materiau->delete();

        return $this->success(null, 'Materiau supprime.');
    }
}
