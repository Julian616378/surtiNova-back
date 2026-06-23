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

            /*
            |--------------------------------------------------------------------------
            | INFORMACIÓN GENERAL
            |--------------------------------------------------------------------------
            */

            $table->string('nombre');

            $table->string('codigo')->unique()->nullable();

            $table->string('codigo_barras')->nullable();

            $table->text('descripcion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | RELACIONES
            |--------------------------------------------------------------------------
            */

            $table->foreignId('id_categoria')
                ->constrained('categorias')
                ->cascadeOnUpdate();

            $table->foreignId('id_marca')
                ->nullable()
                ->constrained('marcas')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
            |--------------------------------------------------------------------------
            | PRECIOS
            |--------------------------------------------------------------------------
            */

            $table->decimal('precio_compra', 12, 2)->default(0);

            $table->decimal('precio', 12, 2);

            /*
            |--------------------------------------------------------------------------
            | PRESENTACIÓN
            |--------------------------------------------------------------------------
            */

            $table->enum('presentacion', [

                'Unidad',

                'Paquete',

                'Caja',

                'Bulto',

                'Fardo',

                'Combo'

            ])->default('Unidad');

            /*
            |--------------------------------------------------------------------------
            | CONTENIDO
            |--------------------------------------------------------------------------
            */

            // Ejemplo:
            // Paquete de arepas x5
            // Caja x24 bebidas
            // Bulto x50 arroz

            $table->integer('contenido')->default(1);

            /*
            |--------------------------------------------------------------------------
            | UNIDAD DE MEDIDA
            |--------------------------------------------------------------------------
            */

            $table->enum('unidad_medida', [

                'Unidad',

                'Kg',

                'g',

                'L',

                'ml'

            ])->default('Unidad');

            /*
            |--------------------------------------------------------------------------
            | COMPRA
            |--------------------------------------------------------------------------
            */

            // Compra mínima

            $table->integer('compra_minima')->default(1);

            // Compra por múltiplos

            $table->integer('multiplo_compra')->default(1);

            /*
            |--------------------------------------------------------------------------
            | INVENTARIO
            |--------------------------------------------------------------------------
            */

            $table->integer('stock')->default(0);

            $table->integer('stock_minimo')->default(5);

            /*
            |--------------------------------------------------------------------------
            | IMAGEN
            |--------------------------------------------------------------------------
            */

            $table->string('imagen')->nullable();

            /*
            |--------------------------------------------------------------------------
            | FECHA DE VENCIMIENTO
            |--------------------------------------------------------------------------
            */

            $table->date('fecha_vencimiento')->nullable();

            /*
            |--------------------------------------------------------------------------
            | INDICADORES
            |--------------------------------------------------------------------------
            */

            $table->boolean('destacado')->default(false);

            $table->boolean('oferta')->default(false);

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            */

            $table->boolean('estado')->default(true);

            /*
            |--------------------------------------------------------------------------
            | OBSERVACIONES
            |--------------------------------------------------------------------------
            */

            $table->text('observaciones')->nullable();

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