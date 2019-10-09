<?php

namespace App\Http\Controllers;

use App\Archivo;
use Illuminate\Http\Request;
use Auth;

class SegmenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('segmenter/index');
    }
    public function store(Request $request)
    {
        $data = [];
        if ($request->hasFile('shp')) {
            $data['shp'] = $request->shp->store('segmentador');
        }
        if ($request->hasFile('shx')) {
            $data['shx'] = $request->shx->store('segmentador');
        }
        if ($request->hasFile('prj')) {
            $data['prj'] = $request->prj->store('segmentador');
        }
        if ($request->hasFile('dbf')) {
            $data['dbf'] = $request->dbf->store('segmentador');
        }
        if ($request->hasFile('c1')) {
            $data['c1'] = $request->c1->store('segmentador');
        }

        if (Archivo::cargar($request, Auth::user())) {

            return view('segmenter/index', ['data' => $data]);
        } else {
            echo "Error en el modelo cargar";
        }
    }
}
