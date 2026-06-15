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
        Schema::create('muestra_productos', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_tienda')
        ->constrained('tiendas');

    $table->foreignId('id_producto')
        ->constrained('productos');

    $table->integer('cantidad');

    $table->date('fecha_entrega');

    $table->date('fecha_revision')->nullable();

    $table->enum('estado', [
        'entregado',
        'vendido',
        'devuelto',
        'perdido'
    ]);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestra_productos');
    }
};
