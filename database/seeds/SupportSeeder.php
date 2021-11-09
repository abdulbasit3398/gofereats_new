<?php

use Illuminate\Database\Seeder;

class SupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('supports')->delete();
        DB::table('supports')->insert(array(
		    array('id' => '1','name' => 'WhatsApp','link' => '+916379630152','status' => 'Active','created_at' => NULL,'updated_at' => '2020-11-16 18:49:24'),
  			array('id' => '2','name' => 'Skype','link' => 'skype:trioangle?chat','status' => 'Active','created_at' => NULL,'updated_at' => '2020-11-21 00:42:24')
		));
    }
}
