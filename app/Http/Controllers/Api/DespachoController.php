<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\Pedido;
use Illuminate\Http\Request;

class DespachoController extends Controller
{
    /**
     * Despachos pendientes para el bodeguero
     */
    public function index(Request $request)
    {
        $despachos = Despacho::with(['pedido.tienda', 'pedido.detalles.producto', 'bodeguero'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($despachos);
    }

    /**
     * Bodeguero inicia preparación de pedido
     */
    public function iniciarPreparacion(Request $request, Pedido $pedido)
    {
        if ($pedido->estado !== 'pendiente') {
            return response()->json(['message' => 'El pedido no está en estado pendiente.'], 422);
        }

        $despacho = Despacho::create([
            'id_pedido'          => $pedido->id,
            'id_bodeguero'       => $request->user()->id,
            'fecha_preparacion'  => now(),
            'estado'             => 'preparando',
        ]);

        $pedido->update(['estado' => 'preparando']);

        return response()->json($despacho->load('pedido'), 201);
    }

    /**
     * Confirmar despacho (pedido listo para salir)
     */
    public function confirmarDespacho(Request $request, Despacho $despacho)
    {
        $request->validate([
            'faltantes' => 'nullable|array',
            'faltantes.*.id_producto' => 'exists:productos,id',
            'faltantes.*.cantidad'    => 'integer|min:1',
        ]);

        $despacho->update([
            'fecha_despacho' => now(),
            'estado'         => 'despachado',
        ]);

        $despacho->pedido->update(['estado' => 'listo']);

        return response()->json($despacho->load(['pedido.tienda', 'bodeguero']));
    }

    public function show(Despacho $despacho)
    {
        return response()->json($despacho->load(['pedido.detalles.producto', 'pedido.tienda', 'bodeguero']));
    }
}
