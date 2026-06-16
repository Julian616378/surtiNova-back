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
        Schema::create('comisiones', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_asesor')
        ->constrained('usuarios');

    $table->foreignId('id_tienda')
        ->constrained('tiendas');

    $table->decimal('valor', 12, 2);

    $table->date('fecha');

    $table->enum('estado', [
        'pendiente',
        'pagada'
    ]);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisions');
    }
};
