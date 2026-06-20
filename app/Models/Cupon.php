<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'descuento',
        'fecha_vencimiento',
        'usos_maximos',
        'estado',
    ];

    protected $casts = [
        'descuento' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'estado' => 'boolean',
    ];
}
