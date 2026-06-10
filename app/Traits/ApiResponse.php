<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

/**
 * Format de reponse homogene pour toute l'API :
 * { "success": bool, "message": string, "data": mixed }.
 */
trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Operation reussie.', int $code = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        // Conserve les metadonnees de pagination lorsque c'est pertinent.
        if ($data instanceof AbstractPaginator) {
            $payload['data'] = $data->items();
            $payload['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return response()->json($payload, $code);
    }

    protected function error(string $message = 'Une erreur est survenue.', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], fn ($v) => ! is_null($v)), $code);
    }
}
