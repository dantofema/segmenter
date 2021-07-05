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
