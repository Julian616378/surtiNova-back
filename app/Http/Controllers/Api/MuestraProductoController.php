<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MuestraProducto;
use App\Models\SeguimientoPrueba;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MuestraProductoController extends Controller
{
    public function index(Request $request)
    {
        $muestras = MuestraProducto::with(['tienda', 'producto'])
            ->when($request->tienda, fn($q) => $q->where('id_tienda', $request->tienda))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->paginate(20);

        return response()->json($muestras);
    }

    /**
     * Entregar productos de muestra a una tienda
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tienda'      => 'required|exists:tiendas,id',
            'id_producto'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'fecha_entrega'  => 'required|date',
            'fecha_revision' => 'nullable|date|after:fecha_entrega',
        ]);

        // Cambiar estado de tienda a "en_prueba" si aplica
        $tienda = Tienda::find($request->id_tienda);
        if (in_array($tienda->estado, ['registrada', 'prospecto'])) {
            $tienda->update(['estado' => 'en_prueba']);
        }

        $muestra = MuestraProducto::create(array_merge(
            $request->only('id_tienda', 'id_producto', 'cantidad', 'fecha_entrega', 'fecha_revision'),
            ['estado' => 'entregado']
        ));

        return response()->json($muestra->load(['tienda', 'producto']), 201);
    }

    /**
     * Registrar seguimiento de la prueba (visita de revisión)
     */
    public function seguimiento(Request $request, MuestraProducto $muestra)
    {
        $request->validate([
            'cantidad_vendida'  => 'required|integer|min:0',
            'cantidad_devuelta' => 'required|integer|min:0',
            'valor_cobrado'     => 'required|numeric|min:0',
            'fecha'             => 'required|date',
            'observaciones'     => 'nullable|string',
        ]);

        $seguimiento = SeguimientoPrueba::create([
            'id_muestra'        => $muestra->id,
            'cantidad_vendida'  => $request->cantidad_vendida,
            'cantidad_devuelta' => $request->cantidad_devuelta,
            'valor_cobrado'     => $request->valor_cobrado,
            'fecha'             => $request->fecha,
            'observaciones'     => $request->observaciones,
        ]);

        // Actualizar estado de la muestra
        $totalGestionado = $request->cantidad_vendida + $request->cantidad_devuelta;
        if ($totalGestionado >= $muestra->cantidad) {
            $estadoFinal = $request->cantidad_devuelta > 0 ? 'devuelto' : 'vendido';
            $muestra->update(['estado' => $estadoFinal]);
        }

        return response()->json($seguimiento->load('muestra'), 201);
    }

    public function show(MuestraProducto $muestra)
    {
        return response()->json($muestra->load(['tienda', 'producto', 'seguimientos']));
    }
}
