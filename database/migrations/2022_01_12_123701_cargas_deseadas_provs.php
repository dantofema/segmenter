<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CargasDeseadasProvs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $sql = file_get_contents(app_path() . '/developer_docs/cargas_deseadas_provs.sql');
       try{
           DB::unprepared($sql);
       }catch(Illuminate\Database\QueryException $e){
          DB::Rollback();
          echo _('Error creando relacion provincias_carga_deseada...
      ');
       }    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provincias_carga_deseada_segmento');
    }
}
