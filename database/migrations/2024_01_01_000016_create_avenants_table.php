<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avenants', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant_demande', 15, 2);
            $table->text('motif');
            $table->string('justificatif', 255)->nullable();
            $table->enum('statut', ['en_attente', 'approuve', 'rejete'])->default('en_attente');
            $table->text('commentaire_admin')->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('id_demandeur')->constrained('utilisateurs')->cascadeOnUpdate();
            $table->dateTime('date_demande')->useCurrent();
            $table->dateTime('date_traitement')->nullable();

            $table->index('id_chantier');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avenants');
    }
};
