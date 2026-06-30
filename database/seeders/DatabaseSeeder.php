<?php
//php artisan db:seed DatabaseSeeder
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // 1. ROLES
        // ─────────────────────────────────────────────
        $roles = [
            ['nombre' => 'admin',       'descripcion' => 'Administrador del sistema con acceso total'],
            ['nombre' => 'asesor',      'descripcion' => 'Asesor comercial encargado de gestionar tiendas y pedidos'],
            ['nombre' => 'cliente',     'descripcion' => 'Cliente final que realiza compras'],
            ['nombre' => 'repartidor',  'descripcion' => 'Repartidor encargado de las entregas en ruta'],
            ['nombre' => 'bodeguero',   'descripcion' => 'Encargado del inventario y despacho en bodega'],
        ];

        DB::table('roles')->insert($roles);

        $idAdmin      = DB::table('roles')->where('nombre', 'admin')->value('id');
        $idAsesor     = DB::table('roles')->where('nombre', 'asesor')->value('id');
        $idCliente    = DB::table('roles')->where('nombre', 'cliente')->value('id');
        $idRepartidor = DB::table('roles')->where('nombre', 'repartidor')->value('id');
        $idBodeguero  = DB::table('roles')->where('nombre', 'bodeguero')->value('id');

        // ─────────────────────────────────────────────
        // 2. USUARIOS
        // ─────────────────────────────────────────────
        DB::table('usuarios')->insert([
            [
                'nombre'   => 'Super',
                'apellido' => 'Admin',
                'correo'   => 'admin@surtinova.com',
                'telefono' => '3001234567',
                'password' => Hash::make('123456'),
                'estado'   => true,
                'id_rol'   => $idAdmin,
            ],
            [
                'nombre'   => 'Carlos',
                'apellido' => 'Mendoza',
                'correo'   => 'asesor1@surtinova.com',
                'telefono' => '3109876543',
                'password' => Hash::make('123456!'),
                'estado'   => true,
                'id_rol'   => $idAsesor,
            ],
            [
                'nombre'   => 'Carlos',
                'apellido' => 'Mendoza',
                'correo'   => 'cliente1@surtinova.com',
                'telefono' => '3109876543',
                'password' => Hash::make('123456!'),
                'estado'   => true,
                'id_rol'   => $idCliente,
            ],
            [
                'nombre'   => 'Laura',
                'apellido' => 'Ríos',
                'correo'   => 'asesor2@surtinova.com',
                'telefono' => '3207654321',
                'password' => Hash::make('123456!'),
                'estado'   => true,
                'id_rol'   => $idAsesor,
            ],
            [
                'nombre'   => 'Pedro',
                'apellido' => 'Gómez',
                'correo'   => 'repartidor1@surtinova.com',
                'telefono' => '3151112233',
                'password' => Hash::make('123456'),
                'estado'   => true,
                'id_rol'   => $idRepartidor,
            ],
            [
                'nombre'   => 'Andrés',
                'apellido' => 'Torres',
                'correo'   => 'bodeguero1@surtinova.com',
                'telefono' => '3164445566',
                'password' => Hash::make('123456'),
                'estado'   => true,
                'id_rol'   => $idBodeguero,
            ],
        ]);

        $idAsesor1 = DB::table('usuarios')->where('correo', 'asesor1@surtinova.com')->value('id');
        $idAsesor2 = DB::table('usuarios')->where('correo', 'asesor2@surtinova.com')->value('id');

        // ─────────────────────────────────────────────
        // 3. TIENDAS
        // ─────────────────────────────────────────────
        DB::table('tiendas')->insert([
            [
                'nombre'     => 'Supermercado El Ahorro',
                'nit'        => '900123456-1',
                'propietario'=> 'Juan Pérez',
                'telefono'   => '6057891234',
                'correo'     => 'elahorro@gmail.com',
                'direccion'  => 'Cra 45 # 80-12, Barranquilla',
                'latitud'    => 10.9878,
                'longitud'   => -74.7889,
                'estado'     => 'activa',
                'id_asesor'  => $idAsesor1,
            ],
            [
                'nombre'     => 'Tienda La Esquina',
                'nit'        => null,
                'propietario'=> 'María López',
                'telefono'   => '3004567890',
                'correo'     => null,
                'direccion'  => 'Cll 72 # 50-33, Barranquilla',
                'latitud'    => 11.0041,
                'longitud'   => -74.8070,
                'estado'     => 'activa',
                'id_asesor'  => $idAsesor1,
            ],
            [
                'nombre'     => 'Minimercado Familiar',
                'nit'        => '800654321-2',
                'propietario'=> 'Roberto Silva',
                'telefono'   => '3058889900',
                'correo'     => 'familiar@hotmail.com',
                'direccion'  => 'Av. Murillo # 23-10, Barranquilla',
                'latitud'    => 10.9730,
                'longitud'   => -74.7960,
                'estado'     => 'en_prueba',
                'id_asesor'  => $idAsesor2,
            ],
            [
                'nombre'     => 'Distribuidora Norte',
                'nit'        => '901234789-0',
                'propietario'=> 'Claudia Herrera',
                'telefono'   => '6052223344',
                'correo'     => 'dnorte@gmail.com',
                'direccion'  => 'Zona Industrial, Barranquilla',
                'latitud'    => 11.0120,
                'longitud'   => -74.7800,
                'estado'     => 'prospecto',
                'id_asesor'  => $idAsesor2,
            ],
        ]);

        // ─────────────────────────────────────────────
        // 4. CATEGORÍAS
        // ─────────────────────────────────────────────
        $categorias = [
            ['nombre' => 'Lácteos',          'descripcion' => 'Leches, quesos, yogures y derivados lácteos'],
            ['nombre' => 'Granos y Cereales', 'descripcion' => 'Arroz, lentejas, frijoles, maíz y cereales'],
            ['nombre' => 'Bebidas',           'descripcion' => 'Jugos, gaseosas, agua y bebidas energéticas'],
            ['nombre' => 'Aseo del Hogar',    'descripcion' => 'Detergentes, jabones, desinfectantes y limpiadores'],
            ['nombre' => 'Snacks',            'descripcion' => 'Pasabocas, galletas, maíz pira y mecatos'],
            ['nombre' => 'Aceites y Grasas',  'descripcion' => 'Aceites vegetales, mantequilla y margarina'],
            ['nombre' => 'Enlatados',         'descripcion' => 'Atún, sardinas, tomates y conservas'],
            ['nombre' => 'Aseo Personal',     'descripcion' => 'Shampoo, jabón de baño, cremas y desodorantes'],
        ];

        DB::table('categorias')->insert($categorias);

        $catLacteos  = DB::table('categorias')->where('nombre', 'Lácteos')->value('id');
        $catGranos   = DB::table('categorias')->where('nombre', 'Granos y Cereales')->value('id');
        $catBebidas  = DB::table('categorias')->where('nombre', 'Bebidas')->value('id');
        $catAseo     = DB::table('categorias')->where('nombre', 'Aseo del Hogar')->value('id');
        $catSnacks   = DB::table('categorias')->where('nombre', 'Snacks')->value('id');
        $catAceites  = DB::table('categorias')->where('nombre', 'Aceites y Grasas')->value('id');
        $catEnlatados= DB::table('categorias')->where('nombre', 'Enlatados')->value('id');

        // ─────────────────────────────────────────────
        // 5. MARCAS
        // ─────────────────────────────────────────────
        $marcas = [
            ['nombre' => 'Alpina',    'descripcion' => 'Productos lácteos colombianos', 'estado' => true],
            ['nombre' => 'Alquería',  'descripcion' => 'Leches y bebidas lácteas',      'estado' => true],
            ['nombre' => 'Coca-Cola', 'descripcion' => 'Bebidas gaseosas y jugos',      'estado' => true],
            ['nombre' => 'Postobón',  'descripcion' => 'Gaseosas y jugos nacionales',   'estado' => true],
            ['nombre' => 'Roa',       'descripcion' => 'Galletas y snacks',             'estado' => true],
            ['nombre' => 'Colanta',   'descripcion' => 'Cooperativa láctea colombiana', 'estado' => true],
            ['nombre' => 'Arroz Diana','descripcion'=> 'Arroz de alta calidad',         'estado' => true],
            ['nombre' => 'Carulla',   'descripcion' => 'Marca propia supermercado',     'estado' => true],
        ];

        DB::table('marcas')->insert($marcas);

        $mAlpina   = DB::table('marcas')->where('nombre', 'Alpina')->value('id');
        $mAlqueria = DB::table('marcas')->where('nombre', 'Alquería')->value('id');
        $mCoca     = DB::table('marcas')->where('nombre', 'Coca-Cola')->value('id');
        $mPostobon = DB::table('marcas')->where('nombre', 'Postobón')->value('id');
        $mRoa      = DB::table('marcas')->where('nombre', 'Roa')->value('id');
        $mColanta  = DB::table('marcas')->where('nombre', 'Colanta')->value('id');
        $mDiana    = DB::table('marcas')->where('nombre', 'Arroz Diana')->value('id');

        // ─────────────────────────────────────────────
        // 6. PRODUCTOS
        // ─────────────────────────────────────────────
        $productos = [
            // Lácteos
            [
                'nombre'         => 'Leche Entera Alpina 1L',
                'codigo'         => 'LAC-001',
                'codigo_barras'  => '7702001000011',
                'descripcion'    => 'Leche entera pasteurizada en bolsa',
                'id_categoria'   => $catLacteos,
                'id_marca'       => $mAlpina,
                'precio_compra'  => 2800.00,
                'precio'         => 3500.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'L',
                'compra_minima'  => 6,
                'multiplo_compra'=> 6,
                'stock'          => 120,
                'stock_minimo'   => 24,
                'destacado'      => true,
                'oferta'         => false,
                'estado'         => true,
            ],
            [
                'nombre'         => 'Yogurt Alpina Fresa 200g',
                'codigo'         => 'LAC-002',
                'codigo_barras'  => '7702001000028',
                'descripcion'    => 'Yogurt saborizado de fresa en vaso',
                'id_categoria'   => $catLacteos,
                'id_marca'       => $mAlpina,
                'precio_compra'  => 1400.00,
                'precio'         => 2000.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'g',
                'compra_minima'  => 12,
                'multiplo_compra'=> 12,
                'stock'          => 80,
                'stock_minimo'   => 20,
                'destacado'      => false,
                'oferta'         => false,
                'estado'         => true,
            ],
            [
                'nombre'         => 'Leche UHT Alquería Paquete x6',
                'codigo'         => 'LAC-003',
                'codigo_barras'  => '7702034000035',
                'descripcion'    => 'Paquete de 6 bolsas de leche UHT entera 900ml c/u',
                'id_categoria'   => $catLacteos,
                'id_marca'       => $mAlqueria,
                'precio_compra'  => 16000.00,
                'precio'         => 20500.00,
                'presentacion'   => 'Paquete',
                'contenido'      => 6,
                'unidad_medida'  => 'L',
                'compra_minima'  => 1,
                'multiplo_compra'=> 1,
                'stock'          => 50,
                'stock_minimo'   => 10,
                'destacado'      => true,
                'oferta'         => false,
                'estado'         => true,
            ],
            // Granos
            [
                'nombre'         => 'Arroz Diana Extra 5Kg',
                'codigo'         => 'GRA-001',
                'codigo_barras'  => '7702999000042',
                'descripcion'    => 'Arroz blanco de primera calidad, bulto 5kg',
                'id_categoria'   => $catGranos,
                'id_marca'       => $mDiana,
                'precio_compra'  => 13500.00,
                'precio'         => 17000.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'Kg',
                'compra_minima'  => 5,
                'multiplo_compra'=> 5,
                'stock'          => 200,
                'stock_minimo'   => 30,
                'destacado'      => true,
                'oferta'         => false,
                'estado'         => true,
            ],
            [
                'nombre'         => 'Frijol Bola Roja x500g',
                'codigo'         => 'GRA-002',
                'codigo_barras'  => '7700001000059',
                'descripcion'    => 'Frijol bola roja seco, bolsa 500g',
                'id_categoria'   => $catGranos,
                'id_marca'       => null,
                'precio_compra'  => 2500.00,
                'precio'         => 3800.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'g',
                'compra_minima'  => 10,
                'multiplo_compra'=> 10,
                'stock'          => 150,
                'stock_minimo'   => 20,
                'destacado'      => false,
                'oferta'         => false,
                'estado'         => true,
            ],
            // Bebidas
            [
                'nombre'         => 'Gaseosa Coca-Cola 1.5L',
                'codigo'         => 'BEB-001',
                'codigo_barras'  => '7421000000066',
                'descripcion'    => 'Gaseosa Coca-Cola original botella 1.5 litros',
                'id_categoria'   => $catBebidas,
                'id_marca'       => $mCoca,
                'precio_compra'  => 3200.00,
                'precio'         => 4500.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'L',
                'compra_minima'  => 12,
                'multiplo_compra'=> 12,
                'stock'          => 300,
                'stock_minimo'   => 48,
                'destacado'      => true,
                'oferta'         => false,
                'estado'         => true,
            ],
            [
                'nombre'         => 'Gaseosa Postobón Manzana 400ml Caja x24',
                'codigo'         => 'BEB-002',
                'codigo_barras'  => '7702006000073',
                'descripcion'    => 'Caja con 24 unidades de Postobón Manzana 400ml',
                'id_categoria'   => $catBebidas,
                'id_marca'       => $mPostobon,
                'precio_compra'  => 38000.00,
                'precio'         => 48000.00,
                'presentacion'   => 'Caja',
                'contenido'      => 24,
                'unidad_medida'  => 'ml',
                'compra_minima'  => 1,
                'multiplo_compra'=> 1,
                'stock'          => 60,
                'stock_minimo'   => 10,
                'destacado'      => false,
                'oferta'         => true,
                'estado'         => true,
            ],
            // Aseo
            [
                'nombre'         => 'Detergente Fab x1000g',
                'codigo'         => 'ASE-001',
                'codigo_barras'  => '7801008000080',
                'descripcion'    => 'Detergente en polvo para ropa Fab 1kg',
                'id_categoria'   => $catAseo,
                'id_marca'       => null,
                'precio_compra'  => 4800.00,
                'precio'         => 6500.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'g',
                'compra_minima'  => 6,
                'multiplo_compra'=> 6,
                'stock'          => 90,
                'stock_minimo'   => 12,
                'destacado'      => false,
                'oferta'         => false,
                'estado'         => true,
            ],
            // Snacks
            [
                'nombre'         => 'Galletas Festival Limón x6 paquetes',
                'codigo'         => 'SNK-001',
                'codigo_barras'  => '7702016000097',
                'descripcion'    => 'Pack 6 paquetes de galletas Festival sabor limón',
                'id_categoria'   => $catSnacks,
                'id_marca'       => $mRoa,
                'precio_compra'  => 4500.00,
                'precio'         => 6000.00,
                'presentacion'   => 'Paquete',
                'contenido'      => 6,
                'unidad_medida'  => 'Unidad',
                'compra_minima'  => 5,
                'multiplo_compra'=> 5,
                'stock'          => 110,
                'stock_minimo'   => 15,
                'destacado'      => false,
                'oferta'         => false,
                'estado'         => true,
            ],
            // Enlatados
            [
                'nombre'         => 'Atún Van Camps en Agua x170g',
                'codigo'         => 'ENL-001',
                'codigo_barras'  => '7420073000104',
                'descripcion'    => 'Atún en agua lata 170g',
                'id_categoria'   => $catEnlatados,
                'id_marca'       => null,
                'precio_compra'  => 3200.00,
                'precio'         => 4200.00,
                'presentacion'   => 'Unidad',
                'contenido'      => 1,
                'unidad_medida'  => 'g',
                'compra_minima'  => 12,
                'multiplo_compra'=> 12,
                'stock'          => 180,
                'stock_minimo'   => 24,
                'destacado'      => false,
                'oferta'         => false,
                'estado'         => true,
            ],
        ];

        // Agregar timestamps a todos
        $now = now();
        foreach ($productos as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }

        DB::table('productos')->insert($productos);

        // ─────────────────────────────────────────────
        // 7. INVENTARIOS
        // ─────────────────────────────────────────────
        $todosProductos = DB::table('productos')->get(['id', 'stock', 'stock_minimo']);

        $inventarios = $todosProductos->map(fn($p) => [
            'id_producto'     => $p->id,
            'cantidad_actual' => $p->stock,
            'cantidad_minima' => $p->stock_minimo,
            'created_at'      => $now,
            'updated_at'      => $now,
        ])->toArray();

        DB::table('inventarios')->insert($inventarios);

        // ─────────────────────────────────────────────
        // 8. VEHÍCULOS
        // ─────────────────────────────────────────────
        DB::table('vehiculos')->insert([
            ['placa' => 'ABC-123', 'tipo' => 'Camioneta',  'capacidad' => 500,  'estado' => true],
            ['placa' => 'XYZ-456', 'tipo' => 'Furgón',     'capacidad' => 1500, 'estado' => true],
            ['placa' => 'DEF-789', 'tipo' => 'Motocicleta','capacidad' => 50,   'estado' => true],
            ['placa' => 'GHI-001', 'tipo' => 'Camión',     'capacidad' => 3000, 'estado' => false],
        ]);
            $this->call([
        // ...tus otros seeders...
        RutaDemoSeeder::class,
    ]);
    }
}