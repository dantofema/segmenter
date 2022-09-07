<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listLoc = Permission::create(['name' => 'listarLocalidades']);
        $editLoc = Permission::create(['name' => 'editarLocalidades']);
        $deleteLoc = Permission::create(['name' => 'eliminarLocalidades']);

        $adminLoc = Role::create(['name' => 'adminLocalidades'])->syncPermissions([$listLoc, $editLoc, $deleteLoc]);
    }
}
