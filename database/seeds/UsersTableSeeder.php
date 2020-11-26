<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Ale',
            'email' => 'indec@indec.com',
            'password' => bcrypt('password'),
        ]);
        DB::table('users')->insert([
            'name' => 'Manu',
            'email' => 'manuel@retamozo.com.ar',
            'password' => bcrypt('adminadmin'),
        ]);
    }
}
