<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_personnel')->constrained('personnel')->cascadeOnDelete();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->date('date_presence');
            $table->enum('statut', ['present', 'demi_journee', 'absent_justifie', 'absent_non_justifie', 'conge']);
            $table->decimal('montant_du', 10, 2)->default(0);
            $table->enum('statut_paiement', ['en_attente', 'paye'])->default('en_attente');
            $table->date('date_paiement')->nullable();

            // Un seul enregistrement de presence par ouvrier et par jour.
            $table->unique(['id_personnel', 'date_presence'], 'u_presence');
            $table->index('id_chantier');
            $table->index('date_presence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
