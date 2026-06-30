<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Por qué: hasta ahora una VisitaComercial solo podía apuntar a una
     * tienda que YA existía en la tabla `tiendas`. Pero en el flujo nuevo,
     * el asesor visita primero (toca puertas en la calle) y SOLO si el
     * dueño acepta se crea la tienda. Entonces id_tienda debe ser nullable,
     * y mientras no exista tienda guardamos los datos crudos del prospecto
     * directo en la visita.
     */
    public function up(): void
    {
        Schema::table('visita_comercials', function (Blueprint $table) {
            // id_tienda pasa a ser opcional: solo se llena si la visita
            // terminó en "registrada" (tienda ya existe) o si la visita
            // era de seguimiento a una tienda que ya estaba en cartera.
            $table->foreignId('id_tienda')->nullable()->change();

            // Resultado tipificado de la visita (lo que el asesor reporta
            // al volver a la calle, después de hablar con el cliente).
            $table->enum('resultado_visita', [
                'registrada',          // aceptó, se crea la tienda
                'no_acepto',           // habló con el dueño, no aceptó
                'no_estaba',           // no encontró a nadie / cerrado
                'muestra_entregada',   // dejó producto de prueba a recoger
            ])->nullable()->after('id_tienda');

            // Datos crudos del prospecto cuando todavía no hay tienda
            // creada (no_acepto / no_estaba / muestra_entregada antes de
            // ser cliente formal).
            $table->string('nombre_prospecto')->nullable()->after('resultado_visita');
            $table->string('telefono_prospecto')->nullable()->after('nombre_prospecto');
            $table->string('direccion_prospecto')->nullable()->after('telefono_prospecto');
            $table->decimal('latitud_prospecto', 10, 7)->nullable()->after('direccion_prospecto');
            $table->decimal('longitud_prospecto', 10, 7)->nullable()->after('latitud_prospecto');

            // Si el resultado fue "muestra_entregada", queda enlazada la
            // muestra creada para poder hacer seguimiento/recogida después.
            $table->foreignId('id_muestra')->nullable()
                ->after('longitud_prospecto')
                ->constrained('muestra_productos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visita_comercials', function (Blueprint $table) {
            $table->dropForeign(['id_muestra']);
            $table->dropColumn([
                'resultado_visita',
                'nombre_prospecto',
                'telefono_prospecto',
                'direccion_prospecto',
                'latitud_prospecto',
                'longitud_prospecto',
                'id_muestra',
            ]);
            $table->foreignId('id_tienda')->nullable(false)->change();
        });
    }
};
