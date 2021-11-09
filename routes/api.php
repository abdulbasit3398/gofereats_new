<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get(
	'/user', function (Request $request) {
		return $request->user();
	}
);
 
Route::match(array('GET', 'POST'),'upload_image', 'UserController@upload_image');

Route::get('');
// TokenAuthController
Route::get('language','TokenAuthController@language');
Route::match(array('GET', 'POST'),'register', 'TokenAuthController@register');
Route::get('number_validation', 'TokenAuthController@number_validation');
Route::get('email_validation', 'TokenAuthController@emailValidation');
Route::match(array('GET', 'POST'),'login', 'TokenAuthController@login');
Route::get('forgot_password', 'TokenAuthController@forgot_password');
Route::get('reset_password', 'TokenAuthController@reset_password');
Route::get('search_drivers', 'DriverController@search_drivers');
Route::get('social_signup', 'TokenAuthController@socialSignup');

Route::get('api_language_content','TokenAuthController@apiContent');

Route::match(array('GET', 'POST'),'apple_callback', 'TokenAuthController@apple_callback');

// cron

Route::get('remain_schedule_order', 'StoreController@remainScheduleOrder');
Route::get('beforeseven', 'StoreController@beforeSevenMin');
Route::get('cron_refund', 'PaymentController@cron_refund');

Route::match(array('GET', 'POST'),'ios','UserController@ios');
Route::match(array('GET', 'POST'),'add_user_review', 'UserController@add_user_review');
Route::match(array('GET', 'POST'),'add_to_cart', 'UserController@add_to_cart');
Route::match(array('GET', 'POST'),'common_data', 'TokenAuthController@common_data');
Route::get('stripe_supported_country_list', 'TokenAuthController@country_list');

Route::match(array('GET', 'POST'),'service_type', 'TokenAuthController@serviceType');

// without Login 
if(!request()->token) {
	Route::group(['middleware' => ['without_login']], function () {
	Route::get('home', 'UserController@home');
	Route::get('get_store_details', 'UserController@get_store_details');
	Route::get('get_menu_item_addon','UserController@get_menu_item_addon');
	Route::get('categories', 'UserController@categories');
	Route::get('search', 'UserController@search');
	Route::get('filter', 'UserController@filter');
	Route::get('info_window', 'UserController@info_window');
	});
	Route::get('apple_service_id','TokenAuthController@appleServiceId');
}
else {
	Route::group(['middleware' => ['jwt_auth']], function () {

	Route::get('home', 'UserController@home');
	Route::get('get_store_details', 'UserController@get_store_details');
	Route::get('get_menu_item_addon','UserController@get_menu_item_addon');
	Route::get('categories', 'UserController@categories');
	Route::get('search', 'UserController@search');
	Route::get('filter', 'UserController@filter');
	Route::get('info_window', 'UserController@info_window');
	});
}

Route::get('new_store_details', 'UserController@new_store_details');
// for Login check
Route::group(['middleware' => ['jwt_auth']], function () {

	Route::get('get_currency_list','TokenAuthController@GetCurrencyLists');
	Route::get('logout', 'TokenAuthController@logout');
	Route::get('change_mobile', 'TokenAuthController@change_mobile');
	Route::get('get_profile', 'TokenAuthController@get_profile');
	Route::get('update_device_id', 'TokenAuthController@update_device_id');
	Route::get('update_currency', 'TokenAuthController@UpdateCurrency');
	Route::get('get_cancel_reason', 'StoreController@get_cancel_reason');

	//UserController
	Route::get('get_location', 'UserController@get_location');
	Route::get('save_location', 'UserController@saveLocation');
	Route::get('default_location', 'UserController@defaultLocation');
	Route::get('remove_location', 'UserController@remove_location');
	Route::get('update_profile', 'UserController@update_profile');

	Route::get('more_store', 'UserController@more_store');

	Route::get('add_promo_code', 'UserController@add_promo_code');
	Route::get('get_promo_details', 'UserController@get_promo_details');
  	// Route::get('remove_promo_code', 'UserController@removePromoCode');
	Route::get('add_wish_list', 'UserController@add_wish_list');
	
	Route::get('view_cart', 'UserController@view_cart');
	Route::get('clear_cart', 'UserController@clear_cart');
	Route::get('pending_order_list', 'UserController@pending_order_list');
	Route::get('upcoming_order_list', 'UserController@upcoming_order_list');
	Route::get('clear_all_cart', 'UserController@clear_all_cart');
	Route::get('add_card_details', 'UserController@add_card_details');
	Route::get('get_card_details', 'UserController@get_card_details');
	Route::get('get_payment_methods', 'UserController@getPaymentMethods');
	Route::get('cancel_order', 'UserController@cancel_order');
	Route::get('user_review', 'UserController@user_review');
	Route::get('add_wallet_amount', 'UserController@add_wallet_amount');
	Route::get('wishlist', 'UserController@wishlist');
	
	//paymentcontroller
	Route::get('place_order', 'PaymentController@place_order');
	Route::get('refund', 'PaymentController@refund');
	Route::match(['GET', 'POST'],'currency_conversion', 'TokenAuthController@currency_conversion');

	// Store API
	Route::get('orders', 'StoreController@orders');
	Route::get('accept_order', 'StoreController@accept_order');
	Route::get('store_order_details', 'StoreController@order_details');
	Route::get('food_ready', 'StoreController@food_ready');
	Route::get('store_cancel_order', 'StoreController@cancel_order');
	Route::get('delay_order', 'StoreController@delay_order');
	Route::get('store_menu', 'StoreController@menu');
	Route::get('store_menu_item', 'StoreController@menu_item');
	Route::get('toggle_visible', 'StoreController@toggle_visible');
	Route::get('order_history', 'StoreController@order_history');
	Route::get('store_availabilty', 'StoreController@store_availabilty');
	Route::get('review_store_to_driver', 'StoreController@review_store_to_driver');
	Route::get('complete_takeaway_order', 'StoreController@completeTakeawayOrder');
	Route::get('pay_store_to_admin', 'StoreController@payStoreToAdmin');

	// Driver API
	Route::get('vehicle_details', 'DriverController@vehicle_details');
	Route::post('document_upload', 'DriverController@document_upload')->middleware('jwt_driver:false');
	Route::group(['middleware' => 'jwt_driver'], function () {
		Route::get('check_status', 'DriverController@check_status');
		Route::get('update_driver_location', 'DriverController@update_driver_location');
		Route::get('get_driver_profile', 'DriverController@get_driver_profile');
		Route::get('update_driver_profile', 'DriverController@update_driver_profile');
		Route::get('accept_request', 'DriverController@accept_request');
		Route::get('cancel_request', 'DriverController@cancel_request');
		Route::get('driver_order_details', 'DriverController@driver_order_details');
		Route::get('dropoff_data', 'DriverController@dropoff_data');
		Route::get('pickup_data', 'DriverController@pickup_data');
		Route::post('add_payout_perference', 'DriverController@add_payout_perference');
		Route::get('payout_details', 'DriverController@payout_details');
		Route::get('payout_changes', 'DriverController@payout_changes');
		Route::get('confirm_order_delivery', 'DriverController@confirm_order_delivery');
		Route::get('start_order_delivery', 'DriverController@start_order_delivery');
		Route::get('drop_off_delivery', 'DriverController@drop_off_delivery');
		Route::post('complete_order_delivery', 'DriverController@complete_order_delivery');
		Route::get('cancel_order_delivery', 'DriverController@cancel_order_delivery');
		Route::get('get_owe_amount', 'DriverController@get_owe_amount');
		Route::get('pay_to_admin', 'DriverController@pay_to_admin');
		Route::get('earning_list', 'DriverController@earning_list');
		Route::get('past_delivery', 'DriverController@past_delivery');
		Route::get('today_delivery', 'DriverController@today_delivery');
		Route::get('particular_order', 'DriverController@particular_order');
		Route::get('weekly_trip', 'DriverController@weekly_trip');
		Route::get('weekly_statement', 'DriverController@weekly_statement');
		Route::get('daily_statement', 'DriverController@daily_statement');
		Route::get('static_map', 'DriverController@static_map');
		Route::get('country_list', 'DriverController@country_list');
		Route::get('driver_accepted_orders', 'DriverController@driver_accept_order');
		Route::get('driver_received_order', 'DriverController@driver_received_order');
	});
	
	Route::get('get_payout_list','DriverController@getPayoutPreference');
});
