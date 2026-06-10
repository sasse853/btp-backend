<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finance extends Model
{
    use HasFactory;

    protected $table = 'finances';

    public $timestamps = false;

    protected $fillable = [
        'libelle',
        'type_operation',
        'montant',
        'date_operation',
        'categorie',
        'justificatif',
        'statut',
        'commentaire_admin',
        'id_chantier',
        'id_utilisateur',
        'date_creation',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_operation' => 'date',
            'date_creation' => 'datetime',
        ];
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }
}
