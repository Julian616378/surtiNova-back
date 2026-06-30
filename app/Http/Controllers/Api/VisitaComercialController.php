<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaComercial;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

    /**
     * GET /comercial/visitas/ruta-hoy?lat=xx&lng=yy
     *
     * Devuelve las tiendas que el asesor debe visitar hoy:
     *   - Prospectos (estado = 'prospecto') asignados a él
     *   - Tiendas activas cuya próxima_visita ya venció o es hoy
     * Si se pasan lat/lng, las ordena por cercanía (greedy nearest-neighbor Haversine).
     * Respuesta: { total: N, tiendas: [...] }
     */
    public function rutaHoy(Request $request)
    {
        $asesorId = $request->user()->id;
        $hoy      = Carbon::today();

        // 1. Tiendas pendientes de visitar hoy
        $tiendas = Tienda::where('id_asesor', $asesorId)
            ->where(function ($q) use ($hoy) {
                // prospectos que nunca han sido visitados
                $q->where('estado', 'prospecto')
                  // o tiendas activas/en_prueba con visita vencida o programada para hoy
                  ->orWhere(function ($q2) use ($hoy) {
                      $q2->whereIn('estado', ['activa', 'en_prueba', 'registrada'])
                         ->where(function ($q3) use ($hoy) {
                             $q3->whereHas('visitas', function ($v) use ($hoy) {
                                 // última visita tiene proxima_visita <= hoy
                                 $v->where('proxima_visita', '<=', $hoy);
                             })->orWhereDoesntHave('visitas'); // nunca visitada
                         });
                  });
            })
            ->get();

        // 2. Ordenar por cercanía si el asesor envía su ubicación
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if ($lat !== null && $lng !== null) {
            $tiendas = $this->ordenarPorCercania($tiendas, (float)$lat, (float)$lng);
        }

        return response()->json([
            'total'   => $tiendas->count(),
            'tiendas' => $tiendas->values(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tienda'        => 'required|exists:tiendas,id',
            'fecha_programada' => 'required|date',
            'objetivo'         => 'nullable|string',
        ]);

        $visita = VisitaComercial::create([
            'id_asesor' => $request->user()->id,
            'id_tienda' => $request->id_tienda,
            'fecha'     => $request->fecha_programada,
            'observaciones' => $request->objetivo,
        ]);

        return response()->json($visita->load('tienda'), 201);
    }

    public function show(VisitaComercial $visita)
    {
        return response()->json($visita->load(['asesor', 'tienda']));
    }

    /**
     * PATCH /comercial/visitas/{visita}/resultado
     * Acepta 'resultado_visita' (campo nuevo) o 'resultado' (campo viejo, retrocompat).
     */
    public function registrarResultado(Request $request, VisitaComercial $visita)
{
    $request->validate([
        'resultado_visita' => 'nullable|string|in:registrada,no_acepto,no_estaba,muestra_entregada',
        'resultado'        => 'nullable|string',
        'observaciones'    => 'nullable|string',
        'proxima_visita'   => 'nullable|date',
    ]);

    $resultado = $request->resultado_visita ?? $request->resultado;

    $visita->update([
        'resultado_visita' => $resultado,
        'resultado'        => $resultado,
        'observaciones'    => $request->observaciones,
        'proxima_visita'   => $request->proxima_visita,
    ]);

    // ✅ Transición automática de estado según el resultado
    if ($visita->id_tienda) {
        $nuevoEstado = match($resultado) {
            'registrada'       => 'activa',
            'muestra_entregada'=> 'en_prueba',
            'no_acepto'        => 'inactiva',
            default            => null, // no_estaba: no cambia
        };
        if ($nuevoEstado) {
            Tienda::where('id', $visita->id_tienda)->update(['estado' => $nuevoEstado]);
        }
    }

    return response()->json($visita->load('tienda'));
}

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Greedy nearest-neighbor con distancia Haversine.
     * Empieza desde la posición del asesor y va eligiendo la tienda
     * más cercana no visitada aún.
     */
    private function ordenarPorCercania($tiendas, float $lat, float $lng)
    {
        $pendientes = $tiendas->filter(fn($t) => $t->latitud !== null && $t->longitud !== null);
        $sinGps     = $tiendas->filter(fn($t) => $t->latitud === null || $t->longitud === null);

        $ordenadas  = collect();
        $currentLat = $lat;
        $currentLng = $lng;

        while ($pendientes->isNotEmpty()) {
            $masNear = $pendientes->sortBy(fn($t) =>
                $this->haversine($currentLat, $currentLng, (float)$t->latitud, (float)$t->longitud)
            )->first();

            $ordenadas->push($masNear);
            $currentLat = (float)$masNear->latitud;
            $currentLng = (float)$masNear->longitud;
            $pendientes = $pendientes->except([$masNear->id]);   // quita por id
        }

        // Las que no tienen GPS van al final
        return $ordenadas->concat($sinGps);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r  = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a  = sin($dLat / 2) ** 2 +
              cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}