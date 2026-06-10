<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avenant extends Model
{
    use HasFactory;

    protected $table = 'avenants';

    public $timestamps = false;

    protected $fillable = [
        'montant_demande',
        'motif',
        'justificatif',
        'statut',
        'commentaire_admin',
        'id_chantier',
        'id_demandeur',
        'date_demande',
        'date_traitement',
    ];

    protected function casts(): array
    {
        return [
            'montant_demande' => 'decimal:2',
            'date_demande' => 'datetime',
            'date_traitement' => 'datetime',
        ];
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_demandeur');
    }
}
