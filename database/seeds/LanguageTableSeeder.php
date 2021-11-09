<?php

use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('language')->delete();
    	
      DB::table('language')->insert([
      ['name' => 'English','value' => 'en','status' => '1','default_language'=>'1'],
      ['name' => 'Português','value' => 'pt','status' => '1','default_language'=>'0'],
      ['name' => 'العربية','value' => 'ar','status' => '1','default_language'=>'0'],
      ]);
    }
}
