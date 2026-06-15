<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_pedido');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_pedido');
    }

    public function despacho()
    {
        return $this->hasOne(Despacho::class, 'id_pedido');
    }

    public function entrega()
    {
        return $this->hasOne(Entrega::class, 'id_pedido');
    }

    public function rutas()
    {
        return $this->belongsToMany(
            Ruta::class,
            'pedido_ruta',
            'id_pedido',
            'id_ruta'
        );
    }
}