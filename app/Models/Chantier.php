<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chantier extends Model
{
    use HasFactory;

    protected $table = 'chantiers';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'reference',
        'adresse',
        'latitude',
        'longitude',
        'date_debut_prevue',
        'date_fin_prevue',
        'budget_initial',
        'budget_consolide',
        'maitre_ouvrage',
        'statut',
        'description',
        'id_chef_chantier',
        'date_creation',
    ];

    protected $appends = [
        'depenses_engagees',
        'solde',
        'pourcentage_consomme',
        'taux_avancement',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'date_debut_prevue' => 'date',
            'date_fin_prevue' => 'date',
            'budget_initial' => 'decimal:2',
            'budget_consolide' => 'decimal:2',
            'date_creation' => 'datetime',
        ];
    }

    /**
     * Budget consolide = budget initial + somme des avenants approuves.
     * Met a jour la colonne et la persiste.
     */
    public function recalculerBudget(): float
    {
        $sommeAvenants = (float) $this->avenants()
            ->where('statut', 'approuve')
            ->sum('montant_demande');

        $consolide = (float) $this->budget_initial + $sommeAvenants;

        $this->budget_consolide = $consolide;
        $this->saveQuietly();

        return $consolide;
    }

    // --- Accessors calcules (source unique de verite pour API / PDF / dashboard) ---

    /** Total des operations financieres validees de type depense. */
    public function getDepensesEngageesAttribute(): float
    {
        return (float) $this->finances()
            ->where('statut', 'valide')
            ->whereIn('type_operation', ['depense', 'facture', 'avance_acompte'])
            ->sum('montant');
    }

    public function getSoldeAttribute(): float
    {
        $budget = (float) ($this->budget_consolide ?? $this->budget_initial);

        return $budget - $this->depenses_engagees;
    }

    public function getPourcentageConsommeAttribute(): float
    {
        $budget = (float) ($this->budget_consolide ?? $this->budget_initial);

        if ($budget <= 0) {
            return 0;
        }

        return round(($this->depenses_engagees / $budget) * 100, 2);
    }

    /**
     * Estimation du taux d'avancement basee sur le statut.
     * (Valeur indicative en l'absence d'un champ dedie dans le cahier.)
     */
    public function getTauxAvancementAttribute(): int
    {
        return match ($this->statut) {
            'en_attente' => 0,
            'en_cours' => 50,
            'en_pause' => 50,
            'termine' => 100,
            'archive' => 100,
            default => 0,
        };
    }

    // --- Relations ---

    public function chef(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_chef_chantier');
    }

    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class, 'id_chantier');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class, 'id_chantier');
    }

    public function materiaux(): HasMany
    {
        return $this->hasMany(Materiau::class, 'id_chantier');
    }

    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class, 'id_chantier');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'id_chantier');
    }

    public function avenants(): HasMany
    {
        return $this->hasMany(Avenant::class, 'id_chantier');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'id_chantier');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_chantier');
    }
}
