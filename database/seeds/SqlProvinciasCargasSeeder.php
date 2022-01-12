<?php

use Illuminate\Database\Seeder;

class SqlProvinciasCargasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        try{
          $this->command->info('ProvinciasCargas table seed!');
          $path = 'app/developer_docs/provincias_cargas.sql';
          DB::unprepared(file_get_contents($path));
        }catch(Exception $e){
             if ($e->getCode()==23505){
                 $this->command->error('ProvinciasCargas NO fueron plantados (ya existían)!');
                 return 0;
            }
        }
        $this->command->info('ProvinciasCargas table seeded!');
    }
}
