<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaComercial extends Model
{
    protected $table = 'visita_comerciales';

    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_asesor');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }
}