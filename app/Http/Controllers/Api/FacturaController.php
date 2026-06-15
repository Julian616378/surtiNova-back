<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $facturas = Factura::with(['pedido.tienda'])
            ->when($request->tienda, fn($q) => $q->whereHas('pedido', fn($p) => $p->where('id_tienda', $request->tienda)))
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->orderByDesc('fecha')
            ->paginate(20);

        return response()->json($facturas);
    }

    /**
     * Generar factura para un pedido entregado
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pedido' => 'required|exists:pedidos,id|unique:facturas,id_pedido',
            'iva'       => 'required|numeric|min:0',
        ]);

        $pedido = Pedido::findOrFail($request->id_pedido);

        $factura = Factura::create([
            'id_pedido'      => $pedido->id,
            'numero_factura' => 'FAC-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT),
            'subtotal'       => $pedido->subtotal,
            'iva'            => $request->iva,
            'total'          => $pedido->total + $request->iva,
            'fecha'          => now()->toDateString(),
        ]);

        return response()->json($factura->load('pedido.tienda'), 201);
    }

    public function show(Factura $factura)
    {
        return response()->json($factura->load(['pedido.tienda', 'pedido.detalles.producto']));
    }
}
