<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Datos de prueba para ver la "ruta del día" funcionando: un asesor con
 * varias tiendas repartidas por Barranquilla (coordenadas reales, no
 * inventadas al azar) en distintos estados, para que rutaHoy() tenga
 * algo real que ordenar por cercanía.
 *
 * Uso: agrega RutaDemoSeeder::class a tu DatabaseSeeder y corre
 *   php artisan db:seed --class=RutaDemoSeeder
 * (o php artisan db:seed si ya lo encadenaste en DatabaseSeeder).
 */
class RutaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rolAsesor = Rol::where('nombre', 'asesor')->first();

        if (! $rolAsesor) {
            $this->command->error("No existe el rol 'asesor'. Corre primero tu RoleSeeder.");
            return;
        }

        $asesor = Usuario::firstOrCreate(
            ['correo' => 'asesor.demo@surtinova.test'],
            [
                'nombre'   => 'Asesor',
                'apellido' => 'Demo',
                'telefono' => '3001234567',
                'password' => Hash::make('password'),
                'estado'   => true,
                'id_rol'   => $rolAsesor->id,
            ]
        );

        $hoy = Carbon::today();

        // Coordenadas reales de Barranquilla, repartidas por distintos
        // barrios para que el orden por cercanía se note de verdad al
        // probar la ruta (no son puntos al azar).
        $tiendasDemo = [
            // ── Tiendas de cartera con próxima visita VENCIDA/HOY ──
            [
                'nombre' => 'Surtitienda El Prado', 'nit' => '900111222-1',
                'propietario' => 'Carlos Mendoza', 'telefono' => '3001112233',
                'correo' => 'elprado@demo.test', 'direccion' => 'Cra 54 #70-20, El Prado',
                'latitud' => 10.9939, 'longitud' => -74.7989,
                'estado' => 'activa', 'proxima_visita' => $hoy, // hoy
            ],
            [
                'nombre' => 'Minimarket Boston', 'nit' => '900111222-2',
                'propietario' => 'Ana María Pérez', 'telefono' => '3002223344',
                'correo' => 'boston@demo.test', 'direccion' => 'Calle 74 #45-30, Boston',
                'latitud' => 10.9886, 'longitud' => -74.7944,
                'estado' => 'en_prueba', 'proxima_visita' => $hoy->copy()->subDays(2), // vencida
            ],
            [
                'nombre' => 'Tienda Doña Rosa - Riomar', 'nit' => '900111222-3',
                'propietario' => 'Rosa Linero', 'telefono' => '3003334455',
                'correo' => 'donarosa@demo.test', 'direccion' => 'Cra 51B #79-60, Riomar',
                'latitud' => 11.0089, 'longitud' => -74.8252,
                'estado' => 'registrada', 'proxima_visita' => $hoy->copy()->subDays(1),
            ],
            [
                'nombre' => 'Distribuciones Alto Prado', 'nit' => '900111222-4',
                'propietario' => 'Jorge Salcedo', 'telefono' => '3004445566',
                'correo' => 'altoprado@demo.test', 'direccion' => 'Cra 53 #82-150, Alto Prado',
                'latitud' => 11.0102, 'longitud' => -74.8019,
                'estado' => 'activa', 'proxima_visita' => $hoy,
            ],

            // ── Prospectos sin visitar todavía (entran por estado, no por fecha) ──
            [
                'nombre' => 'Sin nombre aún - Barrio Abajo', 'nit' => null,
                'propietario' => 'Por confirmar', 'telefono' => '3005556677',
                'correo' => null, 'direccion' => 'Calle 44 #38-12, Barrio Abajo',
                'latitud' => 10.9904, 'longitud' => -74.7944,
                'estado' => 'prospecto', 'proxima_visita' => null,
            ],
            [
                'nombre' => 'Prospecto Bellavista', 'nit' => null,
                'propietario' => 'Por confirmar', 'telefono' => '3006667788',
                'correo' => null, 'direccion' => 'Cra 46 #92-08, Bellavista',
                'latitud' => 11.0205, 'longitud' => -74.8035,
                'estado' => 'prospecto', 'proxima_visita' => null,
            ],

            // ── No debe salir en la ruta: ya activa sin próxima visita pendiente ──
            [
                'nombre' => 'Tienda Activa Sin Pendientes', 'nit' => '900111222-7',
                'propietario' => 'Luisa Fernanda Ríos', 'telefono' => '3007778899',
                'correo' => 'sinpendientes@demo.test', 'direccion' => 'Cra 58 #70-40, El Golf',
                'latitud' => 10.9963, 'longitud' => -74.8003,
                'estado' => 'activa', 'proxima_visita' => null,
            ],
        ];

        foreach ($tiendasDemo as $datos) {
            $proximaVisita = $datos['proxima_visita'];
            unset($datos['proxima_visita']);

            $tienda = Tienda::firstOrCreate(
                ['nombre' => $datos['nombre'], 'id_asesor' => $asesor->id],
                [...$datos, 'id_asesor' => $asesor->id]
            );

            // Si la tienda debe aparecer por "próxima visita vencida/hoy",
            // le creamos una visita pasada con esa proxima_visita —
            // rutaHoy() filtra por whereHas('visitas', proxima_visita <= hoy).
            if ($proximaVisita) {
                $tienda->visitas()->firstOrCreate(
                    ['id_asesor' => $asesor->id, 'id_tienda' => $tienda->id],
                    [
                        'fecha' => $proximaVisita->copy()->subDays(15),
                        'resultado_visita' => 'registrada',
                        'observaciones' => 'Visita anterior (seed de prueba)',
                        'proxima_visita' => $proximaVisita,
                    ]
                );
            }
        }

        $this->command->info("Listo: asesor demo (correo: asesor.demo@surtinova.test / password: password) con " . count($tiendasDemo) . ' tiendas en Barranquilla.');
    }
}