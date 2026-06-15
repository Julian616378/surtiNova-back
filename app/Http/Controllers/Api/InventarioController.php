<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Ver inventario general
     */
    public function index()
    {
        $inventarios = Inventario::with('producto.categoria')->get();
        return response()->json($inventarios);
    }

    /**
     * Ver movimientos con filtros
     */
    public function movimientos(Request $request)
    {
        $movimientos = MovimientoInventario::with(['producto', 'responsable'])
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->producto, fn($q) => $q->where('id_producto', $request->producto))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($movimientos);
    }

    /**
     * Registrar entrada de mercancía
     */
    public function entrada(Request $request)
    {
        $request->validate([
            'id_producto'  => 'required|exists:productos,id',
            'cantidad'     => 'required|integer|min:1',
            'observacion'  => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $inventario = Inventario::where('id_producto', $request->id_producto)->firstOrFail();
            $inventario->increment('cantidad_actual', $request->cantidad);

            Producto::where('id', $request->id_producto)->increment('stock', $request->cantidad);

            $movimiento = MovimientoInventario::create([
                'id_producto'  => $request->id_producto,
                'tipo'         => 'entrada',
                'cantidad'     => $request->cantidad,
                'responsable'  => $request->user()->id,
                'observacion'  => $request->observacion,
            ]);

            return response()->json($movimiento->load('producto'), 201);
        });
    }

    /**
     * Ajuste manual (Admin/Bodeguero)
     */
    public function ajuste(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer', // puede ser negativo
            'observacion' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $inventario = Inventario::where('id_producto', $request->id_producto)->firstOrFail();
            $inventario->increment('cantidad_actual', $request->cantidad);

            Producto::where('id', $request->id_producto)->increment('stock', $request->cantidad);

            $movimiento = MovimientoInventario::create([
                'id_producto' => $request->id_producto,
                'tipo'        => 'ajuste',
                'cantidad'    => $request->cantidad,
                'responsable' => $request->user()->id,
                'observacion' => $request->observacion,
            ]);

            return response()->json($movimiento->load('producto'), 201);
        });
    }

    /**
     * Registrar productos vencidos
     */
    public function vencimiento(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'observacion' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $inventario = Inventario::where('id_producto', $request->id_producto)->firstOrFail();
            $inventario->decrement('cantidad_actual', $request->cantidad);

            Producto::where('id', $request->id_producto)->decrement('stock', $request->cantidad);

            $movimiento = MovimientoInventario::create([
                'id_producto' => $request->id_producto,
                'tipo'        => 'vencimiento',
                'cantidad'    => -$request->cantidad,
                'responsable' => $request->user()->id,
                'observacion' => $request->observacion,
            ]);

            return response()->json($movimiento->load('producto'), 201);
        });
    }
}
