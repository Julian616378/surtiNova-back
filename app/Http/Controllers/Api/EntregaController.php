<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\Pedido;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class EntregaController extends Controller
{
    /**
     * Registrar entrega con evidencia (Repartidor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pedido'      => 'required|exists:pedidos,id|unique:entregas,id_pedido',
            'foto_evidencia' => 'nullable|string',
            'firma_cliente'  => 'nullable|string',
            'observaciones'  => 'nullable|string',
        ]);

        $pedido = Pedido::findOrFail($request->id_pedido);

        $entrega = Entrega::create([
            'id_pedido'      => $pedido->id,
            'fecha_entrega'  => now(),
            'foto_evidencia' => $request->foto_evidencia,
            'firma_cliente'  => $request->firma_cliente,
            'observaciones'  => $request->observaciones,
        ]);

        // Actualizar estado del pedido
        $pedido->update(['estado' => 'entregado']);

        // Notificar al asesor de la tienda
        if ($pedido->tienda && $pedido->tienda->id_asesor) {
            Notificacion::create([
                'id_usuario' => $pedido->tienda->id_asesor,
                'titulo'     => '¡Pedido entregado!',
                'mensaje'    => "El pedido #{$pedido->id} fue entregado exitosamente.",
            ]);
        }

        return response()->json($entrega->load('pedido.tienda'), 201);
    }

    public function show(Entrega $entrega)
    {
        return response()->json($entrega->load(['pedido.tienda', 'pedido.detalles.producto']));
    }

    /**
     * Historial de entregas del repartidor
     */
    public function misEntregas(Request $request)
    {
        $entregas = Entrega::whereHas('pedido.rutas', fn($q) => $q->where('id_repartidor', $request->user()->id))
            ->with(['pedido.tienda'])
            ->orderByDesc('fecha_entrega')
            ->paginate(15);

        return response()->json($entregas);
    }
}
