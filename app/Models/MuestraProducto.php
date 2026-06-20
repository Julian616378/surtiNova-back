<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuestraProducto extends Model
{
    use HasFactory;

    protected $table = 'muestra_productos';

    protected $fillable = [
        'id_tienda',
        'id_producto',
        'cantidad',
        'fecha_entrega',
        'fecha_revision',
        'estado',
    ];

    protected $casts = [
        'fecha_entrega'  => 'date',
        'fecha_revision' => 'date',
    ];

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoPrueba::class, 'id_muestra');
    }
}
