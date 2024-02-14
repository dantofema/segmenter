<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->boolean('is_filter')->default(false); // Agrega la nueva columna 'is_filter' con valor predeterminado false
            });
        } else {
            throw new \Exception("La tabla 'permissions' no existe.");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('is_filter'); // En la migración de reversión, elimina la columna 'is_filter'
            });
        } else {
            throw new \Exception("La tabla 'permissions' no existe.");
        }
    }
};
