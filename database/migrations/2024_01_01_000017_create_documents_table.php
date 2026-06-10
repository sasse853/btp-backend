<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->enum('type_document', ['plan', 'contrat', 'rapport', 'pv', 'fiche_securite', 'autre']);
            $table->string('fichier', 255);
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire_admin')->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnUpdate();
            $table->dateTime('date_upload')->useCurrent();

            $table->index('id_chantier');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
