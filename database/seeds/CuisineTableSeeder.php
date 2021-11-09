<?php

use Illuminate\Database\Seeder;

class CuisineTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('cuisine')->delete();
      DB::table('cuisine')->insert(array(
       array('id' => '1','name' => 'Afghan','description' => 'Afghan','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:06:16'),
       array('id' => '2','name' => 'African','description' => 'African','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:37:43'),
       array('id' => '3','name' => 'American','description' => 'American','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:38:07'),
       array('id' => '4','name' => 'Arabic','description' => 'Arabic','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:38:50'),
       array('id' => '5','name' => 'Bagels','description' => 'Bagels','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:43:53'),
       array('id' => '6','name' => 'Balti','description' => 'Balti','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:44:02'),
       array('id' => '7','name' => 'Bangladeshi','description' => 'Bangladeshi','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:44:11'),
       array('id' => '8','name' => 'BBQ','description' => 'BBQ','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:44:18'),
       array('id' => '9','name' => 'Breakfast','description' => 'Breakfast','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:44:27'),
       array('id' => '10','name' => 'British','description' => 'British','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:44:39'),
       array('id' => '11','name' => 'Burgers','description' => 'Burgers','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:45:42'),
       array('id' => '12','name' => 'Cakes','description' => 'Cakes','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:45:49'),
       array('id' => '13','name' => 'Caribbean','description' => 'Caribbean','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:46:09'),
       array('id' => '14','name' => 'Chicken','description' => 'Chicken','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:46:21'),
       array('id' => '15','name' => 'Chinese','description' => 'Chinese','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:46:32'),
       array('id' => '16','name' => 'Curry','description' => 'Curry','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:46:42'),
       array('id' => '17','name' => 'Desserts','description' => 'Desserts','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:46:52'),
       array('id' => '18','name' => 'Drinks','description' => 'Drinks','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:47:09'),
       array('id' => '19','name' => 'Fish & Chips','description' => 'Fish & Chips','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:47:17'),
       array('id' => '20','name' => 'Fusion','description' => 'Fusion','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:47:29'),
       array('id' => '21','name' => 'Gourmet','description' => 'Gourmet','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:48:25'),
       array('id' => '22','name' => 'Gourmet Burgers','description' => 'Gourmet Burgers','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:48:36'),
       array('id' => '23','name' => 'Grill','description' => 'Grill','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:48:46'),
       array('id' => '24','name' => 'Ice Cream','description' => 'Ice Cream','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:49:01'),
       array('id' => '25','name' => 'Indian','description' => 'Indian','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:49:19'),
       array('id' => '26','name' => 'Indonesian','description' => 'Indonesian','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:49:27'),
       array('id' => '27','name' => 'Iranian','description' => 'Iranian','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:49:42'),
       array('id' => '28','name' => 'Italian','description' => 'Italian','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:49:53'),
       array('id' => '29','name' => 'Jamaican','description' => 'Jamaican','status' => '1','is_top' => '1','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:50:04'),
       array('id' => '30','name' => 'Japanese','description' => 'Japanese','status' => '1','is_top' => '0','is_dietary' => '1','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => '2018-09-07 16:50:22'),
       array('id' => '31','name' => 'Kebab','description' => 'Kebab','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '32','name' => 'Korean','description' => 'Korean','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '33','name' => 'Kosher','description' => 'Kosher','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '34','name' => 'Lebanese','description' => 'Lebanese','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '35','name' => 'Lunch','description' => 'Lunch','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '36','name' => 'Mediterranean','description' => 'Mediterranean','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '37','name' => 'Middle Eastern','description' => 'Middle Eastern','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '38','name' => 'Milkshakes','description' => 'Milkshakes','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '39','name' => 'Nigerian','description' => 'Nigerian','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '40','name' => 'Noodles','description' => 'Noodles','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '41','name' => 'Oriental','description' => 'Oriental','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '42','name' => 'Pakistani','description' => 'Pakistani','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '43','name' => 'Peri Peri','description' => 'Peri Peri','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '44','name' => 'Persian','description' => 'Persian','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => NULL,'service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '45','name' => 'Pizza','description' => 'Pizza','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => '1','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '46','name' => 'Portuguese','description' => 'Portuguese','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '47','name' => 'South Indian','description' => 'South Indian','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '48','name' => 'Steak','description' => 'Steak','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '49','name' => 'Sushi','description' => 'Sushi','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '50','name' => 'Thai','description' => 'Thai','status' => '1','is_top' => '1','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '51','name' => 'Turkish','description' => 'Turkish','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => NULL,'service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL),
       array('id' => '52','name' => 'Vegan','description' => 'Vegan','status' => '1','is_top' => '0','is_dietary' => '0','most_popular' => '0','service_type' => '1','created_at' => '2018-05-28 21:10:00','updated_at' => NULL)
      )); 
  }
}
