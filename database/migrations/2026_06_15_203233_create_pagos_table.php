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
        Schema::create('pagos', function (Blueprint $table) {

    $table->id();

    $table->foreignId('id_pedido')
        ->constrained('pedidos');

    $table->decimal('monto',12,2);

    $table->enum('metodo_pago',[
        'efectivo',
        'nequi',
        'daviplata',
        'transferencia',
        'tarjeta'
    ]);

    $table->enum('estado',[
        'pendiente',
        'pagado',
        'rechazado',
        'reembolsado'
    ]);

    $table->timestamp('fecha_pago')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
