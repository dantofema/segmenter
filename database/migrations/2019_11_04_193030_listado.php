<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Listado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //id, prov, nom_provincia, ups, nro_area, dpto, nom_dpto, codaglo, codloc, nom_loc, codent, nom_ent, frac, radio, mza, lado, nro_inicial, nro_final, orden_recorrido_viv, nro_listado, ccalle, ncalle, nro_catastral, nrocatastralredef, piso, pisoredef, casa, dpto_habitacion, sector, edificio, entrada, cod_tipo_viv, cod_tipo_vivredef, cod_subt_vivloc, descripcion, descripcion_lado, cod_no_enc, fec_no_enc, motivo_no_enc, fec_alta_viv, fec_ult_mod, cod_postal, orden_recorrido_mza, estado, esta_supervisado, creadoen, chequeadoen, editadoen, borradoen, creado, chequeado, editado, borrado, actualizador, supervisor, usuario, tipo_base, tipo_tarea
	Schema::create ( 'lista', function ($table) {
		$table->integer ( 'id' );
		$table->string ('nombre');
		$table->string ( 'usuario' );
		$table->string ( 'tipo_base' );
		$table->string ( 'tipo_tarea' );
	});
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
