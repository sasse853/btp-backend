<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('id_destinataire', $request->user()->id)
            ->orderByDesc('date_creation')
            ->paginate(20)
            ->through(fn (Notification $n) => new NotificationResource($n));

        return $this->success($notifications, 'Liste des notifications.');
    }

    /** Notifications non lues + compteur (badge temps reel). */
    public function nonLues(Request $request): JsonResponse
    {
        $query = Notification::where('id_destinataire', $request->user()->id)
            ->where('lu', false);

        $notifications = (clone $query)
            ->orderByDesc('date_creation')
            ->limit(20)
            ->get();

        return $this->success([
            'total' => $query->count(),
            'notifications' => NotificationResource::collection($notifications),
        ], 'Notifications non lues.');
    }

    public function marquerLue(Request $request, Notification $notification): JsonResponse
    {
        $this->assertProprietaire($request, $notification);

        $notification->update(['lu' => true]);

        return $this->success(new NotificationResource($notification), 'Notification marquee comme lue.');
    }

    public function toutMarquerLu(Request $request): JsonResponse
    {
        $nombre = Notification::where('id_destinataire', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return $this->success(['marquees' => $nombre], 'Toutes les notifications ont ete marquees lues.');
    }

    /** @throws AuthorizationException */
    private function assertProprietaire(Request $request, Notification $notification): void
    {
        if ((int) $notification->id_destinataire !== (int) $request->user()->id) {
            throw new AuthorizationException('Cette notification ne vous appartient pas.');
        }
    }
}
