<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable implements AuthenticatableContract
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'utilisateurs';

    /** Le schema utilise date_creation a la place de created_at/updated_at. */
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'mot_de_passe',
        'role',
        'actif',
        'date_creation',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'date_creation' => 'datetime',
            'mot_de_passe' => 'hashed',
        ];
    }

    /** Laravel doit lire le mot de passe dans la colonne mot_de_passe. */
    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    // --- Helpers de role ---

    public function estAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function estChef(): bool
    {
        return $this->role === 'chef_chantier';
    }

    // --- Accessors ---

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // --- Relations ---

    public function chantiers(): HasMany
    {
        return $this->hasMany(Chantier::class, 'id_chef_chantier');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'id_utilisateur');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'id_utilisateur');
    }

    public function avenants(): HasMany
    {
        return $this->hasMany(Avenant::class, 'id_demandeur');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_expediteur');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'id_destinataire');
    }
}
