<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    //
    protected $table='provincia';

    protected $fillable = [
        'id','codigo','nombre'
    ];

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
}
