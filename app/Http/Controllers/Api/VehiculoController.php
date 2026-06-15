<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $vehiculos = Vehiculo::when($request->estado !== null, fn($q) => $q->where('estado', $request->estado))
            ->withCount('rutas')
            ->get();

        return response()->json($vehiculos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa'     => 'required|string|unique:vehiculos,placa',
            'tipo'      => 'required|string|max:100',
            'capacidad' => 'required|integer|min:1',
            'estado'    => 'sometimes|boolean',
        ]);

        $vehiculo = Vehiculo::create($request->only('placa', 'tipo', 'capacidad', 'estado'));

        return response()->json($vehiculo, 201);
    }

    public function show(Vehiculo $vehiculo)
    {
        return response()->json($vehiculo->load('rutas'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'placa'     => "sometimes|string|unique:vehiculos,placa,{$vehiculo->id}",
            'tipo'      => 'sometimes|string|max:100',
            'capacidad' => 'sometimes|integer|min:1',
            'estado'    => 'sometimes|boolean',
        ]);

        $vehiculo->update($request->only('placa', 'tipo', 'capacidad', 'estado'));

        return response()->json($vehiculo);
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->update(['estado' => false]);
        return response()->json(['message' => 'Vehículo desactivado.']);
    }

    /**
     * Vehículos disponibles para asignar a rutas
     */
    public function disponibles()
    {
        $vehiculos = Vehiculo::where('estado', true)
            ->whereDoesntHave('rutas', fn($q) => $q->where('estado', 'en_curso'))
            ->get();

        return response()->json($vehiculos);
    }
}
