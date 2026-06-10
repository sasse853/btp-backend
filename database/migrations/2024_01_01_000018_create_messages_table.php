<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('contenu');
            $table->string('fichier_joint', 255)->nullable();
            $table->boolean('lu')->default(false);
            $table->foreignId('id_chantier')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('id_expediteur')->constrained('utilisateurs')->cascadeOnUpdate();
            $table->dateTime('date_envoi')->useCurrent();

            $table->index('id_chantier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
