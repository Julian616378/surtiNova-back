<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Cupon;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Listado de pedidos con filtros
     */
    public function index(Request $request)
    {
        $pedidos = Pedido::with(['tienda', 'detalles.producto', 'pagos'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->tienda, fn($q) => $q->where('id_tienda', $request->tienda))
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('fecha_pedido', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('fecha_pedido', '<=', $request->fecha_fin))
            ->orderByDesc('fecha_pedido')
            ->paginate(20);

        return response()->json($pedidos);
    }

    /**
     * Crear pedido
     * Recibe: items[], codigo_cupon opcional
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tienda'           => 'required|exists:tiendas,id',
            'items'               => 'required|array|min:1',
            'items.*.id_producto' => 'required|exists:productos,id',
            'items.*.cantidad'    => 'required|integer|min:1',
            'codigo_cupon'        => 'nullable|string|exists:cupones,codigo',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal  = 0;
            $descuento = 0;
            $detalles  = [];

            foreach ($request->items as $item) {
                $producto = Producto::findOrFail($item['id_producto']);

                if ($producto->stock < $item['cantidad']) {
                    return response()->json([
                        'message' => "Stock insuficiente para: {$producto->nombre}",
                    ], 422);
                }

                $lineaSubtotal = $producto->precio * $item['cantidad'];
                $subtotal     += $lineaSubtotal;
                $detalles[]    = [
                    'id_producto'     => $producto->id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal'        => $lineaSubtotal,
                ];
            }

            // Aplicar cupón si existe
            if ($request->codigo_cupon) {
                $cupon = Cupon::where('codigo', $request->codigo_cupon)
                    ->where('estado', true)
                    ->where('fecha_vencimiento', '>=', now())
                    ->first();

                if ($cupon) {
                    $descuento = $cupon->descuento;
                }
            }

            $total = max(0, $subtotal - $descuento);

            // Crear pedido (sin id_cupon, campo no existe en la tabla)
            $pedido = Pedido::create([
                'id_tienda'    => $request->id_tienda,
                'fecha_pedido' => now(),
                'estado'       => 'pendiente',
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'total'        => $total,
            ]);

            // Insertar detalles y descontar stock
            foreach ($detalles as $detalle) {
                $detalle['id_pedido'] = $pedido->id;
                DetallePedido::create($detalle);

                Producto::where('id', $detalle['id_producto'])->decrement('stock', $detalle['cantidad']);
                Inventario::where('id_producto', $detalle['id_producto'])->decrement('cantidad_actual', $detalle['cantidad']);

                MovimientoInventario::create([
                    'id_producto' => $detalle['id_producto'],
                    'tipo'        => 'salida',
                    'cantidad'    => -$detalle['cantidad'],
                    'responsable' => $request->user()->id ?? null,
                    'observacion' => "Pedido #{$pedido->id}",
                ]);
            }

            // Notificar al usuario autenticado
            Notificacion::create([
                'id_usuario' => $request->user()->id,
                'titulo'     => 'Pedido recibido',
                'mensaje'    => "Tu pedido #{$pedido->id} fue recibido y está siendo procesado.",
            ]);

            return response()->json($pedido->load(['detalles.producto', 'tienda']), 201);
        });
    }

    /**
     * Ver detalle de un pedido
     */
    public function show(Pedido $pedido)
    {
        return response()->json($pedido->load([
            'tienda',
            'detalles.producto',
            'pagos',
            'factura',
            'despacho.bodeguero',
            'entrega',
            'rutas',
        ]));
    }

    /**
     * Cambiar estado de un pedido (Admin/Bodeguero)
     */
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,despachado,en_ruta,entregado,cancelado',
        ]);

        $pedido->update(['estado' => $request->estado]);

        $mensajes = [
            'preparando' => "Tu pedido #{$pedido->id} está siendo preparado.",
            'despachado' => "Tu pedido #{$pedido->id} salió de bodega.",
            'en_ruta'    => "Tu pedido #{$pedido->id} está en camino, ¡pronto llegará!",
            'entregado'  => "Tu pedido #{$pedido->id} fue entregado. ¡Gracias por tu compra!",
            'cancelado'  => "Tu pedido #{$pedido->id} fue cancelado.",
        ];

        if (isset($mensajes[$request->estado])) {
            $tienda = $pedido->tienda;
            // Notificar al asesor de la tienda (id_asesor es el campo correcto en tiendas)
            if ($tienda && $tienda->id_asesor) {
                Notificacion::create([
                    'id_usuario' => $tienda->id_asesor,
                    'titulo'     => 'Actualización de pedido',
                    'mensaje'    => $mensajes[$request->estado],
                ]);
            }
        }

        return response()->json($pedido);
    }

    /**
     * Cancelar pedido (Tienda o Admin)
     */
    public function cancelar(Request $request, Pedido $pedido)
    {
        if (! in_array($pedido->estado, ['pendiente', 'preparando'])) {
            return response()->json(['message' => 'No se puede cancelar un pedido en estado: ' . $pedido->estado], 422);
        }

        DB::transaction(function () use ($pedido) {
            foreach ($pedido->detalles as $detalle) {
                Producto::where('id', $detalle->id_producto)->increment('stock', $detalle->cantidad);
                Inventario::where('id_producto', $detalle->id_producto)->increment('cantidad_actual', $detalle->cantidad);
            }

            $pedido->update(['estado' => 'cancelado']);
        });

        return response()->json(['message' => 'Pedido cancelado.', 'pedido' => $pedido]);
    }

    /**
     * Mis pedidos (para la tienda autenticada)
     */
    public function misPedidos(Request $request)
    {
        $pedidos = Pedido::with(['detalles.producto', 'pagos'])
            ->where('id_tienda', $request->tienda_id)
            ->orderByDesc('fecha_pedido')
            ->paginate(15);

        return response()->json($pedidos);
    }

    /**
     * Pedidos pendientes para bodega
     */
    public function pendientesBodega()
    {
        $pedidos = Pedido::with(['tienda', 'detalles.producto'])
            ->whereIn('estado', ['pendiente', 'preparando'])
            ->orderBy('fecha_pedido')
            ->get();

        return response()->json($pedidos);
    }
}
