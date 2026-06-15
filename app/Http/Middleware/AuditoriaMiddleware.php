<?php

namespace App\Http\Middleware;

use App\Models\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registra automáticamente en auditorías las operaciones
 * de escritura (POST, PUT, PATCH, DELETE) de usuarios autenticados.
 *
 * Registrar en bootstrap/app.php o Kernel.php como alias 'auditoria'.
 * Agregar a rutas sensibles: ->middleware('auditoria')
 */
class AuditoriaMiddleware
{
    // Tablas detectadas por segmento de URL
    private const MAPA_TABLAS = [
        'productos'   => 'productos',
        'categorias'  => 'categorias',
        'pedidos'     => 'pedidos',
        'inventario'  => 'movimiento_inventarios',
        'tiendas'     => 'tiendas',
        'usuarios'    => 'usuarios',
        'precios'     => 'productos',
        'rutas'       => 'rutas',
        'ofertas'     => 'ofertas',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo auditar métodos de escritura con usuario autenticado
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->user()) {
            try {
                $segmentos = $request->segments();
                $tabla = 'general';

                foreach ($segmentos as $seg) {
                    if (isset(self::MAPA_TABLAS[$seg])) {
                        $tabla = self::MAPA_TABLAS[$seg];
                        break;
                    }
                }

                $accion = match ($request->method()) {
                    'POST'   => 'CREAR',
                    'PUT', 'PATCH' => 'MODIFICAR',
                    'DELETE' => 'ELIMINAR',
                    default  => $request->method(),
                };

                Auditoria::create([
                    'id_usuario'     => $request->user()->id,
                    'accion'         => "{$accion} en /{$request->path()}",
                    'tabla_afectada' => $tabla,
                    'ip'             => $request->ip(),
                ]);
            } catch (\Throwable $e) {
                // No interrumpir el flujo si la auditoría falla
                \Log::error('Error en auditoría: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
