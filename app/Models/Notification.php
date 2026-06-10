<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    public $timestamps = false;

    protected $fillable = [
        'message',
        'type',
        'lu',
        'id_destinataire',
        'id_chantier',
        'date_creation',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'date_creation' => 'datetime',
        ];
    }

    // --- Relations ---

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_destinataire');
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'id_chantier');
    }
}
