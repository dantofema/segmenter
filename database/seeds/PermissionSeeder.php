<?php

namespace Database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrayOfPermissionNames = ['Editar Radio', 'Probando123'];
        
        $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });

        foreach ($permissions as $permission) {
            $this->command->info('Creando permiso '.$permission['name']);
            try{
                echo _("HOLA");
                Permission::create($permission);
                $this->command->info('Permiso '.$permission['name'].' creado.');
            } catch ( Spatie\Permission\Exception $e) {
                echo _($e->getMessage());
            }
        }
    }
}
