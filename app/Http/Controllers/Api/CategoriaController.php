<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return response()->json(Categoria::withCount('productos')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
            'estado'      => 'sometimes|boolean',
        ]);

        $categoria = Categoria::create($request->only('nombre', 'descripcion', 'estado'));

        return response()->json($categoria, 201);
    }

    public function show(Categoria $categoria)
    {
        return response()->json($categoria->load('productos'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre'      => "sometimes|string|max:255|unique:categorias,nombre,{$categoria->id}",
            'descripcion' => 'nullable|string',
            'estado'      => 'sometimes|boolean',
        ]);

        $categoria->update($request->only('nombre', 'descripcion', 'estado'));

        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->update(['estado' => false]);
        return response()->json(['message' => 'Categoría desactivada.']);
    }
}
