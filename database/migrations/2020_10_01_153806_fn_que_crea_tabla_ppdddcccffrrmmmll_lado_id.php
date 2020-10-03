<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FnQueCreaTablaPpdddcccffrrmmmllLadoId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $path = 'app/developer_docs/Segmentacion-CORE/lados_completos/cargar_conteos.sql';
        DB::unprepared(file_get_contents($path));
        // fix bug diferente nombres de campos
        $path = 'app/developer_docs/Segmentacion-CORE/lados_completos/segmentos_lados_desde_hasta.sql';
        DB::unprepared(file_get_contents($path));
        // fix crea tabla para seguir un segemto desde hasta y por los lados que pasa

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
