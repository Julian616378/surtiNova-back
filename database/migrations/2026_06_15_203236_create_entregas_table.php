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
        Schema::create('entregas', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_pedido')
        ->unique()
        ->constrained('pedidos');

    $table->timestamp('fecha_entrega')
        ->nullable();

    $table->string('foto_evidencia')
        ->nullable();

    $table->text('firma_cliente')
        ->nullable();

    $table->text('observaciones')
        ->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
