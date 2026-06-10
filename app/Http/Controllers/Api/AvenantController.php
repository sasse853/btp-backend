<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvenantRequest;
use App\Http\Resources\AvenantResource;
use App\Models\Avenant;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AvenantController extends Controller
{
    use HandlesFileUpload;

    public function index(Request $request): JsonResponse
    {
        $query = Avenant::query()->with(['chantier', 'demandeur']);

        $this->scopeChantierAccessible($query, $request->user());

        foreach (['id_chantier', 'statut'] as $filtre) {
            if ($valeur = $request->query($filtre)) {
                $query->where($filtre, $valeur);
            }
        }

        $avenants = $query->orderByDesc('date_demande')
            ->paginate(15)
            ->through(fn (Avenant $a) => new AvenantResource($a));

        return $this->success($avenants, 'Liste des avenants.');
    }

    public function store(AvenantRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        if ($request->hasFile('justificatif')) {
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'avenants');
        }

        $data['id_demandeur'] = $request->user()->id;
        $data['statut'] = 'en_attente';
        $data['date_demande'] = now();

        // L'observer notifie les admins (NouvelAvenant).
        $avenant = Avenant::create($data);

        return $this->success(
            new AvenantResource($avenant->load(['chantier', 'demandeur'])),
            'Demande d\'avenant soumise.',
            201
        );
    }

    public function show(Avenant $avenant): JsonResponse
    {
        $this->authorize('view', $avenant);

        return $this->success(new AvenantResource($avenant->load(['chantier', 'demandeur'])));
    }

   public function update(AvenantRequest $request, Avenant $avenant): JsonResponse
    {
        $this->authorize('update', $avenant);

        $data = $request->validated();

        if ($request->hasFile('justificatif')) {
            $this->supprimerFichier($avenant->justificatif);
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'avenants');
        }

        // Remet en attente après correction suite à un rejet
        $data['statut'] = 'en_attente';
        $data['commentaire_admin'] = null;
        $data['date_traitement'] = null;

        $avenant->update($data);

        return $this->success(
            new AvenantResource($avenant->load(['chantier', 'demandeur'])),
            'Avenant mis a jour et resoumis.'
        );
    }

    public function destroy(Avenant $avenant): JsonResponse
    {
        $this->authorize('delete', $avenant);

        $this->supprimerFichier($avenant->justificatif);
        $avenant->delete();

        return $this->success(null, 'Avenant supprime.');
    }

    /** Approbation / rejet par l'admin (declenche le recalcul du budget consolide). */
    public function valider(Request $request, Avenant $avenant): JsonResponse
    {
        $this->authorize('valider', $avenant);

        $valide = $request->validate([
            'statut' => ['required', Rule::in(['approuve', 'rejete'])],
            'commentaire_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $valide['date_traitement'] = now();
        $avenant->update($valide);

        $message = $valide['statut'] === 'approuve'
            ? 'Avenant approuve, budget consolide recalcule.'
            : 'Avenant rejete.';

        return $this->success(
            new AvenantResource($avenant->load(['chantier', 'demandeur'])),
            $message
        );
    }
}
