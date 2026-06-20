<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventarios';

    protected $fillable = [
        'id_producto',
        'tipo',
        'cantidad',
        'responsable',
        'observacion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable');
    }
}
