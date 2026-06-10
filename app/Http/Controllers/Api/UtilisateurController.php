<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UtilisateurResource;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UtilisateurController extends Controller
{
    /** Liste tous les utilisateurs (admin seulement). */
    public function index(): JsonResponse
    {
        $utilisateurs = Utilisateur::orderBy('date_creation', 'desc')->get();
        return $this->success(UtilisateurResource::collection($utilisateurs));
    }

    /** Crée un nouvel utilisateur (admin seulement). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'email'      => 'required|email|unique:utilisateurs,email',
            'telephone'  => 'nullable|string|max:20',
            'mot_de_passe' => ['required', 'string', Password::min(8)],
            'role'       => 'required|in:admin,chef_chantier',
        ]);

        $utilisateur = Utilisateur::create([
            ...$data,
            'actif'          => true,
            'date_creation'  => now(),
        ]);

        return $this->success(new UtilisateurResource($utilisateur), 'Utilisateur créé.', 201);
    }

    /** Active ou désactive un compte (admin seulement). */
    public function toggleActif(Utilisateur $utilisateur): JsonResponse
    {
        $utilisateur->update(['actif' => ! $utilisateur->actif]);
        $statut = $utilisateur->actif ? 'activé' : 'désactivé';
        return $this->success(new UtilisateurResource($utilisateur), "Compte {$statut}.");
    }

    /** Permet à n'importe quel utilisateur connecté de changer son mot de passe. */
    public function changerMotDePasse(Request $request): JsonResponse
    {
        $request->validate([
            'mot_de_passe_actuel' => 'required|string',
            'nouveau_mot_de_passe' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $utilisateur = $request->user();

        if (! Hash::check($request->mot_de_passe_actuel, $utilisateur->mot_de_passe)) {
            return $this->error('Mot de passe actuel incorrect.', 422);
        }

        $utilisateur->update(['mot_de_passe' => $request->nouveau_mot_de_passe]);

        return $this->success(null, 'Mot de passe modifié avec succès.');
    }


    /** Réinitialise le mot de passe d'un utilisateur (admin seulement). */
    public function reinitialiserMotDePasse(Request $request, Utilisateur $utilisateur): JsonResponse
    {
        $request->validate([
            'nouveau_mot_de_passe' => ['required', 'string', Password::min(8)],
        ]);

        $utilisateur->update(['mot_de_passe' => $request->nouveau_mot_de_passe]);

        return $this->success(null, 'Mot de passe réinitialisé avec succès.');
    }
}