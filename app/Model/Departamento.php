<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    //
     protected $table = 'departamentos';

    protected $fillable = [
        'codigo','nombre'
    ];
    /**
     * Obtener la provicnia a donde pertencen el Departamento.
     */
    public function provincia()
    {
        return $this->belongsTo('App\Model\Provincia');
    }
}
