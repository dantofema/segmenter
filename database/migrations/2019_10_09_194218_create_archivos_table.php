<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->bigInteger('user_id')->index();
            $table->string('nombre_original');
            $table->string('nombre');
            $table->string('tipo')->index();
            $table->string('checksum');
            $table->string('size');
            $table->string('mime');
            $table->boolean('procesado');
            $table->string('tabla')->nullable();
            $table->string('epsg_def')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos');
    }
}
