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
       Schema::create('despachos', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_pedido')
        ->unique()
        ->constrained('pedidos');

    $table->foreignId('id_bodeguero')
        ->constrained('usuarios');

    $table->timestamp('fecha_preparacion')
        ->nullable();

    $table->timestamp('fecha_despacho')
        ->nullable();

    $table->enum('estado', [
        'pendiente',
        'preparando',
        'despachado'
    ]);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
