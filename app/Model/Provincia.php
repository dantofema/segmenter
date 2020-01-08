<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    //
    protected $table='provincia';

    protected $fillable = [
        'codigo','nombre'
    ];

     /**
     * Get the departamentos de la provincia.
     */
    public function departamentos()
    {
        return $this->hasMany('App\Model\Departamento');
    }
}
