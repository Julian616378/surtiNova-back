<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    /**
     * Ofertas activas (para catálogo Flutter)
     */
    public function index(Request $request)
    {
        $ofertas = Oferta::with('productos')
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when(!$request->user() || $request->solo_activas, fn($q) => $q->where('estado', true)->where('fecha_fin', '>=', now()))
            ->paginate(20);

        return response()->json($ofertas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'tipo'         => 'required|in:porcentaje,valor_fijo,combo,dos_por_uno',
            'valor'        => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
            'estado'       => 'sometimes|boolean',
            'productos'    => 'required|array|min:1',
            'productos.*'  => 'exists:productos,id',
        ]);

        $oferta = Oferta::create($request->only('nombre', 'descripcion', 'tipo', 'valor', 'fecha_inicio', 'fecha_fin', 'estado'));

        // Asociar productos a la oferta
        $sync = collect($request->productos)->mapWithKeys(fn($id) => [$id => ['created_at' => now(), 'updated_at' => now()]]);
        $oferta->productos()->sync($sync);

        return response()->json($oferta->load('productos'), 201);
    }

    public function show(Oferta $oferta)
    {
        return response()->json($oferta->load('productos'));
    }

    public function update(Request $request, Oferta $oferta)
    {
        $request->validate([
            'nombre'       => 'sometimes|string|max:255',
            'descripcion'  => 'nullable|string',
            'tipo'         => 'sometimes|in:porcentaje,valor_fijo,combo,dos_por_uno',
            'valor'        => 'sometimes|numeric|min:0',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin'    => 'sometimes|date',
            'estado'       => 'sometimes|boolean',
            'productos'    => 'sometimes|array',
            'productos.*'  => 'exists:productos,id',
        ]);

        $oferta->update($request->only('nombre', 'descripcion', 'tipo', 'valor', 'fecha_inicio', 'fecha_fin', 'estado'));

        if ($request->has('productos')) {
            $oferta->productos()->sync($request->productos);
        }

        return response()->json($oferta->load('productos'));
    }

    public function destroy(Oferta $oferta)
    {
        $oferta->update(['estado' => false]);
        return response()->json(['message' => 'Oferta desactivada.']);
    }
}
