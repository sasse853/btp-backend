<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'poste',
        'type_contrat',
        'taux_journalier',
        'date_entree',
        'date_sortie_prevue',
        'numero_cni',
        'id_chantier',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'taux_journalier' => 'decimal:2',
            'date_entree' => 'date',
            'date_sortie_prevue' => 'date',
        ];
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class, 'id_personnel');
    }
}
