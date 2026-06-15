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
        Schema::create('seguimiento_comercials', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_tienda')
        ->constrained('tiendas');

    $table->foreignId('id_asesor')
        ->constrained('usuarios');

    $table->date('fecha');

    $table->text('observacion');

    $table->string('estado');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_comercials');
    }
};
