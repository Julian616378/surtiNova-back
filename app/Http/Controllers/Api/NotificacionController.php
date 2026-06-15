<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $notificaciones = Notificacion::where('id_usuario', $request->user()->id)
            ->when($request->no_leidas, fn($q) => $q->where('leida', false))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($notificaciones);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        $this->authorize('update', $notificacion); // Solo el dueño puede marcarla
        $notificacion->update(['leida' => true]);
        return response()->json(['message' => 'Notificación marcada como leída.']);
    }

    /**
     * Marcar todas las notificaciones del usuario como leídas
     */
    public function marcarTodasLeidas(Request $request)
    {
        Notificacion::where('id_usuario', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas.']);
    }

    /**
     * Conteo de no leídas (útil para badge en Flutter)
     */
    public function conteoNoLeidas(Request $request)
    {
        $count = Notificacion::where('id_usuario', $request->user()->id)
            ->where('leida', false)
            ->count();

        return response()->json(['no_leidas' => $count]);
    }
}
