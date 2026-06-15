<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UbicacionRepartidor;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /**
     * El repartidor envía su ubicación periódicamente desde Flutter
     */
    public function actualizar(Request $request)
    {
        $request->validate([
            'latitud'  => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $ubicacion = UbicacionRepartidor::create([
            'id_repartidor' => $request->user()->id,
            'latitud'       => $request->latitud,
            'longitud'      => $request->longitud,
            'fecha_hora'    => now(),
        ]);

        return response()->json($ubicacion, 201);
    }

    /**
     * Última ubicación de un repartidor (para tiendas que rastrean su pedido)
     */
    public function ultimaUbicacion(int $repartidorId)
    {
        $ubicacion = UbicacionRepartidor::where('id_repartidor', $repartidorId)
            ->latest('fecha_hora')
            ->first();

        if (! $ubicacion) {
            return response()->json(['message' => 'Sin ubicación disponible.'], 404);
        }

        return response()->json($ubicacion);
    }

    /**
     * Historial de recorrido del repartidor (para una ruta)
     */
    public function historial(Request $request, int $repartidorId)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'nullable|date',
        ]);

        $ubicaciones = UbicacionRepartidor::where('id_repartidor', $repartidorId)
            ->whereBetween('fecha_hora', [$request->desde, $request->hasta ?? now()])
            ->orderBy('fecha_hora')
            ->get(['latitud', 'longitud', 'fecha_hora']);

        return response()->json($ubicaciones);
    }
}
