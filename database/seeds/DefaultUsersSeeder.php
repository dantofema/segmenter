<?php

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
        try {
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@segmenter',
                'password' => bcrypt('superadmin')
               ])->assignRole('Super Admin');
        } catch (Exception $e) {
            echo _($e->getMessage());
        }
        
    }
}
