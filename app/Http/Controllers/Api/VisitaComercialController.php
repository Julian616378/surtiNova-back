<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaComercial;
use Illuminate\Http\Request;

class VisitaComercialController extends Controller
{
    /**
     * Visitas del asesor autenticado
     */
    public function index(Request $request)
    {
        $visitas = VisitaComercial::with('tienda')
            ->where('id_asesor', $request->user()->id)
            ->orderByDesc('fecha_programada')
            ->paginate(20);

        return response()->json($visitas);
    }

    /**
     * Programar visita
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tienda'        => 'required|exists:tiendas,id',
            'fecha_programada' => 'required|date',
            'objetivo'         => 'nullable|string',
        ]);

        $visita = VisitaComercial::create([
            'id_asesor'        => $request->user()->id,
            'id_tienda'        => $request->id_tienda,
            'fecha_programada' => $request->fecha_programada,
            'objetivo'         => $request->objetivo,
            'estado'           => 'programada',
        ]);

        return response()->json($visita->load('tienda'), 201);
    }

    /**
     * Registrar resultado de visita
     */
    public function registrarResultado(Request $request, VisitaComercial $visita)
    {
        $request->validate([
            'resultado'       => 'required|string',
            'fecha_realizada' => 'required|date',
            'observaciones'   => 'nullable|string',
        ]);

        $visita->update([
            'resultado'       => $request->resultado,
            'fecha_realizada' => $request->fecha_realizada,
            'observaciones'   => $request->observaciones,
            'estado'          => 'realizada',
        ]);

        return response()->json($visita);
    }

    public function show(VisitaComercial $visita)
    {
        return response()->json($visita->load(['asesor', 'tienda']));
    }
}
