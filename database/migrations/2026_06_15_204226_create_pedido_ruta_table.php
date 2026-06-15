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
       Schema::create('pedido_ruta', function (Blueprint $table) {

    $table->id();

    $table->foreignId('id_ruta')
        ->constrained('rutas')
        ->cascadeOnDelete();

    $table->foreignId('id_pedido')
        ->constrained('pedidos')
        ->cascadeOnDelete();

    $table->integer('orden_entrega');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_ruta');
    }
};
