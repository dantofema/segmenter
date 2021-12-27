<?php

use Illuminate\Database\Seeder;

class SqlProvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

//        $this->call('SqlSeeder');
        $this->command->info('Provincias table seed!');
        $path = 'app/developer_docs/provincias.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Provincias table seeded!');
    }
}
