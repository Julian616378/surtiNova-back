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
                $q2->where('razon_social', 'like', "%{$request->buscar}%")
                   ->orWhere('nombre_propietario', 'like', "%{$request->buscar}%");
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
            'nombre_establecimiento' => 'required|string|max:255',
            'nombre_propietario'     => 'required|string|max:255',
            'telefono'               => 'required|string|max:20',
            'direccion'              => 'required|string',
            'barrio'                 => 'nullable|string|max:100',
            'ciudad'                 => 'required|string|max:100',
            'observaciones'          => 'nullable|string',
        ]);

        $tienda = Tienda::create(array_merge(
            $request->only('nombre_establecimiento', 'nombre_propietario', 'telefono', 'direccion', 'barrio', 'ciudad', 'observaciones'),
            ['estado' => 'prospecto', 'id_asesor' => $request->user()->id]
        ));

        return response()->json($tienda, 201);
    }

    /**
     * Registrar tienda formal (paso 2 - cuando el prospecto acepta)
     */
    public function store(Request $request)
{
    $request->validate([
        'nombre'       => 'required|string|max:255',
        'nit'          => 'nullable|string|unique:tiendas,nit',
        'propietario'  => 'required|string|max:255',
        'telefono'     => 'required|string|max:20',
        'correo'       => 'nullable|email|unique:tiendas,correo',
        'direccion'    => 'required|string',
        'latitud'      => 'nullable|numeric',
        'longitud'     => 'nullable|numeric',
        'id_asesor'    => 'required|exists:usuarios,id',
    ]);

    $tienda = Tienda::create(array_merge(
        $request->only(
            'nombre',
            'nit',
            'propietario',
            'telefono',
            'correo',
            'direccion',
            'latitud',
            'longitud',
            'id_asesor'
        ),
        ['estado' => 'registrada']
    ));

    return response()->json(
        $tienda->load('asesor'),
        201
    );
}
    public function show(Tienda $tienda)
    {
        return response()->json($tienda->load(['asesor', 'pedidos', 'muestras']));
    }

    public function update(Request $request, Tienda $tienda)
    {
        $request->validate([
            'razon_social' => 'sometimes|string|max:255',
            'propietario'  => 'sometimes|string|max:255',
            'telefono'     => 'sometimes|string|max:20',
            'correo'        => "sometimes|email|unique:tiendas,correo,{$tienda->id}",
            'direccion'    => 'sometimes|string',
            'latitud'      => 'nullable|numeric',
            'longitud'     => 'nullable|numeric',
        ]);

        $tienda->update($request->only('razon_social', 'propietario', 'telefono', 'correo', 'direccion', 'latitud', 'longitud'));

        return response()->json($tienda);
    }

    /**
     * Cambiar estado de una tienda (Admin)
     * Estados: registrada, en_prueba, activa, inactiva, suspendida
     */
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

    /**
     * Tiendas del asesor autenticado
     */
    public function misCartera(Request $request)
    {
        $tiendas = Tienda::where('id_asesor', $request->user()->id)
            ->with(['pedidos' => fn($q) => $q->latest()->limit(1)])
            ->get();
        return response()->json($tiendas);
    }
}
