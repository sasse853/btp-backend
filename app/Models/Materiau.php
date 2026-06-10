<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materiau extends Model
{
    use HasFactory;

    protected $table = 'materiaux';

    public $timestamps = false;

    protected $fillable = [
        'designation',
        'quantite_commandee',
        'unite',
        'quantite_recue',
        'quantite_utilisee',
        'prix_unitaire',
        'fournisseur',
        'date_livraison',
        'justificatif',
        'id_chantier',
        'observations',
    ];

    protected $appends = [
        'stock_restant',
        'cout_total',
    ];

    protected function casts(): array
    {
        return [
            'quantite_commandee' => 'decimal:2',
            'quantite_recue' => 'decimal:2',
            'quantite_utilisee' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'date_livraison' => 'date',
        ];
    }

    /** Stock restant = quantite recue - quantite utilisee. */
    public function getStockRestantAttribute(): float
    {
        return (float) $this->quantite_recue - (float) $this->quantite_utilisee;
    }

    public function getCoutTotalAttribute(): float
    {
        return round((float) $this->quantite_commandee * (float) $this->prix_unitaire, 2);
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }
}
