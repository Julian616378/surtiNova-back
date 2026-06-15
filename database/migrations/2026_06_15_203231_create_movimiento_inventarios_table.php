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
        Schema::create('movimiento_inventarios', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_producto')
        ->constrained('productos');

    $table->enum('tipo', [
        'entrada',
        'salida',
        'ajuste',
        'vencimiento'
    ]);

    $table->integer('cantidad');

    $table->foreignId('responsable')
        ->nullable()
        ->constrained('usuarios');

    $table->text('observacion')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
    }
};
