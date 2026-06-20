<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoPrueba extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_pruebas';

    protected $fillable = [
        'id_muestra',
        'cantidad_vendida',
        'cantidad_devuelta',
        'valor_cobrado',
        'fecha',
        'observaciones',
    ];

    protected $casts = [
        'valor_cobrado' => 'decimal:2',
        'fecha'         => 'date',
    ];

    public function muestra()
    {
        return $this->belongsTo(MuestraProducto::class, 'id_muestra');
    }
}
