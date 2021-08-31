<?php

use Illuminate\Database\Seeder;

class SqlParajeSeeder extends Seeder
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
        $this->command->info('Sembrando parajes...');
        $path = 'app/developer_docs/paraje.sql';
        try{
            DB::unprepared(file_get_contents($path));
            }catch(Exception $e){
                if ($e->getCode()==23505){
                    $this->command->error('Parajes NO fueron plantados!');
                    return 0;
                }
            }
        $this->command->info('Parajes plantados!');
    }
}
