<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvinciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     /**
        Schema::create('provincia', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

        });
     **/
/*
	 $sql = file_get_contents(database_path() . '/migrations/2020_01_02_202716_create_provincia_table.up.sql');
	 DB::unprepared($sql);
*/
	Schema::create('provincia', function (Blueprint $table) {
		$table->bigIncrements('id');
		$table->string('codigo');
		$table->string('nombre');
		$table->date('fecha_desde')->nullable();
		$table->date('fecha_hasta')->nullable();
		$table->integer('observacion_id')->nullable();
		$table->integer('geometria_id')->nullable();
		//$table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provincia');
    }
}
