<?php

use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currency')->delete();
      
        DB::table('currency')->insert([
            ['id' => '1','name' => 'US Dollar','code' => 'USD','symbol' => '&#36;','rate' => '1.000' ,'default_currency' => '1','paypal_currency' => '1'],
            ['id' => '2','name' => 'Pound Sterling','code' => 'GBP','symbol' => '&pound;','rate' => '0.776' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '3','name' => 'Europe','code' => 'EUR','symbol' => '&euro;','rate' => '0.897' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '4','name' => 'Australian Dollar','code' => 'AUD','symbol' => '&#36;','rate' => '1.458' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '5','name' => 'Singapore','code' => 'SGD','symbol' => '&#36;','rate' => '1.362' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '6','name' => 'Swedish Krona','code' => 'SEK','symbol' => 'kr','rate' => '9.653' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '7','name' => 'Danish Krone','code' => 'DKK','symbol' => 'kr','rate' => '6.698' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '8','name' => 'Mexican Peso','code' => 'MXN','symbol' => '$','rate' => '19.124' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '9','name' => 'Brazilian Real','code' => 'BRL','symbol' => 'R$','rate' => '4.118' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '10','name' => 'Malaysian Ringgit','code' => 'MYR','symbol' => 'RM','rate' => '4.185' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '11','name' => 'Philippine Peso','code' => 'PHP','symbol' => 'P','rate' => '51.353' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '12','name' => 'Swiss Franc','code' => 'CHF','symbol' => '&euro;','rate' => '0.986' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '13','name' => 'India','code' => 'INR','symbol' => '&#x20B9;','rate' => '70.993' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '14','name' => 'Argentine Peso','code' => 'ARS','symbol' => '&#36;','rate' => '58.126' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '15','name' => 'Canadian Dollar','code' => 'CAD','symbol' => '&#36;','rate' => '1.313' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '16','name' => 'Chinese Yuan','code' => 'CNY','symbol' => '&#165;','rate' => '7.072' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '17','name' => 'Czech Republic Koruna','code' => 'CZK','symbol' => 'K&#269;','rate' => '22.980' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '18','name' => 'Hong Kong Dollar','code' => 'HKD','symbol' => '&#36;','rate' => '7.842' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '19','name' => 'Hungarian Forint','code' => 'HUF','symbol' => 'Ft','rate' => '296.748' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '20','name' => 'Indonesian Rupiah','code' => 'IDR','symbol' => 'Rp','rate' => '14117.000' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '21','name' => 'Israeli New Sheqel','code' => 'ILS','symbol' => '&#8362;','rate' => '3.543' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '22','name' => 'Japanese Yen','code' => 'JPY','symbol' => '&#165;','rate' => '108.503' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '23','name' => 'South Korean Won','code' => 'KRW','symbol' => '&#8361;','rate' => '1173.905' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '24','name' => 'Norwegian Krone','code' => 'NOK','symbol' => 'kr','rate' => '9.159' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '25','name' => 'New Zealand Dollar','code' => 'NZD','symbol' => '&#36;','rate' => '1.564' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '26','name' => 'Polish Zloty','code' => 'PLN','symbol' => 'z&#322;','rate' => '3.839' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '27','name' => 'Russian Ruble','code' => 'RUB','symbol' => 'p','rate' => '63.800' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '28','name' => 'Thai Baht','code' => 'THB','symbol' => '&#3647;','rate' => '30.272' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '29','name' => 'Turkish Lira','code' => 'TRY','symbol' => '&#8378;','rate' => '5.793' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '30','name' => 'New Taiwan Dollar','code' => 'TWD','symbol' => '&#36;','rate' => '30.563' ,'default_currency' => '0','paypal_currency' => '1'],
            ['id' => '31','name' => 'Vietnamese Dong','code' => 'VND','symbol' => '&#8363;','rate' => '23161.609' ,'default_currency' => '0','paypal_currency' => '0'],
            ['id' => '32','name' => 'South African Rand','code' => 'ZAR','symbol' => 'R','rate' => '14.799' ,'default_currency' => '0','paypal_currency' => '0'],
        ]);
    }
}