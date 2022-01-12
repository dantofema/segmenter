<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvinciasCargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      If (! Schema::hasTable('provincias_cargas')) {
        Schema::create('provincias_cargas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('prov')->index();
            $table->integer('deseada')->nullable();
        });
      }else{
        echo _('Omitiendo creación de tabla de provincias_cargas existente...
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
        Schema::dropIfExists('provincias_cargas');
    }
}
