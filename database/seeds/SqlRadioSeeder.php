<?php

use Illuminate\Database\Seeder;

class SqlRadioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        $this->command->info('Sembrando radios...');
        $path = 'app/developer_docs/radio.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Radios plantados!');
    }
}
