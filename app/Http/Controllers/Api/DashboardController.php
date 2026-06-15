<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Tienda;
use App\Models\Producto;
use App\Models\Ruta;
use App\Models\Comision;
use App\Models\DetallePedido;
use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Indicadores principales para el dashboard Admin en Flutter
     */
    public function resumen()
    {
        $hoy = now()->toDateString();

        $ventasHoy = Pedido::whereDate('fecha_pedido', $hoy)
            ->whereNotIn('estado', ['cancelado'])
            ->sum('total');

        $ventasMes = Pedido::whereMonth('fecha_pedido', now()->month)
            ->whereYear('fecha_pedido', now()->year)
            ->whereNotIn('estado', ['cancelado'])
            ->sum('total');

        $pedidosHoy = Pedido::whereDate('fecha_pedido', $hoy)->count();

        $tiendasActivas = Tienda::where('estado', 'activa')->count();
        $tiendasEnPrueba = Tienda::where('estado', 'en_prueba')->count();

        $repartidoresActivos = Ruta::where('estado', 'en_curso')
            ->distinct('id_repartidor')
            ->count('id_repartidor');

        $rutasEnCurso = Ruta::where('estado', 'en_curso')->count();

        $comisionesGeneradas = Comision::whereMonth('fecha', now()->month)->sum('valor');

        // Tiempo promedio de entrega (en minutos)
        $tiempoPromedio = DB::table('entregas')
            ->join('pedidos', 'entregas.id_pedido', '=', 'pedidos.id')
            ->whereNotNull('entregas.fecha_entrega')
            ->whereMonth('pedidos.fecha_pedido', now()->month)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, pedidos.fecha_pedido, entregas.fecha_entrega)) as promedio')
            ->value('promedio');

        // Inventario crítico
        $inventarioCritico = DB::table('inventarios')
            ->whereColumn('cantidad_actual', '<=', 'cantidad_minima')
            ->count();

        return response()->json([
            'ventas_hoy'           => $ventasHoy,
            'ventas_mes'           => $ventasMes,
            'pedidos_hoy'          => $pedidosHoy,
            'tiendas_activas'      => $tiendasActivas,
            'tiendas_en_prueba'    => $tiendasEnPrueba,
            'repartidores_activos' => $repartidoresActivos,
            'rutas_en_curso'       => $rutasEnCurso,
            'comisiones_mes'       => $comisionesGeneradas,
            'tiempo_promedio_min'  => round($tiempoPromedio ?? 0),
            'inventario_critico'   => $inventarioCritico,
        ]);
    }

    /**
     * Top productos más vendidos
     */
    public function productosTop(Request $request)
    {
        $limite = $request->limit ?? 10;

        $productos = DetallePedido::select('id_producto', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(subtotal) as total_ingresos'))
            ->with('producto:id,nombre,precio')
            ->join('pedidos', 'detalle_pedidos.id_pedido', '=', 'pedidos.id')
            ->whereNotIn('pedidos.estado', ['cancelado'])
            ->when($request->mes, fn($q) => $q->whereMonth('pedidos.fecha_pedido', $request->mes))
            ->groupBy('id_producto')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();

        return response()->json($productos);
    }

    /**
     * Reporte de ventas por período
     */
    public function reporteVentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $ventas = Pedido::with(['tienda', 'detalles.producto', 'pagos'])
            ->whereNotIn('estado', ['cancelado'])
            ->whereBetween('fecha_pedido', [$request->fecha_inicio, $request->fecha_fin . ' 23:59:59'])
            ->orderBy('fecha_pedido')
            ->get();

        $resumen = [
            'total_pedidos'  => $ventas->count(),
            'total_ingresos' => $ventas->sum('total'),
            'promedio_pedido'=> $ventas->avg('total'),
        ];

        return response()->json(['resumen' => $resumen, 'pedidos' => $ventas]);
    }

    /**
     * Reporte de inventario actual
     */
    public function reporteInventario()
    {
        $inventario = DB::table('inventarios')
            ->join('productos', 'inventarios.id_producto', '=', 'productos.id')
            ->join('categorias', 'productos.id_categoria', '=', 'categorias.id')
            ->select(
                'productos.nombre',
                'categorias.nombre as categoria',
                'inventarios.cantidad_actual',
                'inventarios.cantidad_minima',
                'productos.precio',
                DB::raw('(inventarios.cantidad_actual * productos.precio) as valor_stock'),
                DB::raw('CASE WHEN inventarios.cantidad_actual <= inventarios.cantidad_minima THEN 1 ELSE 0 END as critico')
            )
            ->where('productos.estado', true)
            ->orderBy('categorias.nombre')
            ->orderBy('productos.nombre')
            ->get();

        return response()->json($inventario);
    }

    /**
     * Reporte de rutas
     */
    public function reporteRutas(Request $request)
    {
        $rutas = Ruta::with(['repartidor', 'vehiculo', 'pedidos'])
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('fecha')
            ->get()
            ->map(fn($ruta) => array_merge($ruta->toArray(), [
                'total_pedidos' => $ruta->pedidos->count(),
            ]));

        return response()->json($rutas);
    }

    /**
     * Reporte de clientes activos
     */
    public function reporteClientes()
    {
        $tiendas = Tienda::with(['asesor', 'pedidos' => fn($q) => $q->whereNotIn('estado', ['cancelado'])])
            ->where('estado', 'activa')
            ->get()
            ->map(fn($t) => [
                'id'              => $t->id,
                'razon_social'    => $t->razon_social,
                'asesor'          => $t->asesor?->nombre,
                'total_pedidos'   => $t->pedidos->count(),
                'total_compras'   => $t->pedidos->sum('total'),
                'ultimo_pedido'   => $t->pedidos->max('fecha_pedido'),
            ]);

        return response()->json($tiendas);
    }

    /**
     * Reporte de comisiones
     */
    public function reporteComisiones(Request $request)
    {
        $comisiones = Comision::with(['asesor', 'tienda'])
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('fecha')
            ->get();

        $resumen = [
            'total_pendiente' => $comisiones->where('estado', 'pendiente')->sum('valor'),
            'total_pagado'    => $comisiones->where('estado', 'pagada')->sum('valor'),
        ];

        return response()->json(['resumen' => $resumen, 'comisiones' => $comisiones]);
    }
}
