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
       Schema::create('pedidos', function (Blueprint $table) {

    $table->id();

    $table->foreignId('id_tienda')
        ->constrained('tiendas');

    $table->dateTime('fecha_pedido');

    $table->dateTime('fecha_estimada')
        ->nullable();

    $table->enum('estado',[
        'pendiente',
        'preparando',
        'listo',
        'despachado',
        'en_ruta',
        'entregado',
        'cancelado'
    ]);

    $table->decimal('subtotal',12,2);
    $table->decimal('descuento',12,2)->default(0);
    $table->decimal('total',12,2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
