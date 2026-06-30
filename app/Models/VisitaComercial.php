<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaComercial extends Model
{
    use HasFactory;

    protected $table = 'visita_comercials';

    protected $fillable = [
        'id_asesor',
        'id_tienda',
        'fecha',
        'resultado',            // columna vieja (se mantiene por compatibilidad)
        'resultado_visita',     // columna nueva: registrada|no_acepto|no_estaba|muestra_entregada
        'observaciones',
        'proxima_visita',
        // campos de prospecto suelto (sin tienda aún)
        'nombre_prospecto',
        'telefono_prospecto',
        'direccion_prospecto',
        'latitud_prospecto',
        'longitud_prospecto',
        'id_muestra',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'proxima_visita' => 'date',
    ];

    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_asesor');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function muestra()
    {
        return $this->belongsTo(MuestraProducto::class, 'id_muestra');
    }
}