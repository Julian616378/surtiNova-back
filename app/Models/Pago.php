<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Pago extends Model
{
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
}