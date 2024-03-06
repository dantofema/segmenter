<?php

namespace Database\seeders;

use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use App\User;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creando usuario Super Admin...');
        try {
            User::Create([
                'name' => 'Super Admin',
                'email' => 'superadmin@segmenter',
                'password' => bcrypt('superadmin')
               ])->assignRole('Super Admin');
            $this->command->info('Usuario Super Admin creado.');
        } catch (Exception $e) {
            echo _($e->getMessage());
        
        } catch (QueryException $e) {
            if ($e->getCode() == 23505) {
              User::where('email','superadmin@segmenter')->first()->assignRole('Super Admin');
            }else {
              echo _($e->getMessage());
            }
        }
        
    }
}
