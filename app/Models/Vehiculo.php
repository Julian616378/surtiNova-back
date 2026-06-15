<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_vehiculo');
    }
}