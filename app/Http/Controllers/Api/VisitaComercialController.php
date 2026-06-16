<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaComercial;
use Illuminate\Http\Request;

class VisitaComercialController extends Controller
{

    public function index(Request $request)
{
    $visitas = VisitaComercial::with('tienda')
        ->where('id_asesor', $request->user()->id)
        ->orderByDesc('fecha')
        ->paginate(20);

    return response()->json($visitas);
}

public function store(Request $request)
{
    $request->validate([
        'id_tienda'       => 'required|exists:tiendas,id',
        'fecha'           => 'required|date',
        'proxima_visita'  => 'nullable|date|after:fecha',
        'observaciones'   => 'nullable|string',
    ]);

    $visita = VisitaComercial::create([
        'id_asesor'       => $request->user()->id,
        'id_tienda'       => $request->id_tienda,
        'fecha'           => $request->fecha,
        'proxima_visita'  => $request->proxima_visita,
        'observaciones'   => $request->observaciones,
    ]);

    return response()->json($visita->load('tienda'), 201);
}

public function show(VisitaComercial $visita)
{
    return response()->json($visita->load(['asesor', 'tienda']));
}

public function registrarResultado(Request $request, VisitaComercial $visita)
{
    $request->validate([
        'resultado'      => 'required|string',
        'observaciones'  => 'nullable|string',
        'proxima_visita' => 'nullable|date',
    ]);

    $visita->update([
        'resultado'      => $request->resultado,
        'observaciones'  => $request->observaciones,
        'proxima_visita' => $request->proxima_visita,
    ]);

    return response()->json($visita);
}
}
