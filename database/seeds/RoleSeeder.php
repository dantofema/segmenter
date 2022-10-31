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
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $allRoles = Role::all()->pluck('name')->toArray();
        try{
            //if ( Count(Permission::findByName('Asignar Roles')->get()) == 0 )
            if ( !in_array('Asignar Roles', $allPermissions) )
              $asignarRoles = Permission::create(['name' => 'Asignar Roles'],'asignador');
            else
              $asignarRoles = $allPermissions['Asignar Roles'];
            if ( !in_array('Quitar Roles', $allPermissions) )
              $quitarRoles = Permission::create(['name' => 'Quitar Roles'],'vetador');
            else
              $quitarRoles = $allPermissions['Quitar Roles'];

            $this->command->info('Creando rol Super Admin y asignando permisos...');
            if ( !in_array('Super Admin',$allRoles)) {
              $superAdmin = Role::create(['name' => 'Super Admin'])->syncPermissions([$asignarRoles, $quitarRoles]);
              $this->command->info('Rol Super Admin creado.');
            }
        } catch (      Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            $this->command->info('Permiso no existe...'.$e->getMessage());
        } catch ( Spatie\Permission\Exceptions\PermissionAlreadyExists $e) {
            $this->command->info('Permisos del Super Admin existentes...');

//            echo _($e->getMessage());
        } catch ( Spatie\Permission\Exceptions $e) {
            $this->command->info('Permisos del Super Admin desconocido');
//            echo _($e->getMessage());
        }
    }
}
