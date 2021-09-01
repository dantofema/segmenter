<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGobiernoLocalEntidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
/*        Schema::create('gobierno_local_entidad', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
*/
       $sql = file_get_contents(app_path() . '/developer_docs/gobierno_local_entidad.up.sql');
       DB::unprepared($sql);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gobierno_local_entidad');
    }
}
