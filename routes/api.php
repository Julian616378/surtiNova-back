<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\TiendaController;
use App\Http\Controllers\Api\VisitaComercialController;
use App\Http\Controllers\Api\MuestraProductoController;
use App\Http\Controllers\Api\OfertaController;
use App\Http\Controllers\Api\CuponController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Api\RutaController;
use App\Http\Controllers\Api\EntregaController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\ComisionController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| SurtiNova API Routes
|--------------------------------------------------------------------------
| Prefijo base : /api  (configurado en bootstrap/app.php)
| Autenticación: Laravel Sanctum — Bearer Token
|--------------------------------------------------------------------------
*/

// ─────────────────────────────────────────────────────────────────────────────
// RUTAS PÚBLICAS (sin autenticación)
// ─────────────────────────────────────────────────────────────────────────────

Route::post('/usuarios', [UsuarioController::class, 'store']);
Route::post('/login',            [AuthController::class, 'login']);
Route::post('/cupones/validar',  [CuponController::class, 'validar']);

// ─────────────────────────────────────────────────────────────────────────────
// RUTAS PROTEGIDAS (requieren Bearer Token de Sanctum)
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────────
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/perfil',   [AuthController::class, 'perfil']);

    // ── Notificaciones (todos los roles) ─────────────────────────────────────
    Route::prefix('notificaciones')->group(function () {
        Route::get('/',                              [NotificacionController::class, 'index']);
        Route::get('/no-leidas',                     [NotificacionController::class, 'conteoNoLeidas']);
        Route::post('/marcar-todas',                 [NotificacionController::class, 'marcarTodasLeidas']);
        Route::patch('/{notificacion}/leer',         [NotificacionController::class, 'marcarLeida']);
    });

    // ── Catálogo (lectura para todos los roles autenticados) ─────────────────
    Route::get('/categorias',           [CategoriaController::class, 'index']);
    Route::get('/categorias/{categoria}',[CategoriaController::class, 'show']);
    Route::get('/productos',            [ProductoController::class,  'index']);
    Route::get('/productos/{producto}', [ProductoController::class,  'show']);
    Route::get('/ofertas',              [OfertaController::class,    'index']);
    Route::get('/ofertas/{oferta}',     [OfertaController::class,    'show']);


    // ─────────────────────────────────────────────────────────────────────────
    // TIENDA
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('tienda')->group(function () {
        Route::get('/mis-pedidos',                          [PedidoController::class,   'misPedidos']);
        Route::post('/pedidos',                             [PedidoController::class,   'store']);
        Route::get('/pedidos/{pedido}',                     [PedidoController::class,   'show']);
        Route::delete('/pedidos/{pedido}/cancelar',         [PedidoController::class,   'cancelar']);
        Route::get('/ubicacion-repartidor/{repartidorId}',  [UbicacionController::class,'ultimaUbicacion']);
    });


    // ─────────────────────────────────────────────────────────────────────────
    // ASESOR COMERCIAL
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('comercial')->group(function () {

        // Prospectos y tiendas
        Route::post('/prospectos',              [TiendaController::class,         'registrarProspecto']);
        Route::post('/tiendas',                 [TiendaController::class,         'store']);
        Route::get('/cartera',                  [TiendaController::class,         'misCartera']);
        Route::get('/tiendas/{tienda}',         [TiendaController::class,         'show']);
        Route::patch('/tiendas/{tienda}',       [TiendaController::class,         'update']);

        // Visitas comerciales
        Route::get('/visitas',                              [VisitaComercialController::class, 'index']);
        Route::post('/visitas',                             [VisitaComercialController::class, 'store']);
        Route::get('/visitas/{visita}',                     [VisitaComercialController::class, 'show']);
        Route::patch('/visitas/{visita}/resultado',         [VisitaComercialController::class, 'registrarResultado']);

        // Muestras / productos de prueba
        Route::get('/muestras',                             [MuestraProductoController::class, 'index']);
        Route::post('/muestras',                            [MuestraProductoController::class, 'store']);
        Route::get('/muestras/{muestra}',                   [MuestraProductoController::class, 'show']);
        Route::post('/muestras/{muestra}/seguimiento',      [MuestraProductoController::class, 'seguimiento']);

        // Comisiones del asesor
        Route::get('/mis-comisiones',           [ComisionController::class, 'misComisiones']);
    });


    // ─────────────────────────────────────────────────────────────────────────
    // BODEGUERO
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('bodega')->group(function () {

        // Pedidos
        Route::get('/pedidos-pendientes',               [PedidoController::class,   'pendientesBodega']);
        Route::get('/pedidos/{pedido}',                 [PedidoController::class,   'show']);

        // Despachos
        Route::get('/despachos',                        [DespachoController::class, 'index']);
        Route::post('/despachos/iniciar/{pedido}',      [DespachoController::class, 'iniciarPreparacion']);
        Route::patch('/despachos/{despacho}/confirmar', [DespachoController::class, 'confirmarDespacho']);
        Route::get('/despachos/{despacho}',             [DespachoController::class, 'show']);

        // Inventario
        // IMPORTANTE: la ruta estática /inventario/movimientos debe ir ANTES
        // de cualquier ruta dinámica /inventario/{param} para evitar que Laravel
        // trate "movimientos" como un parámetro de ruta.
        Route::get('/inventario',                       [InventarioController::class, 'index']);
        Route::get('/inventario/movimientos',           [InventarioController::class, 'movimientos']);
        Route::post('/inventario/entrada',              [InventarioController::class, 'entrada']);
        Route::post('/inventario/vencimiento',          [InventarioController::class, 'vencimiento']);

        // Productos críticos — también debe ir antes de /productos/{producto}
        Route::get('/productos/criticos',               [ProductoController::class, 'criticos']);
    });


    // ─────────────────────────────────────────────────────────────────────────
    // REPARTIDOR
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('repartidor')->group(function () {

        // Rutas asignadas
        Route::get('/mis-rutas',                    [RutaController::class,    'misRutas']);
        Route::get('/rutas/{ruta}',                 [RutaController::class,    'show']);
        Route::patch('/rutas/{ruta}/iniciar',       [RutaController::class,    'iniciar']);
        Route::patch('/rutas/{ruta}/finalizar',     [RutaController::class,    'finalizar']);

        // Ubicación GPS
        Route::post('/ubicacion',                   [UbicacionController::class, 'actualizar']);

        // Entregas
        Route::post('/entregas',                    [EntregaController::class,  'store']);
        Route::get('/mis-entregas',                 [EntregaController::class,  'misEntregas']);
        Route::get('/entregas/{entrega}',           [EntregaController::class,  'show']);

        // Pedidos de la ruta
        Route::get('/pedidos/{pedido}',             [PedidoController::class,   'show']);
    });


    // ─────────────────────────────────────────────────────────────────────────
    // ADMINISTRADOR
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard',                    [DashboardController::class, 'resumen']);
        Route::get('/dashboard/productos-top',      [DashboardController::class, 'productosTop']);

        // Reportes
        Route::prefix('reportes')->group(function () {
            Route::get('/ventas',       [DashboardController::class, 'reporteVentas']);
            Route::get('/inventario',   [DashboardController::class, 'reporteInventario']);
            Route::get('/rutas',        [DashboardController::class, 'reporteRutas']);
            Route::get('/clientes',     [DashboardController::class, 'reporteClientes']);
            Route::get('/comisiones',   [DashboardController::class, 'reporteComisiones']);
        });

        // Usuarios y roles
        Route::get('/roles',                        [UsuarioController::class, 'roles']);
        Route::apiResource('usuarios',              UsuarioController::class);

        // Categorías (CRUD completo — index y show ya están en el grupo público)
        Route::apiResource('categorias', CategoriaController::class)
             ->except(['index', 'show']);

        // Productos — CRÍTICO: rutas estáticas ANTES del apiResource para que
        // /productos/criticos no sea capturado como /productos/{producto}
        Route::get('productos/criticos',            [ProductoController::class, 'criticos']);
        Route::post('productos',                    [ProductoController::class, 'store']);
        Route::put('productos/{producto}',          [ProductoController::class, 'update']);
        Route::patch('productos/{producto}',        [ProductoController::class, 'update']);
        Route::delete('productos/{producto}',       [ProductoController::class, 'destroy']);

        // Inventario — igual: estáticas primero
        Route::get('inventario',                    [InventarioController::class, 'index']);
        Route::get('inventario/movimientos',        [InventarioController::class, 'movimientos']);
        Route::post('inventario/entrada',           [InventarioController::class, 'entrada']);
        Route::post('inventario/ajuste',            [InventarioController::class, 'ajuste']);
        Route::post('inventario/vencimiento',       [InventarioController::class, 'vencimiento']);

        // Tiendas
        Route::get('tiendas',                       [TiendaController::class, 'index']);
        Route::get('tiendas/{tienda}',              [TiendaController::class, 'show']);
        Route::patch('tiendas/{tienda}/estado',     [TiendaController::class, 'cambiarEstado']);
        Route::patch('tiendas/{tienda}/aprobar',    [TiendaController::class, 'aprobar']);
        Route::get('tiendas/{tienda}/pedidos',      [TiendaController::class, 'pedidos']);

        // Ofertas y cupones
        Route::apiResource('ofertas', OfertaController::class)->except(['index', 'show']);
        Route::apiResource('cupones', CuponController::class)->except(['index', 'show']);

        // Pedidos
        Route::get('pedidos',                       [PedidoController::class, 'index']);
        Route::get('pedidos/{pedido}',              [PedidoController::class, 'show']);
        Route::patch('pedidos/{pedido}/estado',     [PedidoController::class, 'cambiarEstado']);
        Route::delete('pedidos/{pedido}/cancelar',  [PedidoController::class, 'cancelar']);

        // Pagos — /pedidos/{pedido}/pagos debe ir antes del apiResource de pedidos
        // (ya no hay apiResource de pedidos aquí, pero se documenta igual)
        Route::get('pedidos/{pedido}/pagos',        [PagoController::class, 'porPedido']);
        Route::get('pagos',                         [PagoController::class, 'index']);
        Route::post('pagos',                        [PagoController::class, 'store']);
        Route::get('pagos/{pago}',                  [PagoController::class, 'show']);
        Route::patch('pagos/{pago}/estado',         [PagoController::class, 'actualizarEstado']);

        // Facturas
        Route::get('facturas',                      [FacturaController::class, 'index']);
        Route::post('facturas',                     [FacturaController::class, 'store']);
        Route::get('facturas/{factura}',            [FacturaController::class, 'show']);

        // Vehículos
        Route::get('vehiculos-disponibles',         [VehiculoController::class, 'disponibles']);
        Route::apiResource('vehiculos',             VehiculoController::class);

        // Rutas de distribución
        Route::get('rutas',                         [RutaController::class, 'index']);
        Route::post('rutas',                        [RutaController::class, 'store']);
        Route::get('rutas/{ruta}',                  [RutaController::class, 'show']);
        Route::patch('rutas/{ruta}/reordenar',      [RutaController::class, 'reordenar']);

        // Ubicación GPS de repartidores
        Route::get('ubicacion/{repartidorId}',              [UbicacionController::class, 'ultimaUbicacion']);
        Route::get('ubicacion/{repartidorId}/historial',    [UbicacionController::class, 'historial']);

        // Muestras (lectura admin)
        Route::get('muestras',                      [MuestraProductoController::class, 'index']);
        Route::get('muestras/{muestra}',            [MuestraProductoController::class, 'show']);

        // Comisiones
        Route::get('comisiones',                    [ComisionController::class, 'index']);
        Route::post('comisiones',                   [ComisionController::class, 'store']);
        Route::patch('comisiones/{comision}/pagar', [ComisionController::class, 'pagar']);

        // Auditorías
        Route::get('auditorias',                    [AuditoriaController::class, 'index']);
        Route::get('auditorias/{auditoria}',        [AuditoriaController::class, 'show']);
    });
});