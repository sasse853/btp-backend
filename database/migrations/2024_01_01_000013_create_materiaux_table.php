<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiaux', function (Blueprint $table) {
            $table->id();
            $table->string('designation', 150);
            $table->decimal('quantite_commandee', 10, 2);
            $table->string('unite', 20);
            $table->decimal('quantite_recue', 10, 2)->default(0);
            $table->decimal('quantite_utilisee', 10, 2)->default(0);
            $table->decimal('prix_unitaire', 10, 2);
            $table->string('fournisseur', 150)->nullable();
            $table->date('date_livraison')->nullable();
            $table->string('justificatif', 255)->nullable();
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->text('observations')->nullable();

            $table->index('id_chantier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiaux');
    }
};
