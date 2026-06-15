<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoPrueba extends Model
{
    protected $table = 'seguimiento_pruebas';

    public function muestra()
    {
        return $this->belongsTo(MuestraProducto::class, 'id_muestra');
    }
}