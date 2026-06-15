<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'password',
        'estado',
        'id_rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'id_asesor');
    }

    public function visitas()
    {
        return $this->hasMany(VisitaComercial::class, 'id_asesor');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoComercial::class, 'id_asesor');
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_repartidor');
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class, 'id_bodeguero');
    }
}