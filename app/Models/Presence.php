<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presences';

    public $timestamps = false;

    protected $fillable = [
        'id_personnel',
        'id_chantier',
        'date_presence',
        'statut',
        'montant_du',
        'statut_paiement',
        'date_paiement',
    ];

    protected function casts(): array
    {
        return [
            'date_presence' => 'date',
            'montant_du' => 'decimal:2',
            'date_paiement' => 'date',
        ];
    }

    /** Coefficient de remuneration selon le statut de presence. */
    public const COEFFICIENTS = [
        'present' => 1.0,
        'demi_journee' => 0.5,
        'absent_justifie' => 0.0,
        'absent_non_justifie' => 0.0,
        'conge' => 0.0,
    ];

    /**
     * Montant du = taux journalier du personnel x coefficient du statut.
     * present 1.0 / demi_journee 0.5 / sinon 0.
     */
    public function calculerMontant(): float
    {
        $taux = (float) ($this->personnel?->taux_journalier ?? 0);
        $coef = self::COEFFICIENTS[$this->statut] ?? 0.0;

        return round($taux * $coef, 2);
    }

    // --- Relations ---

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'id_personnel');
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }
}
