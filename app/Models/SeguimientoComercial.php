<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoComercial extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_comercials';

    protected $fillable = [
        'id_tienda',
        'id_asesor',
        'fecha',
        'observacion',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_asesor');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }
}
