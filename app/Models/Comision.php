<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $table = 'comisiones';

    protected $fillable = [
        'id_asesor',
        'id_tienda',
        'valor',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
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
