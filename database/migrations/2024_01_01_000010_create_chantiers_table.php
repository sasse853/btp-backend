<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chantiers', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('reference', 50)->unique()->nullable();
            $table->string('adresse', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('date_debut_prevue')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->decimal('budget_initial', 15, 2);
            $table->decimal('budget_consolide', 15, 2)->nullable();
            $table->string('maitre_ouvrage', 150)->nullable();
            $table->enum('statut', ['en_attente', 'en_cours', 'en_pause', 'termine', 'archive']);
            $table->text('description')->nullable();
            $table->foreignId('id_chef_chantier')->constrained('utilisateurs')->cascadeOnUpdate();
            $table->dateTime('date_creation')->useCurrent();

            $table->index('id_chef_chantier');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantiers');
    }
};
