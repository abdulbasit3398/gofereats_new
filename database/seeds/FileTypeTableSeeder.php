<?php

use Illuminate\Database\Seeder;

class FileTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('file_type')->delete();
    	
        DB::table('file_type')->insert([

            ['id' => '1','name' => 'site_setting'],
            ['id' => '2','name' => 'user_image'],
            ['id' => '3','name' => 'store_banner'],
            ['id' => '4','name' => 'store_logo'],
            ['id' => '5','name' => 'driver_image'],
            ['id' => '6','name' => 'menu_item_image'],
            ['id' => '7','name' => 'cuisine_image'],
            ['id' => '8','name' => 'driver_licence_front'],
            ['id' => '9','name' => 'driver_licence_back'],
            ['id' => '10','name' => 'driver_registeration_certificate'],
            ['id' => '11','name' => 'driver_insurance'],
            ['id' => '12','name' => 'driver_motor_certiticate'],
            ['id' => '13','name' => 'store_document'],
            ['id' => '14','name' => 'stripe_document'],
            ['id' => '15','name' => 'trip_image'],
            ['id' => '16','name' => 'map_image'],
            ['id' => '17','name' => 'store_home_slider'],
            ['id' => '18','name' => 'vehicle_image'],
            ['id' => '19','name' => 'dietary_icon'],
            ['id' => '20','name' => 'user_home_slider'],
            ['id' => '21','name' => 'service_type'],
            ['id' => '22','name' => 'social_login'],
            ['id' => '23','name' => 'user_service_image'],
            ['id' => '24','name' => 'user_food_image'],
            ['id' => '25','name' => 'user_alcohol_image'],
            ['id' => '26','name' => 'user_medicine_image'],
            ['id' => '27','name' => 'user_grocery_image'],
            ['id' => '28','name' => 'support_image'],
            ['id' => '29','name' => 'mobile_service_type'],
            ['id' => '30','name' => 'service_type_banner_image'],
        	]);
    }
}
