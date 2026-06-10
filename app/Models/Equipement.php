<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipement extends Model
{
    use HasFactory;

    protected $table = 'equipements';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'reference',
        'type_mise_dispo',
        'fournisseur',
        'cout_journalier',
        'date_affectation',
        'date_retour_prevue',
        'etat',
        'justificatif',
        'id_chantier',
    ];

    protected $appends = [
        'cout_total_location',
        'nb_jours_location',
    ];

    protected function casts(): array
    {
        return [
            'cout_journalier' => 'decimal:2',
            'date_affectation' => 'date',
            'date_retour_prevue' => 'date',
        ];
    }

    /** Nombre de jours entre l'affectation et le retour prevu. */
    public function getNbJoursLocationAttribute(): int
    {
        if (! $this->date_retour_prevue || ! $this->date_affectation) {
            return 0;
        }

        return max(0, $this->date_affectation->diffInDays($this->date_retour_prevue));
    }

    /** Cout total de location = cout journalier x nombre de jours. */
    public function getCoutTotalLocationAttribute(): float
    {
        if ($this->type_mise_dispo !== 'location') {
            return 0;
        }

        return round((float) $this->cout_journalier * $this->nb_jours_location, 2);
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }
}
