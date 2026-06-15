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
      Schema::create('tiendas', function (Blueprint $table) {
    $table->id();

    $table->string('nombre');
    $table->string('nit')->nullable();
    $table->string('propietario');
    $table->string('telefono');
    $table->string('correo')->nullable();
    $table->string('direccion');

    $table->decimal('latitud',10,7)->nullable();
    $table->decimal('longitud',10,7)->nullable();

    $table->enum('estado',[
        'prospecto',
        'registrada',
        'en_prueba',
        'activa',
        'inactiva',
        'suspendida'
    ]);

    $table->foreignId('id_asesor')
        ->constrained('usuarios')
        ->cascadeOnUpdate();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiendas');
    }
};
