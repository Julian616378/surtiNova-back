<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $rutas = Ruta::with(['repartidor', 'vehiculo', 'pedidos.tienda'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->fecha, fn($q) => $q->whereDate('fecha', $request->fecha))
            ->when($request->repartidor, fn($q) => $q->where('id_repartidor', $request->repartidor))
            ->orderByDesc('fecha')
            ->paginate(20);

        return response()->json($rutas);
    }

    /**
     * Crear ruta y asignar pedidos
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'fecha'          => 'required|date',
            'id_repartidor'  => 'required|exists:usuarios,id',
            'id_vehiculo'    => 'required|exists:vehiculos,id',
            'hora_salida'    => 'nullable|date_format:H:i',
            'pedidos'        => 'required|array|min:1',
            'pedidos.*.id'   => 'required|exists:pedidos,id',
            'pedidos.*.orden'=> 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $ruta = Ruta::create([
                'nombre'        => $request->nombre,
                'fecha'         => $request->fecha,
                'id_repartidor' => $request->id_repartidor,
                'id_vehiculo'   => $request->id_vehiculo,
                'hora_salida'   => $request->hora_salida,
                'estado'        => 'pendiente',
            ]);

            // Asociar pedidos con orden de entrega
            foreach ($request->pedidos as $item) {
                DB::table('pedido_ruta')->insert([
                    'id_ruta'       => $ruta->id,
                    'id_pedido'     => $item['id'],
                    'orden_entrega' => $item['orden'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Actualizar estado del pedido a despachado
                Pedido::where('id', $item['id'])->update(['estado' => 'despachado']);
            }

            return response()->json($ruta->load(['repartidor', 'vehiculo', 'pedidos.tienda']), 201);
        });
    }

    public function show(Ruta $ruta)
    {
        return response()->json($ruta->load([
            'repartidor',
            'vehiculo',
            'pedidos.tienda',
            'pedidos.detalles.producto',
            'pedidos.entrega',
        ]));
    }

    /**
     * Iniciar recorrido (Repartidor)
     */
    public function iniciar(Request $request, Ruta $ruta)
    {
        if ($ruta->estado !== 'pendiente') {
            return response()->json(['message' => 'La ruta no está en estado pendiente.'], 422);
        }

        $ruta->update([
            'estado'      => 'en_curso',
            'hora_salida' => now()->format('H:i:s'),
        ]);

        // Cambiar estado de los pedidos a en_ruta
        $ruta->pedidos()->update(['estado' => 'en_ruta']);

        return response()->json($ruta->load(['repartidor', 'vehiculo', 'pedidos']));
    }

    /**
     * Finalizar ruta (Repartidor)
     */
    public function finalizar(Ruta $ruta)
    {
        $ruta->update([
            'estado'   => 'finalizada',
            'hora_fin' => now()->format('H:i:s'),
        ]);

        return response()->json(['message' => 'Ruta finalizada.', 'ruta' => $ruta]);
    }

    /**
     * Rutas del repartidor autenticado
     */
    public function misRutas(Request $request)
    {
        $rutas = Ruta::with(['vehiculo', 'pedidos.tienda'])
            ->where('id_repartidor', $request->user()->id)
            ->orderByDesc('fecha')
            ->paginate(10);

        return response()->json($rutas);
    }

    /**
     * Actualizar orden de entrega de pedidos en la ruta
     */
    public function reordenar(Request $request, Ruta $ruta)
    {
        $request->validate([
            'pedidos'        => 'required|array',
            'pedidos.*.id'   => 'required|exists:pedidos,id',
            'pedidos.*.orden'=> 'required|integer|min:1',
        ]);

        foreach ($request->pedidos as $item) {
            DB::table('pedido_ruta')
                ->where('id_ruta', $ruta->id)
                ->where('id_pedido', $item['id'])
                ->update(['orden_entrega' => $item['orden']]);
        }

        return response()->json(['message' => 'Orden actualizado.']);
    }
}
