<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnaSegController;
use App\Http\Controllers\BanorteController;
use App\Http\Controllers\ChubbController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CotizacionHistorialController;
use App\Http\Controllers\GSController;
use App\Http\Controllers\MapfreController;
use App\Http\Controllers\NAdminController;
use App\Http\Controllers\NAutoController;
use App\Http\Controllers\NClientesController;
use App\Http\Controllers\NCursosController;
use App\Http\Controllers\NEmpleadosController;
use App\Http\Controllers\NSubClientsController;
use App\Http\Controllers\NTareasController;
use App\Http\Controllers\NVideosController;
use App\Http\Controllers\QualitasController;
use App\Http\Controllers\SepomexController;
use App\Http\Controllers\TestController;
use App\Models\NTareasModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/





$router->get('/', function () use ($router) {
    //return project details as json
    return response()->json(
        [
            "ws" => "api_rest",
            "content" => [
                "name" => "Seguros Villagomez",
                "autocotizador" => "https://www.autocotizador.villagomezseguros.com",
                "version" => "1.0.0",
                "author" => "PROCEL TI",
            ],
            "access" => "Inauthorized",
        ],
        200
    );
});

$router->post('/admin', NAdminController::class . '@store');
// getMantenimiento
$router->get('/admin/mantenimiento/{provider}', NAdminController::class . '@getMantenimiento');
// updateMantenimiento
$router->put('/admin/mantenimiento/{provider}', NAdminController::class . '@updateMantenimiento');
$router->get('/admin/test', AdminController::class . '@test');
$router->post('/admin/login', NAdminController::class . '@login');
$router->put('/admin/profile/{id}', NAdminController::class . '@update');
$router->get('/admin/profile/discounts', NAdminController::class . '@getDescuentos');

$router->post('/general_quote', CotizacionController::class . '@cotizar');

$router->get('/empleados', NEmpleadosController::class . '@getEmpleados');
$router->post('/empleados', NEmpleadosController::class . '@store');
$router->post('/empleados/sucursales', NEmpleadosController::class . '@getSucursales');
$router->put('/empleados/update/{empleado_id}', NempleadosController::class . '@update');

$router->post('/empleados/tareas', NTareasController::class . '@store');
$router->post('/empleados/tareasByEmpleado', NTareasController::class . '@getTareasByEmpleado');
$router->delete('/empleados/tareas/delete', NTareasController::class . '@deleteTarea');
$router->put('/empleados/tareas', NTareasController::class . '@updateEstado');

$router->post('/clientes', NCursosController::class . '@store');
$router->get('/clientes/find', NClientesController::class . '@getClienteById');
$router->get('/clientes/search', NClientesController::class . '@searchClientes');
$router->put('/clientes/{id}', NClientesController::class . '@update');
$router->delete('/clientes/delete', NClientesController::class . '@delete');
$router->post('/clientes/login', NClientesController::class . '@login');

$router->post('/subclientes', NSubClientsController::class . '@store');
$router->get('/subclientes', NSubClientsController::class . '@getSubClients');
$router->get('/subclientes/search', NSubClientsController::class . '@searchSubClients');
$router->put('/subclientes/{id}', NSubClientsController::class . '@updateSubClient');
$router->delete('/subclientes/delete', NSubClientsController::class . '@deleteSubClient');

$router->post('/cursos/categorias', NCursosController::class . '@getCategorias');
$router->post('/cursos', NCursosController::class . '@store');
$router->get('/cursos/search', NCursosController::class . '@searchCursos');
$router->put('/cursos/{id}', NCursosController::class . '@update');

//NVideosController
$router->post('/videos', NVideosController::class . '@store');
$router->get('/videos/search', NVideosController::class . '@searchVideos');
$router->put('/videos/{id}', NVideosController::class . '@update');



$router->get('/clientes/autos/{cliente_id}', NAutoController::class . '@getAutosByClient');
$router->post('/clientes/autos', NAutoController::class . '@store');
$router->put('/clientes/autos/{id}', NAutoController::class . '@update');

//Web Service Providers
$router->post('/gs/token', GSController::class . '@getToken');
$router->post('/gs/tokenTest', TestController::class . '@getTokenTest');
$router->post('/gs/brands', GSController::class . '@getMarcas');
$router->post('/gs/sub_brands', GSController::class . '@getSubMarcas');
$router->post('/gs/models', GSController::class . '@getModelos');
$router->post('/gs/versions', GSController::class . '@getVersions');
$router->post('/gs/quote', GSController::class . '@getCotizacion');
$router->post('/gs/quote_details', GSController::class . '@getCobertura');
$router->post('/gs/emission', GSController::class . '@sendCotizacion');

$router->post('/ana/quote', AnaSegController::class . '@setCotizacion');
$router->post('/ana/emition', AnaSegController::class . '@setEmition');
$router->post('/ana/locations', AnaSegController::class . '@getLocations');
//get banks
$router->post('/ana/banks', AnaSegController::class . '@getBanks');
// get colors
$router->post('/ana/colors', AnaSegController::class . '@getColors');
// getOcupations
$router->post('/ana/ocupations', AnaSegController::class . '@getOcupations');
// getNacionalities
$router->post('/ana/nacionalities', AnaSegController::class . '@getNacionalities');
// getModelos
$router->post('/ana/models', AnaSegController::class . '@getModelos');

$router->post('/mapfre/brands', MapfreController::class . '@getMarcas');
$router->post('/mapfre/models', MapfreController::class . '@getModelos');
$router->post('/mapfre/states', MapfreController::class . '@getEstados');
$router->post('/mapfre/quote', MapfreController::class . '@setCotizacion');
$router->post('/mapfre/emission', MapfreController::class . '@setEmision');
$router->get('/mapfre/gettoken', MapfreController::class . '@getToken');

//payemision
$router->post('/mapfre/payemision', MapfreController::class . '@payEmision');

$router->post('/mapfre/quote_xml', MapfreController::class . '@setCotizacionXML');
$router->post('/mapfre/emission_xml', MapfreController::class . '@setEmisionXML');

//provincias
$router->post('/mapfre/provinces', MapfreController::class . '@getProvincias');

//getMarcas of QualitasController
$router->post('/qualitas/brands', QualitasController::class . '@getMarcas');
$router->post('/qualitas/quote', QualitasController::class . '@setQuote');
$router->post('/qualitas/emission', QualitasController::class . '@setEmision');
//getExample
$router->post('/qualitas/example', QualitasController::class . '@getExample');
// digito verificador
$router->get('/qualitas/dv/{claveAmis}', QualitasController::class . '@getDigitoVerificador');

//banorte
$router->post('/banorte/quote', BanorteController::class . '@cotizar');
$router->post('/banorte/emission', BanorteController::class . '@setEmision');

//chubb
$router->post('/chubb/token', ChubbController::class . '@getToken');
//get marcas
$router->post('/chubb/quote', ChubbController::class . '@setQuote');
$router->post('/chubb/emitir', ChubbController::class . '@setEmitir');


// historial de cotizaciones
$router->get('/historial/getAll', CotizacionHistorialController::class . '@getAll');
$router->get('/historial/notemited', CotizacionHistorialController::class . '@getNotEmitted');
$router->get('/historial/emited', CotizacionHistorialController::class . '@getEmitted');
$router->post('/historial/create', CotizacionHistorialController::class . '@store');
$router->put('/historial/update/{id}', CotizacionHistorialController::class . '@updateEmitir');
$router->put('/historial/updateFolio/{id}', CotizacionHistorialController::class . '@updateFolio');
// getByClienteId
$router->get('/historial/getByClienteId', CotizacionHistorialController::class . '@getByClienteId');


// NUM_POLISA_GRUPO

//put sepomex routes here
$router->get('/sepomex/getColonias/{d_codigo}', SepomexController::class . '@getColonias');
