<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'tipo',
        'capacidad',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_vehiculo');
    }
}
