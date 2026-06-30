<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    /**
     * Listado con filtros por estado, asesor, etc.
     */
    public function index(Request $request)
    {
        $tiendas = Tienda::with(['asesor'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->asesor, fn($q) => $q->where('id_asesor', $request->asesor))
            ->when($request->buscar, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('nombre', 'like', "%{$request->buscar}%")
                   ->orWhere('propietario', 'like', "%{$request->buscar}%");
            }))
            ->paginate(20);

        return response()->json($tiendas);
    }

    /**
     * Registrar prospecto (paso 1)
     */
    public function registrarProspecto(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'propietario' => 'required|string|max:255',
            'telefono'    => 'required|string|max:20',
            'direccion'   => 'required|string',
            'correo'      => 'nullable|email|max:255',
        ]);

        $tienda = Tienda::create(array_merge(
            $request->only('nombre', 'propietario', 'telefono', 'direccion', 'correo'),
            [
                'estado'    => 'prospecto',
                'id_asesor' => $request->user()->id,
            ]
        ));

        return response()->json($tienda, 201);
    }

    public function store(Request $request)
{
    $request->validate([
        'nombre'      => 'required|string|max:255',
        'nit'         => 'nullable|string|unique:tiendas,nit',
        'propietario' => 'required|string|max:255',
        'telefono'    => 'required|string|max:20',
        'correo'      => 'nullable|email|unique:tiendas,correo',
        'direccion'   => 'required|string',
        'latitud'     => 'nullable|numeric',
        'longitud'    => 'nullable|numeric',
    ]);

    $tienda = Tienda::create([
        ...$request->only(
            'nombre',
            'nit',
            'propietario',
            'telefono',
            'correo',
            'direccion',
            'latitud',
            'longitud'
        ),
        'estado' => 'registrada',
        'id_asesor' => $request->user()->id,
    ]);

    return response()->json($tienda->load('asesor'), 201);
}

    public function cambiarEstado(Request $request, Tienda $tienda)
    {
        $request->validate([
            'estado' => 'required|in:registrada,en_prueba,activa,inactiva,suspendida',
            'motivo' => 'nullable|string',
        ]);

        $tienda->update(['estado' => $request->estado]);

        return response()->json(['message' => "Tienda actualizada a: {$request->estado}", 'tienda' => $tienda]);
    }

    /**
     * Aprobar tienda (Admin) - la pasa a activa
     */
    public function aprobar(Tienda $tienda)
    {
        $tienda->update(['estado' => 'activa']);
        return response()->json(['message' => 'Tienda aprobada y activada.', 'tienda' => $tienda]);
    }

    /**
     * Historial de pedidos de la tienda
     */
    public function pedidos(Tienda $tienda)
    {
        $pedidos = $tienda->pedidos()->with(['detalles.producto', 'pagos'])->orderByDesc('fecha_pedido')->paginate(15);
        return response()->json($pedidos);
    }

  public function misCartera(Request $request)
{
    $tiendas = Tienda::where('id_asesor', $request->user()->id)
        ->whereHas('visitas')  // ✅ solo las que tienen al menos 1 visita
        ->orderByDesc('created_at')
        ->get();

    return response()->json($tiendas);
}
    public function show(Tienda $tienda)
    {
        return response()->json($tienda->load(['asesor', 'pedidos', 'muestras']));
    }

    public function update(Request $request, Tienda $tienda)
{
    $request->validate([
        'nombre'      => 'sometimes|string|max:255',
        'propietario' => 'sometimes|string|max:255',
        'telefono'    => 'sometimes|string|max:20',
        'correo'      => "sometimes|email|unique:tiendas,correo,{$tienda->id}",
        'direccion'   => 'sometimes|string',
        'latitud'     => 'nullable|numeric',
        'longitud'    => 'nullable|numeric',
        'estado'      => 'sometimes|in:prospecto,registrada,en_prueba,activa,inactiva,suspendida', // ✅
    ]);

    $tienda->update($request->only(
        'nombre', 'propietario', 'telefono', 'correo',
        'direccion', 'latitud', 'longitud', 'estado' // ✅
    ));

    return response()->json($tienda);
}
}
