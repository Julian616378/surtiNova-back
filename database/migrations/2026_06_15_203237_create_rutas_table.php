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
       Schema::create('rutas', function (Blueprint $table) {

    $table->id();

    $table->string('nombre');

    $table->date('fecha');

    $table->foreignId('id_repartidor')
        ->constrained('usuarios');

    $table->foreignId('id_vehiculo')
        ->constrained('vehiculos');

    $table->enum('estado',[
        'pendiente',
        'en_curso',
        'finalizada'
    ]);

    $table->time('hora_salida')->nullable();
    $table->time('hora_fin')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
