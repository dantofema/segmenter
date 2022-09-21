<?php

namespace Database\Seeders;

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
        try{
            $asignarRoles = Permission::create(['name' => 'Asignar Roles'],'asignador');
            $quitarRoles = Permission::create(['name' => 'Quitar Roles'],'vetador');

            $superAdmin = Role::create(['name' => 'Super Admin'])->syncPermissions([$asignarRoles, $quitarRoles]);
        } catch ( Spatie\Permission\Exceptions $e) {
            echo _($e->getMessage());
        }
    }
}
