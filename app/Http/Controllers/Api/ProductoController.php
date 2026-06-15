<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Catálogo público con filtros para Flutter
     */
    public function index(Request $request)
    {
        $productos = Producto::with(['categoria', 'inventario', 'ofertas' => fn($q) => $q->where('estado', true)])
            ->when($request->categoria, fn($q) => $q->where('id_categoria', $request->categoria))
            ->when($request->estado !== null, fn($q) => $q->where('estado', $request->estado))
            ->when($request->buscar, fn($q) => $q->where('nombre', 'like', "%{$request->buscar}%"))
            ->when($request->critico, fn($q) => $q->whereHas('inventario', fn($i) => $i->whereColumn('cantidad_actual', '<=', 'cantidad_minima')))
            ->paginate(20);

        return response()->json($productos);
    }

    /**
     * Crear producto + registro de inventario inicial
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'precio'            => 'required|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'id_categoria'      => 'required|exists:categorias,id',
            'fecha_vencimiento' => 'nullable|date',
            'estado'            => 'sometimes|boolean',
            'stock_minimo'      => 'nullable|integer|min:0',
        ]);

        $producto = Producto::create($request->only(
            'nombre', 'descripcion', 'precio', 'stock', 'id_categoria', 'fecha_vencimiento', 'estado'
        ));

        // Crear registro de inventario automáticamente
        Inventario::create([
            'id_producto'     => $producto->id,
            'cantidad_actual' => $request->stock,
            'cantidad_minima' => $request->stock_minimo ?? 5,
        ]);

        return response()->json($producto->load(['categoria', 'inventario']), 201);
    }

    public function show(Producto $producto)
    {
        return response()->json($producto->load(['categoria', 'inventario', 'ofertas']));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre'            => 'sometimes|string|max:255',
            'descripcion'       => 'nullable|string',
            'precio'            => 'sometimes|numeric|min:0',
            'stock'             => 'sometimes|integer|min:0',
            'id_categoria'      => 'sometimes|exists:categorias,id',
            'fecha_vencimiento' => 'nullable|date',
            'estado'            => 'sometimes|boolean',
        ]);

        $producto->update($request->only(
            'nombre', 'descripcion', 'precio', 'stock', 'id_categoria', 'fecha_vencimiento', 'estado'
        ));

        return response()->json($producto->load(['categoria', 'inventario']));
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['estado' => false]);
        return response()->json(['message' => 'Producto desactivado.']);
    }

    /**
     * Productos con stock crítico (para dashboard admin/bodeguero)
     */
    public function criticos()
    {
        $productos = Producto::with(['categoria', 'inventario'])
            ->whereHas('inventario', fn($q) => $q->whereColumn('cantidad_actual', '<=', 'cantidad_minima'))
            ->where('estado', true)
            ->get();

        return response()->json($productos);
    }
}
