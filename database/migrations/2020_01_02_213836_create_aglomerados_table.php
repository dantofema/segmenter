<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAglomeradosTable extends Migration
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
	 $sql = file_get_contents(app_path() . '/developer_docs/aglomerados.up.sql');
      try{
           DB::unprepared($sql);
       }catch(Illuminate\Database\QueryException $e){
          DB::Rollback();
	        echo _('Omitiendo creación de tabla de aglomerados...
		      ');
       }
      try{
        Schema::table('aglomerados', function (Blueprint $table) {
            $table->index(['id']);
            $table->index(['codigo']);
        });
       }catch(Illuminate\Database\QueryException $e){
	        echo _('Omitiendo creación de indices de aglomerados...
');
       }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('aglomerados');
    }
}
