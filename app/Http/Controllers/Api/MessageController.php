<?php

namespace App\Http\Controllers\Api;

use App\Events\NouveauMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Chantier;
use App\Models\Message;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use HandlesFileUpload;

    /** Fil de discussion d'un chantier (acces controle). */
    public function index(Request $request, Chantier $chantier): JsonResponse
    {
        $this->assertAccesChantier($request->user(), $chantier->id);

        $messages = $chantier->messages()
            ->with('expediteur')
            ->orderByDesc('date_envoi')
            ->paginate(30)
            ->through(fn (Message $m) => new MessageResource($m));

        return $this->success($messages, 'Fil de messages.');
    }

    public function store(MessageRequest $request, Chantier $chantier): JsonResponse
    {
        $this->assertAccesChantier($request->user(), $chantier->id);

        $data = $request->validated();

        if ($request->hasFile('fichier_joint')) {
            $data['fichier_joint'] = $this->stockerFichier($request->file('fichier_joint'), 'messages');
        }

        $message = $chantier->messages()->create([
            'contenu' => $data['contenu'],
            'fichier_joint' => $data['fichier_joint'] ?? null,
            'id_expediteur' => $request->user()->id,
            'date_envoi' => now(),
        ]);

        broadcast(new NouveauMessage($message))->toOthers();

        return $this->success(
            new MessageResource($message->load('expediteur')),
            'Message envoye.',
            201
        );
    }
}
