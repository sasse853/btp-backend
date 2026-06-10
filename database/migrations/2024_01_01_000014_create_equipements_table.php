<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('reference', 100)->nullable();
            $table->enum('type_mise_dispo', ['propriete', 'location']);
            $table->string('fournisseur', 150)->nullable();
            $table->decimal('cout_journalier', 10, 2)->nullable();
            $table->date('date_affectation');
            $table->date('date_retour_prevue')->nullable();
            $table->enum('etat', ['bon_etat', 'en_maintenance', 'defectueux']);
            $table->string('justificatif', 255)->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();

            $table->index('id_chantier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
