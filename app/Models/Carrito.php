<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';

    protected $fillable = [
        'id_tienda',
        'id_producto',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'cantidad'        => 'integer',
    ];

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
