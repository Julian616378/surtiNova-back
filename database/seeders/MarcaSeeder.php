<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $marcas = [

            'Colanta',
            'Noel',
            'Doria',
            'Diana',
            'Ramo',
            'Zenú',
            'Nestlé',
            'Coca Cola',
            'Postobón',
            'Alpina',
            'Familia',
            'Elite',

        ];

        foreach ($marcas as $marca){

            Marca::create([

                'nombre'=>$marca,
                'estado'=>true,

            ]);

        }
    }
}