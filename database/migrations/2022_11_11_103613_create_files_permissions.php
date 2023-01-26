<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call( 'db:seed', [
            '--class' => 'PermissionSeeder',
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
            //Permission::where(['name'=>'Ver Archivos'])->first()->delete();
            //Permission::where(['name'=>'Administrar Archivos'])->first()->delete();
            DB::commit();
    }
}
