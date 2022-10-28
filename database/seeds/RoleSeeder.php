<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $this->command->info('Creando permisos del Super Admin...');
        try{
            $asignarRoles = Permission::create(['name' => 'Asignar Roles'],'asignador');
            $quitarRoles = Permission::create(['name' => 'Quitar Roles'],'vetador');

            $this->command->info('Creando rol Super Admin y asignando permisos...');
            $superAdmin = Role::create(['name' => 'Super Admin'])->syncPermissions([$asignarRoles, $quitarRoles]);
            $this->command->info('Rol Super Admin creado.');
        } catch ( Spatie\Permission\Exceptions\PermissionAlreadyExists $e) {
            $this->command->error('Permisos del Super Admin existentes...');

//            echo _($e->getMessage());
        } catch ( Spatie\Permission\Exceptions $e) {
            $this->command->error('Permisos del Super Admin desconocido');
//            echo _($e->getMessage());
        }
    }
}
