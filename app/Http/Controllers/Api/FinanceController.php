<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinanceRequest;
use App\Http\Resources\FinanceResource;
use App\Models\Finance;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    use HandlesFileUpload;

    public function index(Request $request): JsonResponse
    {
        $query = Finance::query()->with(['chantier', 'utilisateur']);

        $this->scopeChantierAccessible($query, $request->user());

        foreach (['id_chantier', 'statut', 'type_operation', 'categorie'] as $filtre) {
            if ($valeur = $request->query($filtre)) {
                $query->where($filtre, $valeur);
            }
        }

        $finances = $query->orderByDesc('date_operation')
            ->paginate(15)
            ->through(fn (Finance $f) => new FinanceResource($f));

        return $this->success($finances, 'Liste des operations financieres.');
    }

    public function store(FinanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        if ($request->hasFile('justificatif')) {
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'finances');
        }

        $data['id_utilisateur'] = $request->user()->id;
        $data['statut'] = 'en_attente';

        // L'observer notifie les admins (NouvelleDepense).
        $finance = Finance::create($data);

        return $this->success(
            new FinanceResource($finance->load(['chantier', 'utilisateur'])),
            'Operation financiere soumise.',
            201
        );
    }

    public function show(Finance $finance): JsonResponse
    {
        $this->authorize('view', $finance);

        return $this->success(new FinanceResource($finance->load(['chantier', 'utilisateur'])));
    }

    public function update(FinanceRequest $request, Finance $finance): JsonResponse
    {
        $this->authorize('update', $finance);

        $data = $request->validated();

        if ($request->hasFile('justificatif')) {
            $this->supprimerFichier($finance->justificatif);
            $data['justificatif'] = $this->stockerFichier($request->file('justificatif'), 'finances');
        }

        // Remet en attente après correction suite à un rejet
        $data['statut'] = 'en_attente';
        $data['commentaire_admin'] = null;

        $finance->update($data);

        return $this->success(
            new FinanceResource($finance->load(['chantier', 'utilisateur'])),
            'Operation financiere mise a jour et resoumise.'
        );
    }

    public function destroy(Finance $finance): JsonResponse
    {
        $this->authorize('delete', $finance);

        $this->supprimerFichier($finance->justificatif);
        $finance->delete();

        return $this->success(null, 'Operation financiere supprimee.');
    }

    /** Validation / rejet par l'admin (declenche l'observer : seuil 80 %, notifications). */
    public function valider(Request $request, Finance $finance): JsonResponse
    {
        $this->authorize('valider', $finance);

        $valide = $request->validate([
            'statut' => ['required', Rule::in(['valide', 'rejete'])],
            'commentaire_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $finance->update($valide);

        $message = $valide['statut'] === 'valide'
            ? 'Operation validee.'
            : 'Operation rejetee.';

        return $this->success(
            new FinanceResource($finance->load(['chantier', 'utilisateur'])),
            $message
        );
    }
}
