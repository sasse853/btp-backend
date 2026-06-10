<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('type', 50);
            $table->boolean('lu')->default(false);
            $table->foreignId('id_destinataire')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('id_chantier')->nullable()->constrained('chantiers')->nullOnDelete();
            $table->dateTime('date_creation')->useCurrent();

            $table->index('id_destinataire');
            $table->index('lu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
