<?php

use Illuminate\Database\Seeder;

class FileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('file')->delete();
      DB::table('file')->insert(array(
       
       array('id' => '1','type' => '1','name' => 'logo1534427307.png','source' => '1','source_id' => '1','created_at' => NULL,'updated_at' => '2021-03-18 00:56:33'),
       array('id' => '2','type' => '1','name' => 'favicon.png','source' => '1','source_id' => '2','created_at' => NULL,'updated_at' => '2021-03-18 00:56:33'),
       array('id' => '3','type' => '1','name' => 'logo1534427307.png','source' => '1','source_id' => '3','created_at' => NULL,'updated_at' => '2021-03-18 00:56:33'),
       array('id' => '4','type' => '1','name' => 'logo1534427307.png','source' => '1','source_id' => '4','created_at' => NULL,'updated_at' => '2021-03-18 00:56:33'),
       array('id' => '5','type' => '1','name' => 'footer_logo.png','source' => '1','source_id' => '5','created_at' => NULL,'updated_at' => '2021-03-18 00:56:33'),
       array('id' => '6','type' => '1','name' => 'app_logo.png','source' => '1','source_id' => '6','created_at' => NULL,'updated_at' => '2018-08-31 23:52:59'),
       array('id' => '7','type' => '1','name' => 'driver_logo.png','source' => '1','source_id' => '7','created_at' => NULL,'updated_at' => '2018-08-31 23:52:59'),
       array('id' => '8','type' => '1','name' => 'driver_white_logo.png','source' => '1','source_id' => '8','created_at' => NULL,'updated_at' => '2018-08-31 23:52:59'),

       array('id' => '9','type' => '17','name' => 'businesspartnerns11535970022.jpg','source' => '1','source_id' => '1','created_at' => '2018-09-04 02:50:22','updated_at' => '2018-09-04 02:50:22'),
       array('id' => '10','type' => '17','name' => '920x9201535970072.jpg','source' => '1','source_id' => '2','created_at' => '2018-09-04 02:51:12','updated_at' => '2018-09-04 02:51:12'),
       array('id' => '11','type' => '17','name' => 'how-to-toe-the-line-between-friend-and-business-partner1535970118.jpg','source' => '1','source_id' => '3','created_at' => '2018-09-04 02:51:58','updated_at' => '2018-09-04 02:51:58'),

       array('id' => '12','type' => '21','name' => 'image-20210310-052859-11615871049.png','source' => '1','source_id' => '1','created_at' => '2020-06-19 04:39:25','updated_at' => '2021-03-16 10:34:09'),

      array('id' => '13','type' => '28','name' => 'whatsapp.jpg','source' => '1','source_id' => '1','created_at' => '2020-07-21 19:28:39','updated_at' => '2020-07-21 19:28:39'),
       array('id' => '14','type' => '28','name' => 'skype.jpeg','source' => '1','source_id' => '2','created_at' => '2020-07-21 19:28:39','updated_at' => '2020-07-21 19:28:39'),

       array('id' => '15','type' => '29','name' => 'food-1x1617637430.png','source' => '1','source_id' => '1','created_at' => '2021-03-16 10:16:33','updated_at' => '2021-03-16 10:16:33'),
       array('id' => '16','type' => '30','name' => 'servicebannerfood1616249044.jpg','source' => '1','source_id' => '1','created_at' => '2021-03-16 10:16:33','updated_at' => '2021-03-20 19:34:04'),

       array('id' => '17','type' => '7','name' => 'afghan1615988384.jpg','source' => '1','source_id' => '1','created_at' => '2018-09-03 18:19:15','updated_at' => '2021-03-17 19:09:44'),
       array('id' => '18','type' => '7','name' => 'african1615988394.jpg','source' => '1','source_id' => '2','created_at' => '2018-09-03 18:20:09','updated_at' => '2021-03-17 19:09:54'),
       array('id' => '19','type' => '7','name' => 'american1615988404.jpg','source' => '1','source_id' => '3','created_at' => '2018-09-03 18:20:17','updated_at' => '2021-03-17 19:10:04'),
       array('id' => '20','type' => '7','name' => 'arabic1615988413.jpg','source' => '1','source_id' => '4','created_at' => '2018-09-03 18:20:27','updated_at' => '2021-03-17 19:10:13'),
       array('id' => '21','type' => '7','name' => 'begels1615988426.jpg','source' => '1','source_id' => '5','created_at' => '2018-09-03 18:20:36','updated_at' => '2021-03-17 19:10:26'),
       array('id' => '22','type' => '7','name' => 'balti1615988436.jpg','source' => '1','source_id' => '6','created_at' => '2018-09-03 18:20:44','updated_at' => '2021-03-17 19:10:36'),
       array('id' => '23','type' => '7','name' => 'bangladesh1615988460.jpg','source' => '1','source_id' => '7','created_at' => '2018-09-03 18:22:49','updated_at' => '2021-03-17 19:11:00'),
       array('id' => '24','type' => '7','name' => 'bbq1615988473.jpg','source' => '1','source_id' => '8','created_at' => '2018-09-03 18:25:09','updated_at' => '2021-03-17 19:11:13'),
       array('id' => '25','type' => '7','name' => 'breakfast1615988494.jpg','source' => '1','source_id' => '9','created_at' => '2018-09-03 18:25:57','updated_at' => '2021-03-17 19:11:34'),
       array('id' => '26','type' => '7','name' => 'british1615988506.jpg','source' => '1','source_id' => '10','created_at' => '2018-09-03 18:26:46','updated_at' => '2021-03-17 19:11:46'),
       array('id' => '27','type' => '7','name' => 'burger1615988520.jpg','source' => '1','source_id' => '11','created_at' => '2018-09-03 18:27:39','updated_at' => '2021-03-17 19:12:00'),
       array('id' => '28','type' => '7','name' => 'cake1615988531.jpg','source' => '1','source_id' => '12','created_at' => '2018-09-03 18:29:10','updated_at' => '2021-03-17 19:12:11'),
       array('id' => '29','type' => '7','name' => 'caribean1615988548.jpg','source' => '1','source_id' => '13','created_at' => '2018-09-03 18:29:51','updated_at' => '2021-03-17 19:12:28'),
       array('id' => '30','type' => '7','name' => 'chicken1615988581.jpg','source' => '1','source_id' => '14','created_at' => '2018-09-03 18:32:03','updated_at' => '2021-03-17 19:13:01'),
       array('id' => '31','type' => '7','name' => 'chinese1615988593.jpg','source' => '1','source_id' => '15','created_at' => '2018-09-03 18:32:40','updated_at' => '2021-03-17 19:13:13'),
       array('id' => '32','type' => '7','name' => 'curry1615988640.jpg','source' => '1','source_id' => '16','created_at' => '2018-09-03 18:33:41','updated_at' => '2021-03-17 19:14:00'),
       array('id' => '33','type' => '7','name' => 'dessert1615988650.jpg','source' => '1','source_id' => '17','created_at' => '2018-09-03 18:35:01','updated_at' => '2021-03-17 19:14:10'),
       array('id' => '34','type' => '7','name' => 'drinks1615988661.jpg','source' => '1','source_id' => '18','created_at' => '2018-09-03 18:35:41','updated_at' => '2021-03-17 19:14:21'),
       array('id' => '35','type' => '7','name' => 'fish-and-chips1615988674.jpg','source' => '1','source_id' => '19','created_at' => '2018-09-03 18:37:00','updated_at' => '2021-03-17 19:14:34'),
       array('id' => '36','type' => '7','name' => 'fusion1615988693.jpg','source' => '1','source_id' => '20','created_at' => '2018-09-03 18:37:57','updated_at' => '2021-03-17 19:14:53'),
       array('id' => '37','type' => '7','name' => 'gourmet1615988706.jpg','source' => '1','source_id' => '21','created_at' => '2018-09-03 18:40:24','updated_at' => '2021-03-17 19:15:06'),
       array('id' => '38','type' => '7','name' => 'gourmet-burger1615988722.jpg','source' => '1','source_id' => '22','created_at' => '2018-09-03 18:41:20','updated_at' => '2021-03-17 19:15:22'),
       array('id' => '39','type' => '7','name' => 'grill1615988750.jpg','source' => '1','source_id' => '23','created_at' => '2018-09-03 18:42:24','updated_at' => '2021-03-17 19:15:50'),
       array('id' => '40','type' => '7','name' => 'icecream1615988756.jpg','source' => '1','source_id' => '24','created_at' => '2018-09-03 18:42:59','updated_at' => '2021-03-17 19:15:56'),
       array('id' => '41','type' => '7','name' => 'indian1615988762.jpg','source' => '1','source_id' => '25','created_at' => '2018-09-03 18:43:58','updated_at' => '2021-03-17 19:16:02'),
       array('id' => '42','type' => '7','name' => 'indonesian1615988771.jpg','source' => '1','source_id' => '26','created_at' => '2018-09-03 18:45:00','updated_at' => '2021-03-17 19:16:11'),
       array('id' => '43','type' => '7','name' => 'iranian1615988801.jpg','source' => '1','source_id' => '27','created_at' => '2018-09-03 18:45:49','updated_at' => '2021-03-17 19:16:41'),
       array('id' => '44','type' => '7','name' => 'italian1615988806.jpg','source' => '1','source_id' => '28','created_at' => '2018-09-03 18:46:28','updated_at' => '2021-03-17 19:16:46'),
       array('id' => '45','type' => '7','name' => 'jamaican1615988827.jpg','source' => '1','source_id' => '29','created_at' => '2018-09-03 18:47:33','updated_at' => '2021-03-17 19:17:07'),
       array('id' => '46','type' => '7','name' => 'japan1615988832.jpg','source' => '1','source_id' => '30','created_at' => '2018-09-03 18:48:08','updated_at' => '2021-03-17 19:17:12'),

       array('id' => '47','type' => '7','name' => 'kebab1615988840.jpg','source' => '1','source_id' => '31','created_at' => '2020-07-17 05:31:55','updated_at' => '2021-03-17 19:17:20'),
       array('id' => '48','type' => '7','name' => 'korean1615988889.jpg','source' => '1','source_id' => '32','created_at' => '2020-07-18 05:43:21','updated_at' => '2021-03-17 19:18:09'),
       array('id' => '49','type' => '7','name' => 'kosher1615988994.jpg','source' => '1','source_id' => '33','created_at' => '2020-07-18 05:35:31','updated_at' => '2021-03-17 19:19:54'),
       array('id' => '50','type' => '7','name' => 'lebanese1615989000.jpg','source' => '1','source_id' => '34','created_at' => '2021-03-17 19:20:00','updated_at' => '2021-03-17 19:20:00'),
       array('id' => '51','type' => '7','name' => 'lunch1615989007.jpg','source' => '1','source_id' => '35','created_at' => '2021-03-17 19:20:07','updated_at' => '2021-03-17 19:20:07'),
       array('id' => '52','type' => '7','name' => 'mediterranean1615989016.jpg','source' => '1','source_id' => '36','created_at' => '2021-03-17 19:20:16','updated_at' => '2021-03-17 19:20:16'),
       array('id' => '53','type' => '7','name' => 'middle-eastern1615989023.jpg','source' => '1','source_id' => '37','created_at' => '2021-03-17 19:20:23','updated_at' => '2021-03-17 19:20:23'),
       array('id' => '54','type' => '7','name' => 'milkshake1615989029.jpg','source' => '1','source_id' => '38','created_at' => '2020-07-18 05:47:38','updated_at' => '2021-03-17 19:20:29'),
       array('id' => '55','type' => '7','name' => 'nigerian-food1615989035.jpg','source' => '1','source_id' => '39','created_at' => '2020-07-18 05:48:46','updated_at' => '2021-03-17 19:20:35'),
       array('id' => '56','type' => '7','name' => 'noodles1615989046.jpg','source' => '1','source_id' => '40','created_at' => '2021-03-17 19:20:46','updated_at' => '2021-03-17 19:20:46'),
       array('id' => '57','type' => '7','name' => 'oriental1615989052.jpg','source' => '1','source_id' => '41','created_at' => '2020-07-18 05:46:41','updated_at' => '2021-03-17 19:20:52'),
       array('id' => '58','type' => '7','name' => 'pakistani1615989059.jpg','source' => '1','source_id' => '42','created_at' => '2021-03-17 19:20:59','updated_at' => '2021-03-17 19:20:59'),
       array('id' => '59','type' => '7','name' => 'peri-peri1615989081.jpg','source' => '1','source_id' => '43','created_at' => '2020-07-18 05:25:16','updated_at' => '2021-03-17 19:21:21'),
       array('id' => '60','type' => '7','name' => 'persian1615989087.jpg','source' => '1','source_id' => '44','created_at' => '2020-07-18 05:26:38','updated_at' => '2021-03-17 19:21:27'),
       array('id' => '61','type' => '7','name' => 'pizza1615989094.jpg','source' => '1','source_id' => '45','created_at' => '2020-07-18 05:27:58','updated_at' => '2021-03-17 19:21:34'),
       array('id' => '62','type' => '7','name' => 'portuegese1616482352.jpg','source' => '1','source_id' => '46','created_at' => '2021-03-23 12:22:32','updated_at' => '2021-03-23 12:22:32'),
       array('id' => '63','type' => '7','name' => 'southindian11615989117.jpg','source' => '1','source_id' => '47','created_at' => '2020-07-18 05:39:25','updated_at' => '2021-03-17 19:21:57'),
       array('id' => '64','type' => '7','name' => 'steak1615989126.jpg','source' => '1','source_id' => '48','created_at' => '2020-07-18 05:42:44','updated_at' => '2021-03-17 19:22:06'),
       array('id' => '65','type' => '7','name' => 'sushi1615989131.jpg','source' => '1','source_id' => '49','created_at' => '2020-07-18 05:46:26','updated_at' => '2021-03-17 19:22:11'),
        array('id' => '66','type' => '7','name' => 'thai1615989136.jpg','source' => '1','source_id' => '50','created_at' => '2020-07-18 05:28:59','updated_at' => '2021-03-17 19:22:17'),
       array('id' => '67','type' => '7','name' => 'turkish1615989143.jpg','source' => '1','source_id' => '51','created_at' => '2020-07-18 05:38:03','updated_at' => '2021-03-17 19:22:23'),
       array('id' => '68','type' => '7','name' => 'vegan1615989149.jpg','source' => '1','source_id' => '52','created_at' => '2020-07-17 05:08:03','updated_at' => '2021-03-17 19:22:29')
     ));
  }
}
