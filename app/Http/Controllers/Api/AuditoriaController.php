<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Consultar auditorías con filtros (solo Admin)
     */
    public function index(Request $request)
    {
        $auditorias = Auditoria::with('usuario')
            ->when($request->usuario, fn($q) => $q->where('id_usuario', $request->usuario))
            ->when($request->tabla, fn($q) => $q->where('tabla_afectada', $request->tabla))
            ->when($request->accion, fn($q) => $q->where('accion', 'like', "%{$request->accion}%"))
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('created_at', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('created_at', '<=', $request->fecha_fin))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($auditorias);
    }

    public function show(Auditoria $auditoria)
    {
        return response()->json($auditoria->load('usuario'));
    }
}
