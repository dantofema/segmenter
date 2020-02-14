<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
    		UsersTableSeeder::class,
	        // Bunch of seeders using Eloquent
	        SqlSeeder::class,
            SqlDeptoSeeder::class,
            SqlLocSeeder::class,
            SqlLocDeptoSeeder::class,
            SqlAgloSeeder::class,
            SqlGobLocSeeder::class,
            SqlFraccionSeeder::class,
            SqlRadioSeeder::class,
            SqlRadioLocSeeder::class
]);


    }
}
