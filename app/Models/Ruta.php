<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    public function repartidor()
    {
        return $this->belongsTo(Usuario::class, 'id_repartidor');
    }

    public function pedidos()
    {
        return $this->belongsToMany(
            Pedido::class,
            'pedido_ruta',
            'id_ruta',
            'id_pedido'
        );
    }
}