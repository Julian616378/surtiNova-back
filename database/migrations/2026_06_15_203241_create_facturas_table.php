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
        Schema::create('facturas', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_pedido')
        ->unique()
        ->constrained('pedidos');

    $table->string('numero_factura')->unique();

    $table->decimal('subtotal', 12, 2);

    $table->decimal('iva', 12, 2);

    $table->decimal('total', 12, 2);

    $table->date('fecha');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
