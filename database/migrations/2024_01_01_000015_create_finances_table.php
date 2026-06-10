<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 255);
            $table->enum('type_operation', ['depense', 'devis', 'facture', 'bon_livraison', 'avance_acompte']);
            $table->decimal('montant', 15, 2);
            $table->date('date_operation');
            $table->enum('categorie', ['main_oeuvre', 'materiaux', 'equipements', 'divers']);
            $table->string('justificatif', 255)->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire_admin')->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs')->cascadeOnUpdate();
            $table->dateTime('date_creation')->useCurrent();

            $table->index('id_chantier');
            $table->index('id_utilisateur');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
