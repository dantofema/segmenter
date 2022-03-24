<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tetete', function () {
    return view('test3');
});

Route::get('/tete', function () {
    return view('test2');
});

Route::get('/testeando', function () {
    return view('test');
});

Auth::routes();

/**
* Estructura mas ordenada de aplicacion, layout...
*/
Route::get('/home', function()
{
    return View::make('pages.home');
});
Route::get('/about', function()
{
    return View::make('pages.about');
});
Route::get('projects', function()
{
    return View::make('pages.projects');
});
Route::get('/contact', function()
{
    return View::make('pages.contact');
});
Route::get('/serverinfo', function()
{
    return View::make('pages.serverinfo');
});

Route::get('/guia', function()
{
    return View::make('segmentacion.guia');
});
Route::get('/sala', 'SalaController@index')->name('sala');

Route::get('/setup', 'SetupController@index')->name('setup');
Route::get('/setup/test', 'SetupController@testFlash')->name('setup.test');
Route::get('/setup/{esquema}', 'SetupController@permisos')->name('setup.permisos');
Route::get('/setup/topo/{esquema}',
'SetupController@cargarTopologia')->name('setup.topologia');
Route::get('/setup/topo_drop/{esquema}',
'SetupController@dropTopologia')->name('setup.drop.topologia');
Route::get('/setup/index/{esquema}',
'SetupController@addIndexListado')->name('setup.index');
Route::get('/setup/index/id/{tabla}',
'SetupController@addIndexId')->name('setup.indexId');
Route::get('/setup/geo/{esquema}',
'SetupController@georeferenciarEsquema')->name('setup.geo');
Route::get('/setup/geo/{esquema}/{force}',
'SetupController@georeferenciarEsquema')->name('setup.geo.force');
Route::get('/setup/geoseg/{esquema}',
'SetupController@georeferenciarSegmentacionEsquema')->name('setup.geoseg');
Route::get('/setup/segmenta/{esquema}',
'SetupController@segmentarEsquema')->name('setup.segmenta');
Route::get('/setup/limpia/{esquema}',
'SetupController@limpiarEsquema')->name('setup.limpia');
Route::get('/setup/muestrea/{esquema}',
'SetupController@muestreaEsquema')->name('setup.muestrea');
Route::get('/setup/junta/{esquema}',
'SetupController@juntarSegmentos')->name('setup.junta');
Route::get('/setup/index/{esquema}/{tabla}/{cols}',
'SetupController@createIndex')->name('setup.create.index');
Route::get('/setup/grupogeoestadistica/{usuario}',
'SetupController@grupoGeoestadistica')->name('setup.grupogeo');
Route::get('/setup/grupogeoestadistica/tabla/{tabla}',
'SetupController@grupoGeoestadisticaTabla')->name('setup.grupogeo.tabla');
Route::get('/setup/duplicadosLSV/{esquema}',
'SetupController@limpiaListado')->name('setup.limpialistado');
Route::get('/setup/updateTipoViv/{esquema}',
'SetupController@tipoVivdeDescripcion')->name('setup.tipovivdescripcion');
Route::get('/setup/update/R3',
'SetupController@juntaR3')->name('setup.juntaR3');
Route::get('/setup/adyacencias/{esquema}',
'SetupController@generarAdyacenciasEsquema')->name('setup.adyacencias');


Route::get('/inicio', 'HomeController@index')->name('inicio');
Route::resource('/listado', 'ListadoController',['only' => [
   'index', 'show', 'save'
]]);
Route::post('/domicilio/guardar/','DomicilioController@save');
/**
 * Segmenter
 */
Route::get('/segmentador', 'SegmenterController@index')->name('segmentador');
Route::post('/segmentador/guardar', 'SegmenterController@store');

Route::get('/', function () {
    flash(' Bienvenides !')->success();
    return view('welcome');
});

Route::post('/import', ['as'=>'import', 'uses'=>'Controller@import']);

Route::get('csv_file', 'CsvFile@index');

Route::get('csv_file/export', 'CsvFile@csv_export')->name('export');

Route::post('csv_file/import', 'CsvFile@csv_import')->name('import');

Route::get('search_provincia', 'AutoCompleteProvinciaController@index');
Route::get('autocomplete_provincia', 'AutoCompleteProvinciaController@search');
Route::get('provincia','ProvinciaController@index');

// ---------- PROVINCIAS --------
Route::get('provs-list', 'ProvinciaController@provsList'); 
Route::get('provs','ProvinciaController@index');
Route::get('prov/{provincia}','ProvinciaController@show');
Route::post('prov/{provincia}','ProvinciaController@show_post');

// ---------- DEPARTAMENTOS --------

Route::get('prov/deptos/{provincia?}','DepartamentoController@index');
Route::get('prov/list/{provincia}','DepartamentoController@list');
Route::get('depto/{departamento}','DepartamentoController@show');
Route::post('depto/{departamento}','DepartamentoController@show_post');

// ---------- LOCALIDADES --------
Route::get('locas-list', 'LocalidadController@locasList');
Route::post('locas-list', 'LocalidadController@locasList');
Route::get('localidades','LocalidadController@list');
Route::get('localidades_json','LocalidadController@index');
Route::get('localidad/{localidad}','LocalidadController@show');
Route::post('localidad/{localidad}','LocalidadController@segmenta_post');
Route::post('localidad-segmenta/{localidad}','LocalidadController@segmenta_post');
Route::get('localidad-segmenta/{localidad}','LocalidadController@segmenta_post');
Route::post('localidad-segmenta-run/{localidad}','LocalidadController@run_segmentar');
//Route::post('localidad/{localidad}','LocalidadController@show_post');
// PxSeg Localidad
Route::get('localidad/{localidad}/pxseg','LocalidadController@ver_pxseg')->name('localidad-ver-segmentacion-pxseg');
Route::get('localidad/{localidad}/segmentacion','LocalidadController@ver_segmentacion')->name('localidad-ver-segmentacion');
Route::get('localidad/{localidad}/segmentacion-lados','LocalidadController@ver_segmentacion_lados')->name('localidad-ver-segmentacion-lados');
Route::get('localidad/{localidad}/grafico','LocalidadController@ver_segmentacion_grafico')->name('localidad-ver-segmentacion-grafico');
Route::post('localidad/{localidad}/grafico','LocalidadController@ver_segmentacion_grafico_resumen')->name('localidad-ver-segmentacion-grafico');

// ---------- AGLOMERADOS --------
Route::get('aglos-list', 'AglomeradoController@aglosList');
Route::post('aglos-list', 'AglomeradoController@aglosList');
Route::get('aglos','AglomeradoController@index');
Route::get('aglo/{aglomerado}','AglomeradoController@show')->name('aglo-ver');
Route::post('aglo/{aglomerado}','AglomeradoController@show_post');
Route::post('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::get('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::post('aglo-segmenta-run/{aglomerado}','AglomeradoController@run_segmentar');

// --------- SEGMENTACION X AGLOMERADO --------- 
Route::get('aglo/{aglomerado}/pxseg','AglomeradoController@ver_pxseg')->name('ver-segmentacion-pxseg');
Route::get('ver-segmentacion/{aglomerado}','AglomeradoController@ver_segmentacion')->name('ver-segmentacion');
Route::get('ver-segmentacion-lados/{aglomerado}','AglomeradoController@ver_segmentacion_lados')->name('ver-segmentacion-lados');
Route::get('ver-segmentacion/grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');
Route::post('ver-segmentacion-grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');

Route::get('ver-segmentacion/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
Route::post('ver-segmentacion-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
//ver_segmentacion_lados_grafico_resumen
Route::get('ver-segmentacion-lados/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');
Route::post('ver-segmentacion-lados-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');

// ---------- RADIOS Localidad Depto --------
// Para CABA
Route::get('radios/{localidad}/{departamento}','RadiosController@show');
Route::get('radio/{radio}','RadioController@show');

// ---------- GRAFOS AGLOMERADOS --------
Route::get('grafo/{aglomerado}','SegmentacionController@index')->name('index');
Route::get('grafo/{aglomerado}/{radio}/','SegmentacionController@ver_grafo_legacy')->name('ver-grafo-redirect');
Route::get('radio/{localidad}/{radio}/','SegmentacionController@ver_grafo')->name('ver-grafo');

// ---------- ARCHIVOS --------
Route::post('archivos','ArchivoController@index');
Route::get('archivos','ArchivoController@index');
Route::get('archivo/{archivo}','ArchivoController@show');
Route::delete('archivo/{archivo}','ArchivoController@destroy');
Route::get('archivo/{archivo}/descargar','ArchivoController@descargar');


// ---------- TABLERO ---------

Route::get('informe/prov','TableroController@GraficoProvincias');
Route::post('informe/prov','TableroController@GraficoProvincias');
Route::get('informe/avances','TableroController@GraficoAvances');
Route::post('informe/avances','TableroController@GraficoAvances');
Route::get('informe/avance','TableroController@GraficoAvance');
Route::post('informe/avance','TableroController@GraficoAvance');

//Route::get('mail', 'MailCsvController@index');

/* Logout via GET */
Route::get('/logout', 'Auth\LoginController@logout');

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('Admin')->name('admin/')->group(static function() {
        Route::prefix('admin-users')->name('admin-users/')->group(static function() {
            Route::get('/',                                             'AdminUsersController@index')->name('index');
            Route::get('/create',                                       'AdminUsersController@create')->name('create');
            Route::post('/',                                            'AdminUsersController@store')->name('store');
            Route::get('/{adminUser}/edit',                             'AdminUsersController@edit')->name('edit');
            Route::post('/{adminUser}',                                 'AdminUsersController@update')->name('update');
            Route::delete('/{adminUser}',                               'AdminUsersController@destroy')->name('destroy');
            Route::get('/{adminUser}/resend-activation',                'AdminUsersController@resendActivationEmail')->name('resendActivationEmail');
        });
    });
});

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('Admin')->name('admin/')->group(static function() {
        Route::get('/profile',                                      'ProfileController@editProfile')->name('edit-profile');
        Route::post('/profile',                                     'ProfileController@updateProfile')->name('update-profile');
        Route::get('/password',                                     'ProfileController@editPassword')->name('edit-password');
        Route::post('/password',                                    'ProfileController@updatePassword')->name('update-password');
    });
});
Auth::routes();

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::get('/home', 'HomeController@index')->name('home');
