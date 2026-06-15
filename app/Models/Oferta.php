<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'oferta_producto',
            'id_oferta',
            'id_producto'
        );
    }
}