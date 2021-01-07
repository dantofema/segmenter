<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MyDB;

class SetupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        MyDB::addUser('mretamozo');
        MyDB::addUser('halperin');
        MyDB::addUser('vheredia');
        MyDB::addUser('mretamozo','geoestadistica_test');


        MyDB::addUser('adibiase');
        MyDB::addUser('alitichever');
        MyDB::addUser('atoca');
        MyDB::addUser('efilgueira');
        MyDB::addUser('cdiaz');
        MyDB::addUser('fhaddad');
        MyDB::addUser('sfarace');
        MyDB::addUser('malves');
        MyDB::addUser('nallendes');
        MyDB::addUser('pdelsere');
        MyDB::addUser('sbouzas');
        MyDB::addUser('sfailde');
        MyDB::darPermisos('e0002');

        return view('home');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function permisos($schema)
    {
        MyDB::darPermisos($schema);
        return view('home');
    }

    public function cargarTopologia($schema)
    {
        MyDB::cargarTopologia($schema);
        flash('Se creo la topología para '.$schema);
        return view('home');
    }

    public function dropTopologia($schema)
    {
        MyDB::dropTopologia($schema);
        return view('home');
   
    public function addIndexListado($schema)
    {
        MyDB::addIndexListado($schema);
        flash('Se creo el indice para lados en listado en '.$schema);
        MyDB::addIndexListadoId($schema);
        flash('Se creo el indice para id listado en '.$schema);
        MyDB::addIndexListadoRadio($schema);
        flash('Se creo el indice para radio en listado en '.$schema);
        return view('home');
    }

    public function addIndexId($tabla)
    {
        MyDB::addIndexId($tabla);
        flash('Se creo el indice para id en '.$tabla);
        return view('home');
    }

    public function georeferenciarEsquema($schema)
    {
        MyDB::georeferenciar_listado($schema);
        flash('Se georeferencio el listado del esquema '.$schema);
        return view('home');
    
    }
    
}
