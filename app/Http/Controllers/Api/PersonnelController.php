<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonnelRequest;
use App\Http\Resources\PersonnelResource;
use App\Models\Personnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Personnel::query()->with('chantier');

        $this->scopeChantierAccessible($query, $request->user());

        if ($idChantier = $request->query('id_chantier')) {
            $query->where('id_chantier', $idChantier);
        }

        $personnel = $query->orderBy('nom')
            ->paginate(15)
            ->through(fn (Personnel $p) => new PersonnelResource($p));

        return $this->success($personnel, 'Liste du personnel.');
    }

    public function store(PersonnelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        $personnel = Personnel::create($data);

        return $this->success(
            new PersonnelResource($personnel->load('chantier')),
            'Personnel ajoute.',
            201
        );
    }

    public function show(Personnel $personnel): JsonResponse
    {
        $this->authorize('view', $personnel);

        return $this->success(new PersonnelResource($personnel->load('chantier')));
    }

    public function update(PersonnelRequest $request, Personnel $personnel): JsonResponse
    {
        $this->authorize('update', $personnel);

        $personnel->update($request->validated());

        return $this->success(
            new PersonnelResource($personnel->load('chantier')),
            'Personnel mis a jour.'
        );
    }

    public function destroy(Personnel $personnel): JsonResponse
    {
        $this->authorize('delete', $personnel);

        $personnel->delete();

        return $this->success(null, 'Personnel supprime.');
    }
}
