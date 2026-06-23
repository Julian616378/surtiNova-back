<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    /**
     * Ver el carrito de una tienda.
     * GET /api/carrito/{id_tienda}
     */
    public function index(int $id_tienda)
    {
        $items = Carrito::with('producto:id,nombre,precio,imagen,stock,estado')
            ->where('id_tienda', $id_tienda)
            ->get();

        $total = $items->sum(fn($item) => $item->cantidad * $item->precio_unitario);

        return response()->json([
            'id_tienda' => $id_tienda,
            'items'     => $items,
            'total'     => round($total, 2),
            'cantidad_items' => $items->sum('cantidad'),
        ]);
    }

    /**
     * Agregar o actualizar un producto en el carrito.
     * Si el producto ya existe, suma la cantidad.
     * POST /api/carrito
     * Body: { id_tienda, id_producto, cantidad }
     */
    public function agregar(Request $request)
    {
        $request->validate([
            'id_tienda'   => 'required|exists:tiendas,id',
            'id_producto' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->id_producto);

        if (! $producto->estado) {
            return response()->json(['message' => 'El producto no está disponible.'], 422);
        }

        if ($producto->stock < $request->cantidad) {
            return response()->json([
                'message' => "Stock insuficiente. Disponible: {$producto->stock}",
            ], 422);
        }

        // Buscar si ya existe en el carrito
        $item = Carrito::where('id_tienda', $request->id_tienda)
            ->where('id_producto', $request->id_producto)
            ->first();

        if ($item) {
            $nuevaCantidad = $item->cantidad + $request->cantidad;

            if ($producto->stock < $nuevaCantidad) {
                return response()->json([
                    'message' => "Stock insuficiente para esa cantidad. Disponible: {$producto->stock}",
                ], 422);
            }

            $item->update([
                'cantidad'        => $nuevaCantidad,
                'precio_unitario' => $producto->precio, // actualiza precio al vigente
            ]);
        } else {
            $item = Carrito::create([
                'id_tienda'       => $request->id_tienda,
                'id_producto'     => $request->id_producto,
                'cantidad'        => $request->cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        }

        return response()->json($item->load('producto:id,nombre,precio,imagen'), 201);
    }

    /**
     * Actualizar cantidad de un item del carrito.
     * PUT /api/carrito/{id}
     * Body: { cantidad }
     */
    public function actualizar(Request $request, int $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $item = Carrito::findOrFail($id);
        $producto = $item->producto;

        if ($producto->stock < $request->cantidad) {
            return response()->json([
                'message' => "Stock insuficiente. Disponible: {$producto->stock}",
            ], 422);
        }

        $item->update([
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $producto->precio,
        ]);

        return response()->json($item->load('producto:id,nombre,precio,imagen'));
    }

    /**
     * Quitar un item del carrito.
     * DELETE /api/carrito/{id}
     */
    public function quitar(int $id)
    {
        $item = Carrito::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Producto eliminado del carrito.']);
    }

    /**
     * Vaciar todo el carrito de una tienda.
     * DELETE /api/carrito/vaciar/{id_tienda}
     */
    public function vaciar(int $id_tienda)
    {
        Carrito::where('id_tienda', $id_tienda)->delete();

        return response()->json(['message' => 'Carrito vaciado.']);
    }

    /**
     * Confirmar el carrito → crea un Pedido con todos los items.
     * POST /api/carrito/confirmar
     * Body: { id_tienda, codigo_cupon? }
     */
    public function confirmar(Request $request)
    {
        $request->validate([
            'id_tienda'    => 'required|exists:tiendas,id',
            'codigo_cupon' => 'nullable|string|exists:cupones,codigo',
        ]);

        $items = Carrito::with('producto')
            ->where('id_tienda', $request->id_tienda)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 422);
        }

        return DB::transaction(function () use ($request, $items) {
            $subtotal  = 0;
            $descuento = 0;
            $detalles  = [];

            // Validar stock y calcular totales
            foreach ($items as $item) {
                $producto = $item->producto;

                if (! $producto->estado) {
                    return response()->json([
                        'message' => "El producto '{$producto->nombre}' ya no está disponible.",
                    ], 422);
                }

                if ($producto->stock < $item->cantidad) {
                    return response()->json([
                        'message' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock}",
                    ], 422);
                }

                $lineaSubtotal = $producto->precio * $item->cantidad;
                $subtotal     += $lineaSubtotal;

                $detalles[] = [
                    'id_producto'     => $producto->id,
                    'cantidad'        => $item->cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal'        => $lineaSubtotal,
                ];
            }

            // Aplicar cupón si existe
            if ($request->codigo_cupon) {
                $cupon = \App\Models\Cupon::where('codigo', $request->codigo_cupon)
                    ->where('estado', true)
                    ->where('fecha_vencimiento', '>=', now())
                    ->first();

                if ($cupon) {
                    $descuento = $cupon->descuento;
                }
            }

            $total = max(0, $subtotal - $descuento);

            // Crear pedido
            $pedido = Pedido::create([
                'id_tienda'    => $request->id_tienda,
                'fecha_pedido' => now(),
                'estado'       => 'pendiente',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'total'        => $total,
            ]);

            // Crear detalles, descontar stock, registrar movimientos
            foreach ($detalles as $detalle) {
                DetallePedido::create(array_merge($detalle, ['id_pedido' => $pedido->id]));

                Producto::where('id', $detalle['id_producto'])
                    ->decrement('stock', $detalle['cantidad']);

                Inventario::where('id_producto', $detalle['id_producto'])
                    ->decrement('cantidad_actual', $detalle['cantidad']);

                MovimientoInventario::create([
                    'id_producto' => $detalle['id_producto'],
                    'tipo'        => 'salida',
                    'cantidad'    => -$detalle['cantidad'],
                    'responsable' => $request->user()?->id,
                    'observacion' => "Pedido #{$pedido->id} (desde carrito)",
                ]);
            }

            // Vaciar carrito después de confirmar
            Carrito::where('id_tienda', $request->id_tienda)->delete();

            // Notificación
            if ($request->user()) {
                \App\Models\Notificacion::create([
                    'id_usuario' => $request->user()->id,
                    'titulo'     => 'Pedido recibido',
                    'mensaje'    => "Tu pedido #{$pedido->id} fue recibido y está siendo procesado.",
                ]);
            }

            return response()->json($pedido->load(['detalles.producto', 'tienda']), 201);
        });
    }
}
