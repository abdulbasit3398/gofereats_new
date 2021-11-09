<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */


Route::get('all_load','HomeController@singleLoad');
Route::get('update_time',function(){
	\DB::table('order')->where('id',request()->id)->update(['schedule_time'=>request()->time]);
});

Route::get('order_load','HomeController@orderLoad');

Route::get('exchange','CronController@currency_cron');
Route::get('paypal_payout','CronController@updatePaypalPayouts');
Route::post('get_menu_items','HomeController@get_menu_items')->name('get_menu_items');

//home page	
// Route::get('/', 'HomeController@home')->name('home')->middleware('installer','locale');
Route::group(['middleware' => ['installer','locale','clear_cache']], function () {
	Route::post('set_session', 'HomeController@set_session');
	Route::get('about/{page}', 'HomeController@static_page')->name('page');

	Route::get('/', 'HomeController@newHome')->name('newhome')->middleware('installer','locale');
	Route::get('feeds', 'HomeController@feeds')->name('feeds');
	Route::get('newdetails/{store_id}', 'HomeController@newDetails')->name('newdetails');
	Route::post('category_based_store', 'HomeController@categoryStore')->name('categorystore');
	Route::post('set_service_type','HomeController@setServiceType')->name('set_service_type');

	// search page
	Route::match(array('GET', 'POST'), '/stores', 'SearchController@index')->name('search');
	Route::post('store_location', 'SearchController@store_location_data')->name('store_location');
	Route::post('/search_result', 'SearchController@search_result')->name('search_result');
	Route::post('/search_data', 'SearchController@search_data')->name('search_data');
	Route::post('schedule_store', 'SearchController@schedule_store')->name('schedule_store');

	// store details
	Route::match(array('GET', 'POST'), '/details/{store_id}', 'UsersController@detail')->name('details');
	Route::post('item_details', 'UsersController@menu_item_detail')->name('item_details');
	Route::post('category_details', 'UsersController@menu_category_details')->name('category_details');
	Route::post('get_category_item', 'UsersController@get_category_item')->name('get_category_item');
	Route::post('orders_store', 'UsersController@orders_store')->name('orders_store');
	Route::post('orders_remove', 'UsersController@orders_remove')->name('orders_remove');
	Route::post('orders_change', 'UsersController@orders_change')->name('orders_change');
	Route::post('session_clear_data', 'UsersController@session_clear_data')->name('session_clear_data');

	Route::post('add_wallet_amount_paypal','UserController@addWalletAmountPaypal')->name('add_wallet_amount_paypal');
	// Route::post('add_wallet_amount_stripe','UserController@addWalletAmountStripe')->name('add_wallet_amount_stripe');
	Route::post('add_wallet_amount','UserController@addWalletAmount')->name('add_wallet_amount');

	Route::post('add_wallet_stripe','UserController@addWalletStripe')->name('add_wallet_stripe');


	Route::post('add_wb_wallet','UserController@addWebWallet')->name('add_wb_wallet');

	// payment page
	Route::get('/checkout', 'PaymentController@checkout')->name('checkout');
	Route::post('add_cart', 'PaymentController@add_cart')->name('add_cart');
	Route::get('order_track', 'PaymentController@order_track')->name('order_track');
	Route::post('card_details', 'PaymentController@add_card_details')->name('card_details');
	Route::post('paypal_currency_conversion', 'PaymentController@paypal_currency_conversion')->name('paypal_currency_conversion');
	Route::post('place_order_details', 'PaymentController@place_order_details')->name('place_order_details');
	Route::post('place_order', 'PaymentController@place_order')->name('place_order');
	Route::post('location_check', 'UsersController@location_check')->name('location_check');
	Route::get('location_not_found', 'UsersController@location_not_found')->name('location_not_found');
	Route::post('cancel_order', 'PaymentController@cancel_order')->name('cancel_order');
	Route::post('order_invoice', 'UsersController@order_invoice')->name('order_invoice');
	Route::get('set_default_promo/{code}','UsersController@setDefaultPromo');
	Route::get('remove_promo_code_user/{code}', 'UsersController@removeUserPromo');
	Route::get('/privacy_policy', function () {
		return view('privacy_policy');
	});

	Route::get('help', 'HelpController@help')->name('help');
	Route::get('help/{page}', 'HelpController@help')->where(['page' => 'user|restaurant|driver'])->name('help_page');
	Route::get('help/{page}/{category_id}', 'HelpController@help_category')->where(['category_id' => '[0-9]+', 'page' => 'user|restaurant|driver'])->name('help_category');
	Route::get('help/{page}/{category_id}/{subcategory_id}', 'HelpController@help_subcategory')->where(['category_id' => '[0-9]+', 'page' => 'user|restaurant|driver'])->name('help_subcategory');
	Route::get('help/{page}/{category_id}/{subcategory_id}/{question_id}', 'HelpController@help_question')->where(['category_id' => '[0-9]+', 'question_id' => '[0-9]+', 'page' => 'user|restaurant|driver'])->name('help_question');
	Route::get('ajax_help_search', 'HelpController@ajax_help_search')->name('ajax_help_search');
	Route::view('/help_category', 'help_category');
	Route::view('/help_detail', 'help_detail');
	Route::view('/order_rating','order_rating');


	// login page
	Route::get('/login', 'HomeController@login')->name('login');
	Route::post('/authenticate', 'UserController@authenticate')->name('authenticate');

	Route::group(['middleware' => 'auth:web'], function () {
		Route::get('/orders', 'UsersController@order_history')->name('orders');
		Route::get('/logout', 'UserController@logout')->name('logout');
		Route::get('/profile', 'UserController@user_profile')->name('profile');
		Route::get('/user_payment', 'UserController@user_payment')->name('user_payment');
		Route::post('user_details_store', 'UserController@user_details_store')->name('user_details_store');
		Route::post('add_promo_code_data', 'UsersController@add_promo_code')->name('add_promo_code_data');
		Route::post('add_promo_code_order', 'UsersController@addPromoOrder')->name('add_promo_code_order');
		Route::post('remove_promo_code_data', 'UsersController@removePromoCode')->name('remove_promo_code_data');
		Route::post('add_driver_tips', 'UsersController@addDriverTips')->name('add_driver_tips');
		Route::post('remove_driver_tips', 'UsersController@removeDriverTips')->name('remove_driver_tips');
	});

	Route::post('password_change', 'HomeController@password_change')->name('password_change');

	Route::group(['middleware' => ['guest:web', 'clear_cache']], function () {
		Route::get('/signup', 'HomeController@signup')->name('signup');
		Route::get('/signup_confirm', 'HomeController@signup_confirm')->name('signup2');
		Route::post('signup_data', 'HomeController@store_signup_data')->name('signup_data');
		Route::post('store_signup_data', 'HomeController@store_user_data')->name('store_signup_data');
		Route::match(array('POST', 'GET'), 'forgot_password', 'HomeController@forgot_password')->name('forgot_password');
		Route::match(array('POST', 'GET'), 'otp_confirm', 'HomeController@otp_confirm')->name('otp_confirm');
		Route::match(array('POST', 'GET'), 'reset_password', 'HomeController@reset_password')->name('reset_password');
	});
});

Route::get('googleAuthenticate', 'UsersController@googleAuthenticate');
Route::post('apple_callback', 'UserController@appleCallback');
Route::get('facebook_login', 'UserController@facebook_login');
Route::get('facebookAuthenticate', 'UserController@facebookAuthenticate');

// Route::get('run_command','HomeController@artisanCommand');
// 

Route::get('session_phone_code/{id}','HomeController@setPhoneCode');
Route::get('clear__l--log', 'HomeController@clearLog');
Route::get('show__l--log', 'HomeController@showLog');
Route::get('update__env--content', 'HomeController@updateEnv');

Route::get('clear_cache', function() {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('view:clear');
	Artisan::call('route:clear');
	return 'Cache is cleared';
});

Route::get('db_backup', function(){
	if(env('APP_DEBUG')==true){
			$filename = "backup-" . time() . ".sql";
            $fileurl =  public_path() . "/" . $filename;
            exec('mysqldump -u '. env('DB_USERNAME') .' '. env('DB_DATABASE') .' > '.$filename);
            if (file_exists($fileurl)) {
                return \Response::download($fileurl, $filename, array('Content-Type: application/octet-stream','Content-Length: '. filesize($fileurl)))->deleteFileAfterSend(true);;
            } else {
                return ['status'=>'zip file does not exist'];
            }
        }
});


Route::get('query_update/{type}', 'HomeController@query_update');