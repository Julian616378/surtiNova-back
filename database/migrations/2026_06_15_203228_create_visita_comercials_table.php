<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visita_comercials', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_asesor')
        ->constrained('usuarios');

    $table->foreignId('id_tienda')
        ->constrained('tiendas');

    $table->date('fecha');

    $table->text('resultado')->nullable();

    $table->text('observaciones')->nullable();

    $table->date('proxima_visita')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visita_comercials');
    }
};
