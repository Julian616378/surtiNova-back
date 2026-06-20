<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'valor'       => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin'   => 'date',
        'estado'      => 'boolean',
    ];

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
