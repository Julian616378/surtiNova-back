<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'id_asesor');
    }

    public function visitas()
    {
        return $this->hasMany(VisitaComercial::class, 'id_asesor');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoComercial::class, 'id_asesor');
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_repartidor');
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class, 'id_bodeguero');
    }
}