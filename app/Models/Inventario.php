<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}