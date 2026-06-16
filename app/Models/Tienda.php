<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $fillable = [
    'nombre',
    'nit',
    'propietario',
    'telefono',
    'correo',
    'direccion',
    'latitud',
    'longitud',
    'estado',
    'id_asesor',
];
    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_asesor');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_tienda');
    }

    public function visitas()
    {
        return $this->hasMany(VisitaComercial::class, 'id_tienda');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoComercial::class, 'id_tienda');
    }

    public function muestras()
    {
        return $this->hasMany(MuestraProducto::class, 'id_tienda');
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'id_tienda');
    }
}
