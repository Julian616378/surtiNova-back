<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionRepartidor extends Model
{
    protected $table = 'ubicacion_repartidores';

    protected $fillable = [
        'id_repartidor',
        'latitud',
        'longitud',
        'fecha_hora'
    ];

    public function repartidor()
    {
        return $this->belongsTo(Usuario::class, 'id_repartidor');
    }
}