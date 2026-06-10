<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UtilisateurResource;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Authentifie l'utilisateur et renvoie un token Sanctum. */
    public function login(LoginRequest $request): JsonResponse
    {
        $utilisateur = Utilisateur::where('email', $request->email)->first();

        if (! $utilisateur || ! Hash::check($request->mot_de_passe, $utilisateur->mot_de_passe)) {
            return $this->error('Identifiants incorrects.', 401);
        }

        if (! $utilisateur->actif) {
            return $this->error('Ce compte est desactive. Contactez un administrateur.', 403);
        }

        // Un seul token actif par session de connexion.
        $token = $utilisateur->createToken('api-token')->plainTextToken;

        return $this->success([
            'utilisateur' => new UtilisateurResource($utilisateur),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Connexion reussie.');
    }

    /** Revoque le token courant. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Deconnexion reussie.');
    }

    /** Retourne l'utilisateur authentifie. */
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UtilisateurResource($request->user()));
    }
}
