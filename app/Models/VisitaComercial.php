<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaComercial extends Model
{
    protected $fillable = [
    'id_asesor',
    'id_tienda',
    'fecha',
    'resultado',
    'observaciones',
    'proxima_visita',
];
    protected $table = 'visita_comercials';

    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_asesor');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }
}