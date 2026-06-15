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
        Schema::create('productos', function (Blueprint $table) {
    $table->id();

    $table->string('nombre');
    $table->text('descripcion')->nullable();

    $table->decimal('precio',12,2);

    $table->integer('stock')->default(0);

    $table->date('fecha_vencimiento')->nullable();

    $table->boolean('estado')->default(true);

    $table->foreignId('id_categoria')
        ->constrained('categorias')
        ->cascadeOnUpdate();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
