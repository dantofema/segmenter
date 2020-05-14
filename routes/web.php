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
    flash('Laravel 6 Flash Message')->success();
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

// ---------- AGLOMERADOS --------
Route::get('aglos-list', 'AglomeradoController@aglosList');
Route::post('aglos-list', 'AglomeradoController@aglosList');
Route::get('aglos','AglomeradoController@index');
Route::get('aglo/{aglomerado}','AglomeradoController@show');
Route::post('aglo/{aglomerado}','AglomeradoController@show_post');
Route::post('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::get('aglo-segmenta/{aglomerado}','AglomeradoController@segmenta_post');
Route::post('aglo-segmenta-run/{aglomerado}','AglomeradoController@run_segmentar');

// --------- SEGMENTACION X AGLOMERADO --------- 
Route::get('ver-segmentacion/{aglomerado}','AglomeradoController@ver_segmentacion')->name('ver-segmentacion');
Route::get('ver-segmentacion-lados/{aglomerado}','AglomeradoController@ver_segmentacion_lados')->name('ver-segmentacion-lados');
Route::get('ver-segmentacion/grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');
Route::post('ver-segmentacion-grafico/{aglomerado}','AglomeradoController@ver_segmentacion_grafico')->name('ver-segmentacion-grafico');

Route::get('ver-segmentacion/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
Route::post('ver-segmentacion-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_grafico_resumen')->name('ver-segmentacion-grafico-resumen');
//ver_segmentacion_lados_grafico_resumen
Route::get('ver-segmentacion-lados/grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');
Route::post('ver-segmentacion-lados-grafico-resumen/{aglomerado}','AglomeradoController@ver_segmentacion_lados_grafico_resumen')->name('ver-segmentacion-lados-grafico-resumen');


// ---------- GRAFOS AGLOMERADOS --------
Route::get('grafo/{aglomerado}','SegmentacionController@index');
Route::get('grafo/{aglomerado}/{radio}/','SegmentacionController@ver_grafo')->name('ver-grafo');

//Route::get('mail', 'MailCsvController@index');

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

Route::get('/home', 'HomeController@index')->name('home');
