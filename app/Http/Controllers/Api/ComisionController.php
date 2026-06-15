<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comision;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    /**
     * Todas las comisiones (Admin)
     */
    public function index(Request $request)
    {
        $comisiones = Comision::with(['asesor', 'tienda'])
            ->when($request->asesor, fn($q) => $q->where('id_asesor', $request->asesor))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_inicio, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->fecha_fin, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->orderByDesc('fecha')
            ->paginate(20);

        return response()->json($comisiones);
    }

    /**
     * Registrar comisión para un asesor
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_asesor' => 'required|exists:usuarios,id',
            'id_tienda' => 'required|exists:tiendas,id',
            'valor'     => 'required|numeric|min:0',
            'fecha'     => 'required|date',
        ]);

        $comision = Comision::create(array_merge(
            $request->only('id_asesor', 'id_tienda', 'valor', 'fecha'),
            ['estado' => 'pendiente']
        ));

        return response()->json($comision->load(['asesor', 'tienda']), 201);
    }

    /**
     * Pagar comisión (Admin)
     */
    public function pagar(Comision $comision)
    {
        $comision->update(['estado' => 'pagada']);
        return response()->json(['message' => 'Comisión marcada como pagada.', 'comision' => $comision]);
    }

    /**
     * Mis comisiones (Asesor autenticado)
     */
    public function misComisiones(Request $request)
    {
        $comisiones = Comision::with('tienda')
            ->where('id_asesor', $request->user()->id)
            ->orderByDesc('fecha')
            ->paginate(20);

        $totalPendiente = Comision::where('id_asesor', $request->user()->id)->where('estado', 'pendiente')->sum('valor');
        $totalPagado    = Comision::where('id_asesor', $request->user()->id)->where('estado', 'pagada')->sum('valor');

        return response()->json([
            'comisiones'      => $comisiones,
            'total_pendiente' => $totalPendiente,
            'total_pagado'    => $totalPagado,
        ]);
    }
}
