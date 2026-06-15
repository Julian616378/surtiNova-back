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
        Schema::create('inventarios', function (Blueprint $table) {

    $table->id();

    $table->foreignId('id_producto')
        ->unique()
        ->constrained('productos')
        ->cascadeOnDelete();

    $table->integer('cantidad_actual');
    $table->integer('cantidad_minima');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
