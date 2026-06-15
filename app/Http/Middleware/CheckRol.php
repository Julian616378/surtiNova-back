<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar el rol del usuario autenticado.
 *
 * Uso en rutas:
 *   ->middleware('rol:admin')
 *   ->middleware('rol:admin,asesor')
 *
 * El nombre del rol se compara con el campo `nombre` de la tabla `rols`.
 */
class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $rolUsuario = $usuario->rol?->nombre;

        if (! in_array($rolUsuario, $roles)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.',
                'rol_requerido' => $roles,
                'tu_rol' => $rolUsuario,
            ], 403);
        }

        return $next($request);
    }
}
