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
        Schema::create('seguimiento_pruebas', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_muestra')
        ->constrained('muestra_productos');

    $table->integer('cantidad_vendida')->default(0);

    $table->integer('cantidad_devuelta')->default(0);

    $table->decimal('valor_cobrado', 12, 2)->default(0);

    $table->date('fecha');

    $table->text('observaciones')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_pruebas');
    }
};
