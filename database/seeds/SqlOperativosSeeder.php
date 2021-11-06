<?php

use Illuminate\Database\Seeder;

class SqlOperativosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Eloquent::unguard();
        $this->command->info('Sembrando operativos...');
        $path = 'app/developer_docs/operativos.sql';
        try{
            DB::unprepared(file_get_contents($path));
            }catch(Exception $e){
                if ($e->getCode()==23505){
                    $this->command->error('Operativos NO fueron plantados!');
                    return 0;
                }
            }
        $this->command->info('Operativos plantados!');
    }
}
