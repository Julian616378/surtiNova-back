<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory;

    protected $table = 'despachos';

    protected $fillable = [
        'id_pedido',
        'id_bodeguero',
        'fecha_preparacion',
        'fecha_despacho',
        'estado',
    ];

    protected $casts = [
        'fecha_preparacion' => 'datetime',
        'fecha_despacho'    => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function bodeguero()
    {
        return $this->belongsTo(Usuario::class, 'id_bodeguero');
    }
}
