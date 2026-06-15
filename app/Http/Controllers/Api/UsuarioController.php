<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Lista todos los usuarios (Admin)
     */
    public function index(Request $request)
    {
        $usuarios = Usuario::with('rol')
            ->when($request->rol, fn($q) => $q->whereHas('rol', fn($r) => $r->where('nombre', $request->rol)))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->paginate(20);

        return response()->json($usuarios);
    }

    /**
     * Crear usuario (Admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'id_rol'   => 'required|exists:rols,id',
            'telefono' => 'nullable|string|max:20',
        ]);

        $usuario = Usuario::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'id_rol'   => $request->id_rol,
            'telefono' => $request->telefono,
            'estado'   => true,
        ]);

        return response()->json($usuario->load('rol'), 201);
    }

    /**
     * Mostrar un usuario
     */
    public function show(Usuario $usuario)
    {
        return response()->json($usuario->load('rol'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'nombre'   => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:usuarios,email,{$usuario->id}",
            'password' => 'sometimes|string|min:6',
            'id_rol'   => 'sometimes|exists:rols,id',
            'telefono' => 'nullable|string|max:20',
            'estado'   => 'sometimes|boolean',
        ]);

        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return response()->json($usuario->load('rol'));
    }

    /**
     * Eliminar usuario
     */
    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado.']);
    }

    /**
     * Lista de roles disponibles
     */
    public function roles()
    {
        return response()->json(Rol::all());
    }
}
