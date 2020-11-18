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
        MyDB::darPermisos('e0002');

        return view('home');
    }
}
