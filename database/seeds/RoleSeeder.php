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
        $asignarRoles = Permission::create(['name' => 'Asignar Roles']);
        $quitarRoles = Permission::create(['name' => 'Quitar Roles']);

        $superAdmin = Role::create(['name' => 'Super Admin'])->syncPermissions([$asignarRoles, $quitarRoles]);
    }
}
