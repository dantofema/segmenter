<?php

use Illuminate\Database\Seeder;

class SqlDeptoSeeder extends Seeder
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
        $this->command->info('Sembrando departamentos...');
        $path = 'app/developer_docs/departamentos.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Departamentos plantados!');
    }
}
