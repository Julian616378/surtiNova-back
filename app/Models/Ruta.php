<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'nombre',
        'fecha',
        'id_repartidor',
        'id_vehiculo',
        'estado',
        'hora_salida',
        'hora_fin',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Usuario::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    public function pedidos()
    {
        return $this->belongsToMany(
            Pedido::class,
            'pedido_ruta',
            'id_ruta',
            'id_pedido'
        );
    }
}
