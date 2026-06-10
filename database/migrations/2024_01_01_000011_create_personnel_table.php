<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('telephone', 20)->nullable();
            $table->string('poste', 100);
            $table->enum('type_contrat', ['cdi', 'cdd', 'journalier', 'prestataire']);
            $table->decimal('taux_journalier', 10, 2)->nullable();
            $table->date('date_entree');
            $table->date('date_sortie_prevue')->nullable();
            $table->string('numero_cni', 50)->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->text('observations')->nullable();

            $table->index('id_chantier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};
