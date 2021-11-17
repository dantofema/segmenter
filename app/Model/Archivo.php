<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
            'user_id','nombre_original','nombre','tipo','checksum','size','mime'
    ];

    // Funcion para cargar información de archivo en la base de datos.
    public static function cargar($request_file, $user, $tipo=null){
		$original_extension = strtolower($request_file->getClientOriginalExtension());
		$original_name = $request_file->getClientOriginalName();
		$random_name='t_'.$request_file->hashName();
		$file_storage = $request_file->storeAs('segmentador', $random_name.'.'.$request_file->getClientOriginalExtension());
		return self::create([
                            'user_id' => $user->id,
			    'nombre_original' => $original_name,
			    'nombre' => $file_storage,
			    'tipo' => $request_file->guessClientExtension()?$request_file->guessClientExtension():$original_extension,
			    'checksum'=> md5_file($request_file->getRealPath()),
                            'size' => $request_file->getClientSize(),
                            'mime' => $request_file->getClientMimeType()
                        ]);
	//return false;
    }
}
