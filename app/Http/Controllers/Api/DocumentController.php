<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use HandlesFileUpload;

    public function index(Request $request): JsonResponse
    {
        $query = Document::query()->with(['chantier', 'utilisateur']);

        $this->scopeChantierAccessible($query, $request->user());

        foreach (['id_chantier', 'statut', 'type_document'] as $filtre) {
            if ($valeur = $request->query($filtre)) {
                $query->where($filtre, $valeur);
            }
        }

        $documents = $query->orderByDesc('date_upload')
            ->paginate(15)
            ->through(fn (Document $d) => new DocumentResource($d));

        return $this->success($documents, 'Liste des documents.');
    }

    public function store(DocumentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->assertAccesChantier($request->user(), (int) $data['id_chantier']);

        $data['fichier'] = $this->stockerFichier($request->file('fichier'), 'documents');
        $data['id_utilisateur'] = $request->user()->id;
        $data['statut'] = 'en_attente';
        $data['date_upload'] = now();

        // L'observer notifie les admins (NouveauDocument).
        $document = Document::create($data);

        return $this->success(
            new DocumentResource($document->load(['chantier', 'utilisateur'])),
            'Document televerse.',
            201
        );
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return $this->success(new DocumentResource($document->load(['chantier', 'utilisateur'])));
    }

    public function update(DocumentRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $data = $request->validated();

        if ($request->hasFile('fichier')) {
            $this->supprimerFichier($document->fichier);
            $data['fichier'] = $this->stockerFichier($request->file('fichier'), 'documents');
        }

        // Remet en attente après correction suite à un rejet
        $data['statut'] = 'en_attente';
        $data['commentaire_admin'] = null;

        $document->update($data);

        return $this->success(
            new DocumentResource($document->load(['chantier', 'utilisateur'])),
            'Document mis a jour et resoumis.'
        );
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->supprimerFichier($document->fichier);
        $document->delete();

        return $this->success(null, 'Document supprime.');
    }

    /** Telechargement du fichier (acces controle par la Policy). */
    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        $disque = Storage::disk('public');
        abort_unless($document->fichier && $disque->exists($document->fichier), 404, 'Fichier introuvable.');

        $extension = pathinfo($document->fichier, PATHINFO_EXTENSION);
        $nom = Str::slug($document->titre).'.'.$extension;

        return $disque->download($document->fichier, $nom);
    }

    /** Validation / rejet par l'admin. */
    public function valider(Request $request, Document $document): JsonResponse
    {
        $this->authorize('valider', $document);

        $valide = $request->validate([
            'statut' => ['required', Rule::in(['valide', 'rejete'])],
            'commentaire_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $document->update($valide);

        $message = $valide['statut'] === 'valide'
            ? 'Document valide.'
            : 'Document rejete.';

        return $this->success(
            new DocumentResource($document->load(['chantier', 'utilisateur'])),
            $message
        );
    }
}
