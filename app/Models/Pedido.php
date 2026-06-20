<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'id_tienda',
        'fecha_pedido',
        'fecha_estimada',
        'estado',
        'subtotal',
        'descuento',
        'total',
    ];

    protected $casts = [
        'fecha_pedido'  => 'datetime',
        'fecha_estimada' => 'datetime',
        'subtotal'      => 'decimal:2',
        'descuento'     => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_pedido');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_pedido');
    }

    public function despacho()
    {
        return $this->hasOne(Despacho::class, 'id_pedido');
    }

    public function entrega()
    {
        return $this->hasOne(Entrega::class, 'id_pedido');
    }

    public function rutas()
    {
        return $this->belongsToMany(
            Ruta::class,
            'pedido_ruta',
            'id_pedido',
            'id_ruta'
        );
    }
}
