<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    public $timestamps = false;

    protected $fillable = [
        'contenu',
        'fichier_joint',
        'lu',
        'id_chantier',
        'id_expediteur',
        'date_envoi',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'date_envoi' => 'datetime',
        ];
    }

    // --- Relations ---

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_expediteur');
    }
}
