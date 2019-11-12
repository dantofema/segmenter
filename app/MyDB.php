<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class MyDB extends Model
{
    //a
	public static function createSchema($esquema)
	{
	 DB::statement('CREATE SCHEMA IF NOT EXISTS e'.$esquema);
	}
}
