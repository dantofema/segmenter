<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{

    // Fucnion para cargar archivo en la base de datos.
    public static function cargar($request_file, $user){
	return true;
    }
}
