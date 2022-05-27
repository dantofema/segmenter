<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class Provincia extends Model
{
    //
    protected $table='provincia';

    protected $fillable = [
        'id','codigo','nombre'
    ];

    protected $url_json = 'https://geoservicios.indec.gob.ar/geoserver/sig/ows';
    protected $params = ['service'=>'WFS','version'=>'1.0.0','request'=>'GetFeature',
    'typeName'=>'sig:v_provincias','outputFormat'=>'application/json'];

    // Sin fecha de creación o modificación
    //
    public $timestamps = false;

     /**
     * Get the departamentos de la provincia.
     */
    public function departamentos()
    {
        return $this->hasMany('App\Model\Departamento');
    }

    /**
     * Relación con Fracciones, a través de departamentos puede tener muchas fracciones.
     */
    public function fracciones()
    {
        return $this->hasManyThrough('App\Model\Fraccion','App\Model\Departamento');
    }

    /**
     * Relación con Fracciones, a través de departamentos puede tener muchas fracciones.
     */
//    public function radios()
//    {
//        return $this->hasManyThrough('App\Model\Radio','App\Model\Departamento');
//    }
     /**
     * Get the json geoservicios de la provincia.
     */
    public function geojson()
    {
        return Http::get($this->url_json,
         Arr::add($this->params,"cql_filter","link =  '".$this->codigo."'")
        );
    }

}
