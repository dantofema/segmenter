<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class CreateBorrarXxPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call( 'db:seed', [
          '--class' => 'PermissionSeederBorrar',
          '--force' => true ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
            /** elimino los permisos */
            Permission::where(['name'=>'Borrar Departamento'])->first()->delete();
            Permission::where(['name'=>'Borrar Localdiad'])->first()->delete();
            Permission::where(['name'=>'Borrar Fracción'])->first()->delete();
            Permission::where(['name'=>'Borrar Radio'])->first()->delete();
            Permission::where(['name'=>'Borrar Aglomerado'])->first()->delete();
            Permission::where(['name'=>'Borrar Paraje'])->first()->delete();
            Permission::where(['name'=>'Borrar Entidad'])->first()->delete();
            Permission::where(['name'=>'Borrar Base Antártica'])->first()->delete();
            DB::commit();
    }
}
