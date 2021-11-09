<?php

use Illuminate\Database\Seeder;

class SiteSettingTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('site_setting')->delete();

		DB::table('site_setting')->insert(array(
			array('name' => 'site_name', 'value' => 'gofereats'),
			array('name' => 'site_url', 'value' => ''),
			array('name' => 'site_date_format', 'value' => 'd-m-Y'),
			array('name' => 'site_time_format', 'value' => '12'),
			array('name' => 'default_currency', 'value' => 'USD'),
			array('name' => 'default_language', 'value' => 'en'),
			array('name' => 'version', 'value' => '1.6'),
			array('name' => 'join_us_facebook', 'value' => 'https://www.facebook.com/Trioangle.Technologies/'),
			array('name' => 'join_us_twitter', 'value' => 'https://twitter.com/trioangle'),
			array('name' => 'join_us_youtube', 'value' => 'https://www.youtube.com/channel/UC2EWcEd5dpvGmBh-H4TQ0wg'),
			array('name' => 'user_apple_link', 'value' => 'https://apps.apple.com/in/app/gofereats-user/id1521997749'),
			array('name' => 'store_apple_link', 'value' => 'https://apps.apple.com/in/app/gofereats-store/id1522002173'),
			array('name' => 'driver_apple_link', 'value' => 'https://apps.apple.com/in/app/gofereats-driver/id1522003712'),
			array('name' => 'user_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereatsuser'),
			array('name' => 'store_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereatsstore'),
			array('name' => 'driver_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereatsdriver'),
			array('name' => 'google_api_key', 'value' => 'AIzaSyAVop-_ZxT0Vc0yakoTIMWkgNnRg8K44Hg'),
			array('name' => 'stripe_publish_key', 'value' => 'pk_test_51HJgCOLVTjTr3Be9R7XL5H4IBzcCP6DdCvWgWaADcmlrzecM6EerhDqAXWH7W157MhqhZ73wyJzXvEhHCAKYW81b00Zk4YVMec'),
			array('name' => 'stripe_secret_key', 'value' => 'sk_test_51HJgCOLVTjTr3Be9hestCVoihF6x4dtx4L7TBrsyk8qbBAOKjdiqd800FOHLzvC4usWleVww40LRkWywMJSu7hvm00R4JZ4bpa'),
			array('name' => 'stripe_api_version', 'value' => '2019-08-14'),
			array('name' => 'twillo_id', 'value' => 'ACf64f4d6b2a55e7c56b592b6dec3919ae'),
			array('name' => 'twillo_token', 'value' => 'bc887b0e7159ab5cb0945c3fc59b345a'),
			array('name' => 'twillo_from_number', 'value' => '+15594238858'),
			array('name' => 'delivery_fee_type', 'value' => '1'),
			array('name' => 'delivery_fee', 'value' => '10'),
			array('name' => 'booking_fee', 'value' => '10'),
			array('name' => 'store_commision_fee', 'value' => '10'),
			array('name' => 'driver_commision_fee', 'value' => '10'),
			array('name' => 'pickup_fare', 'value' => '15'),
			array('name' => 'drop_fare', 'value' => '20'),
			array('name' => 'distance_fare', 'value' => '3'),
			array('name' => 'email_driver', 'value' => 'smtp'),
			array('name' => 'email_host', 'value' => 'smtp.gmail.com'),
			array('name' => 'email_port', 'value' => '25'),
			array('name' => 'email_to_address', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_from_address', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_from_name', 'value' => 'gofereats'),
			array('name' => 'email_encryption', 'value' => 'tls'),
			array('name' => 'email_user_name', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_password', 'value' => 'hismljhblilxdusd'),
			array('name' => 'email_domain', 'value' => 'sandboxcc51fc42882e46ccbffd90316d4731e7.mailgun.org'),
			array('name' => 'email_secret', 'value' => 'key-3160b23116332e595b861f60d77fa720'),
			array('name' => 'fcm_server_key', 'value' => 'AIzaSyDOQRzxCTZu-u4iItc2d60hafNuOsTXBvU'),
			array('name' => 'fcm_sender_id', 'value' => '387444873632'),
			array('name' => 'site_support_phone', 'value' => '1800-00-2568'),
			array('name' => 'store_km', 'value' => '10'),
			array('name' => 'driver_km', 'value' => '10'),
			array('name' => 'admin_prefix', 'value' => 'admin'),
			// array('name' => 'site_translation_name', 'value' => 'gofereats Arabic'),
			array('name' => 'locale', 'value' => ''),
			// array('name' => 'site_pt_translation', 'value' => 'gofereats Portugeues'),
			array('name' => 'analystics', 'value' => ''),
			array('name' => 'defaulty_curreny_name', 'value' => 'USD Currenncy'),
			array('name' => 'defaulty_curreny_symbol', 'value' => '$'),
			array('name' => 'google_server_key', 'value' => 'AIzaSyBo67LSkcBL1C-RZ8fKzPNOwG7tojMqRGg'),
			array('name' => 'paypal_currency_code', 'value' => 'USD'),
			array('name' => 'paypal_access_token', 'value' => 'access_token$sandbox$rcbzjf2w9qzxxvjg$bd89a3fd65204321d9dc03c61cf0cba2'),

			array('name' => 'paypal_mode', 'value' => 'sandbox'),
			array('name' => 'paypal_client', 'value' => 'AfhcISbOi1x1_z5KO2jNh3yE4YprHsJjM8VSFBSvwMj6QbVU422DPB6whzmGobCO4f5fLqhKaKYcxi4n'),
			array('name' => 'paypal_secret', 'value' => 'EHWQdqAIxqa8QGHDf9fruYb7eHOwJctPvNvSdhGbTTgl7QnDfhbxEyDp7M8S5wKRpMKl0UrwxhDiXZSw'),
			array('name' => 'payment_methods', 'value' => 'Stripe,Paypal,Cash,Wallet'),
			array('name' => 'apple_key_id', 'value' => 'CD36G9CQ5M'),
			array('name' => 'apple_team_id', 'value' => 'W89HL6566S'),
			array('name' => 'apple_service_id', 'value' => 'com.trioangle.gofereatsuser.service'),
			array('name' => 'apple_key_file', 'value' => ' '),
			array('name' => 'google_client_id', 'value' => '942638704070-3kcv43kirgu1mh7i8c3urrefglnklcbv.apps.googleusercontent.com'),
			array('name' => 'google_client_secret', 'value' => 'yxTjdMVxFpRVd0fsf0aXXKSe'),
			array('name' => 'facebook_client_id', 'value' => '767320630553846'),
			array('name' => 'facebook_client_secret', 'value' => '12e7181be59dd19c4b702f9d41851824'),
			array('name' => 'payout_methods', 'value' => 'Paypal,Stripe,BankTransfer'),
			array('name' => 'database_url', 'value' => 'https://goferdeliverall.firebaseio.com/'),
			array('name' => 'service_account', 'value' => '/resources/credentials/service_account.json'),
			array('name' => 'number_of_delivery', 'value' => '2'),
			array('name' => 'delivery_radius', 'value' => '0.1'),
			array('name' => 'preperation_time_interval', 'value' => '1'),
			array('name' => 'multiple_delivery', 'value' => 'No'),
			array('name' => 'otp_verification', 'value' => 'Yes'),
			array('name' => 'facebook_login', 'value' => 'Yes'),
			array('name' => 'google_login', 'value' => 'Yes'),
			array('name' => 'apple_login', 'value' => 'Yes'),
			array('name' => 'maintenance_mode', 'value' => 'No'),
			array('name' => 'force_update', 'value' => 'No'),
		));
	}
}
