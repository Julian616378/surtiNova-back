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
       Schema::create('ofertas', function (Blueprint $table) {
    $table->id();

    $table->string('nombre');

    $table->text('descripcion')->nullable();

    $table->enum('tipo', [
        'porcentaje',
        'valor_fijo',
        'combo',
        'dos_por_uno'
    ]);

    $table->decimal('valor', 12, 2);

    $table->date('fecha_inicio');

    $table->date('fecha_fin');

    $table->boolean('estado')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
