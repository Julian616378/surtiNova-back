<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_tienda')
                ->constrained('tiendas')
                ->cascadeOnDelete();

            $table->foreignId('id_producto')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->integer('cantidad')->default(1);

            $table->decimal('precio_unitario', 12, 2);

            $table->decimal('subtotal', 12, 2)->storedAs('cantidad * precio_unitario');

            $table->timestamps();

            // Una tienda no puede tener el mismo producto duplicado en el carrito
            $table->unique(['id_tienda', 'id_producto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carritos');
    }
};
