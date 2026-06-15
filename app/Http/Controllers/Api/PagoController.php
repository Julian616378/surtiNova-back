<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Pagos con filtros (Admin)
     */
    public function index(Request $request)
    {
        $pagos = Pago::with(['pedido.tienda'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->metodo, fn($q) => $q->where('metodo_pago', $request->metodo))
            ->when($request->pedido, fn($q) => $q->where('id_pedido', $request->pedido))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($pagos);
    }

    /**
     * Registrar pago de un pedido
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pedido'    => 'required|exists:pedidos,id',
            'monto'        => 'required|numeric|min:0',
            'metodo_pago'  => 'required|in:efectivo,nequi,daviplata,transferencia,tarjeta',
            'fecha_pago'   => 'nullable|date',
        ]);

        $pago = Pago::create([
            'id_pedido'   => $request->id_pedido,
            'monto'       => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'estado'      => 'pendiente',
            'fecha_pago'  => $request->fecha_pago,
        ]);

        return response()->json($pago->load('pedido'), 201);
    }

    /**
     * Actualizar estado del pago (Admin)
     */
    public function actualizarEstado(Request $request, Pago $pago)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,pagado,rechazado,reembolsado',
        ]);

        $pago->update([
            'estado'     => $request->estado,
            'fecha_pago' => $request->estado === 'pagado' ? now() : $pago->fecha_pago,
        ]);

        return response()->json($pago);
    }

    public function show(Pago $pago)
    {
        return response()->json($pago->load('pedido.tienda'));
    }

    /**
     * Pagos de un pedido específico
     */
    public function porPedido(Pedido $pedido)
    {
        return response()->json($pedido->pagos()->get());
    }
}
