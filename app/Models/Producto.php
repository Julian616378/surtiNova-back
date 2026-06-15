<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function inventario()
    {
        return $this->hasOne(Inventario::class, 'id_producto');
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }

    public function muestras()
    {
        return $this->hasMany(MuestraProducto::class, 'id_producto');
    }

    public function ofertas()
    {
        return $this->belongsToMany(
            Oferta::class,
            'oferta_producto',
            'id_producto',
            'id_oferta'
        );
    }
}