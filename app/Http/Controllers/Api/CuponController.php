<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    public function index()
    {
        return response()->json(Cupon::orderByDesc('created_at')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'            => 'required|string|unique:cupones,codigo',
            'descuento'         => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date|after:today',
            'usos_maximos'      => 'required|integer|min:1',
            'estado'            => 'sometimes|boolean',
        ]);

        $cupon = Cupon::create($request->only('codigo', 'descuento', 'fecha_vencimiento', 'usos_maximos', 'estado'));

        return response()->json($cupon, 201);
    }

    public function show(Cupon $cupon)
    {
        return response()->json($cupon);
    }

    /**
     * Validar cupón antes de aplicarlo al pedido (llamado desde Flutter)
     */
    public function validar(Request $request)
    {
        $request->validate(['codigo' => 'required|string']);

        $cupon = Cupon::where('codigo', $request->codigo)->first();

        if (! $cupon) {
            return response()->json(['valid' => false, 'message' => 'Cupón no encontrado.'], 404);
        }

        if (! $cupon->estado) {
            return response()->json(['valid' => false, 'message' => 'Cupón inactivo.'], 422);
        }

        if ($cupon->fecha_vencimiento < now()) {
            return response()->json(['valid' => false, 'message' => 'Cupón vencido.'], 422);
        }

        $usosActuales = $cupon->pedidos()->count();
        if ($usosActuales >= $cupon->usos_maximos) {
            return response()->json(['valid' => false, 'message' => 'Cupón agotado.'], 422);
        }

        return response()->json(['valid' => true, 'descuento' => $cupon->descuento, 'cupon' => $cupon]);
    }

    public function update(Request $request, Cupon $cupon)
    {
        $request->validate([
            'descuento'         => 'sometimes|numeric|min:0',
            'fecha_vencimiento' => 'sometimes|date',
            'usos_maximos'      => 'sometimes|integer|min:1',
            'estado'            => 'sometimes|boolean',
        ]);

        $cupon->update($request->only('descuento', 'fecha_vencimiento', 'usos_maximos', 'estado'));

        return response()->json($cupon);
    }

    public function destroy(Cupon $cupon)
    {
        $cupon->update(['estado' => false]);
        return response()->json(['message' => 'Cupón desactivado.']);
    }
}
