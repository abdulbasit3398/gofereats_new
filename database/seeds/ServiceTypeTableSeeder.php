<?php

use Illuminate\Database\Seeder;

class ServiceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service_type')->delete();
        DB::table('service_type')->insert(array(
        array('id' => '1','service_name' => 'Food Delivery','service_description'=>'When it comes to delivery, the first and foremost preferred delivery service of the customer is food. Facilitate food delivery services from the nearby restaurants for your customers.','has_addon' => 'Yes','status' => '1','created_at' => '2020-05-28 10:01:19','updated_at' => '2020-05-28 10:01:19'),
        ));
    }
}
