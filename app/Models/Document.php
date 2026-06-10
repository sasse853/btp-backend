<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    public $timestamps = false;

    protected $fillable = [
        'titre',
        'type_document',
        'fichier',
        'statut',
        'commentaire_admin',
        'id_chantier',
        'id_utilisateur',
        'date_upload',
    ];

    protected function casts(): array
    {
        return [
            'date_upload' => 'datetime',
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
