<?php

use App\Models\Country;
use App\Models\Cuisine;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\DriverOweAmount;
use App\Models\File;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\MenuTime;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\OrderItemModifierItem;
use App\Models\OrderItemModifier;
use App\Models\Penality;
use App\Models\PenalityDetails;
use App\Models\Store;
use App\Models\StoreOffer;
use App\Models\StoreTime;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UsersPromoCode;
use App\Models\Wallet;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Mail\ForgotEmail;
use App\Models\CurrencyConversion;
use App\Models\MenuItemModifierItem;
use App\Models\StoreOweAmount;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use App\Models\ServiceType;
use App\Models\UserPaymentMethod;
use App\Models\Payment;


Class helper {
	use CurrencyConversion;

	function itemConvert($from,$to,$price) {
		return $this->currency_convert($from,$to,$price);
	}
}


/**
 * Currency Convert
 *
 * @param int $from   Currency Code From
 * @param int $to     Currency Code To
 * @param int $price  Price Amount
 * @return int Converted amount
 */
if (!function_exists('currencyConvert')) {
	function currencyConvert($from, $to, $price = 0)
	{
        $price = str_replace(',', '', $price);
		$price = floatval($price);
		
		if($from == $to) {
            return number_format($price, 2, '.', '');
        }

        if($price == 0) {
        	return number_format(0, 2, '.', '');
        }
       
        $rate = Currency::whereCode($from)->first()->rate;
        $session_rate = Currency::whereCode($to)->first()->rate;
        $usd_amount = $price / $rate;
       
        return number_format($usd_amount * $session_rate, 2, '.', '');
	}
}


if (!function_exists('store_images')) {

	/**
	 * Fetch store images from File
	 *
	 * @param  int    $source_id Store Id
	 * @param  string $type      Type of the image
	 * @return string               file name
	 */
	function store_images($source_id, $type) {
		$image = File::where('type', $type)->where('source_id', $source_id)->first();

		if ($image) {
			return $image->image_name;
		}
		return getEmptyStoreImage();
	}
}

if (!function_exists('sample_image')) {
	/**
	 * Get sample image
	 */
	function sample_image() {
		return url('/images/sample.png');
	}
}

if (!function_exists('day_name')) {

	/**
	 * Get array of day names
	 *
	 * @param  string $key Key of the day
	 * @return string      Name of the week day
	 */
	function day_name($key = '') {

		$arrayName = array('1' => trans('admin_messages.monday'), '2' => trans('admin_messages.tuesday'), '3' => trans('admin_messages.wednesday'), '4' => trans('admin_messages.thursday'), '5' => trans('admin_messages.friday'), '6' => trans('admin_messages.saturday'), '7' => trans('admin_messages.sunday'));
		if ($key == '') {
			return $arrayName;
		}
		
		return $arrayName[$key];
	}
}

if (!function_exists('static_pages')) {
	function static_pages($key = '') {
		$data = resolve('static_page');
		if (isset($data[$key])) {
			return $data[$key];
		}
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key
 * The searched value
 *
 * @return if key found, return particular value of key. Otherwise return full array.
 */
if (!function_exists('site_setting')) {

	function site_setting($key = '', $value = '')
	{
		$setting = resolve('site_setting');
		//site_name translation process
		if($key == 'site_name') {
			$getLocale = App::getLocale();
			
			if($getLocale == 'ar') {
				if(!empty($setting['site_translation_name'])){
					$setting[$key] = $setting['site_translation_name'];	
				}
			}
			if($getLocale == 'pt') {
				if(!empty($setting['site_pt_translation'])){
					$setting[$key] = $setting['site_pt_translation'];	
				}
			}
		}
		
		if ($key != '') {
			if ($value == '') {
				return $setting[$key];
			}

			$file = File::where('type', 1)->where('source_id', $value)->first();
			if ($file) {
				$url = url('/').'/';
				if(App::runningInConsole()) {
					$url = site_setting('site_url');
				}
				return $url.$file->site_image_url;
			}
		}
		return $setting;
	}
}

if (!function_exists('convertPHPToMomentFormat')) {

	/*
	    * Matches each symbol of PHP date format standard
	    * with jQuery equivalent codeword
	    * @author Tristan Jahier
	*/
	function convertPHPToMomentFormat($format) {
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		];
		$momentFormat = strtr($format, $replacements);
		return $momentFormat;
	}
}

if (!function_exists('convertPHPToJqueryUIFormat')) {

	/*
	 * Matches each symbol of PHP date format standard
	 * with jQuery equivalent codeword
	 * @author Tristan Jahier
	 */
	function convertPHPToJqueryUIFormat($php_format)
	{
	    $SYMBOLS_MATCHING = array(
	        // Day
	        'd' => 'dd',
	        'D' => 'D',
	        'j' => 'd',
	        'l' => 'DD',
	        'N' => '',
	        'S' => '',
	        'w' => '',
	        'z' => 'o',
	        // Week
	        'W' => '',
	        // Month
	        'F' => 'MM',
	        'm' => 'mm',
	        'M' => 'M',
	        'n' => 'm',
	        't' => '',
	        // Year
	        'L' => '',
	        'o' => '',
	        'Y' => 'yy',
	        'y' => 'y',
	        // Time
	        'a' => '',
	        'A' => '',
	        'B' => '',
	        'g' => '',
	        'G' => '',
	        'h' => '',
	        'H' => '',
	        'i' => '',
	        's' => '',
	        'u' => ''
	    );
	    $jqueryui_format = "";
	    $escaping = false;
	    for($i = 0; $i < strlen($php_format); $i++)
	    {
	        $char = $php_format[$i];
	        if($char === '\\') // PHP date format escaping character
	        {
	            $i++;
	            if($escaping) $jqueryui_format .= $php_format[$i];
	            else $jqueryui_format .= '\'' . $php_format[$i];
	            $escaping = true;
	        }
	        else
	        {
	            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
	            if(isset($SYMBOLS_MATCHING[$char]))
	                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
	            else
	                $jqueryui_format .= $char;
	        }
	    }
	    return $jqueryui_format;
	}
}

/**
 * Getting driving distance
 *
 * @param  string $lat1  Start point latitude
 * @param  string $lat2  Start point longitude
 * @param  string $long1 End point latitude
 * @param  string $long2 End point longitude
 * @return array        Array of status, distance, time
 */
if (!function_exists('get_driving_distance')) {
	function get_driving_distance($lat1, $lat2, $long1, $long2)
	{

		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL&key=" . site_setting('google_server_key');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);

		if ($response_a['status'] == "REQUEST_DENIED" || $response_a['status'] == "OVER_QUERY_LIMIT") {
			return array('status' => "fail", 'msg' => $response_a['error_message'], 'time' => '0', 'distance' => "0");
		}
		elseif ($response_a['status'] == "OK" && $response_a['rows'][0]['elements'][0]['status'] != "ZERO_RESULTS") {
			try {
				$dist_find = $response_a['rows'][0]['elements'][0]['distance']['value'];
				$time_find = $response_a['rows'][0]['elements'][0]['duration']['value'];

				$dist = $dist_find != '' ? $dist_find : '0';
				$time = $time_find != '' ? $time_find : '0';

				return array('status' => 'success', 'distance' => $dist, 'time' => (int) $time);
			}
			catch(\Exception $e) {
				return array('status' => "fail", 'msg' => $e->getMessage(), 'time' => '0', 'distance' => "0");
			}
		}
		else {
			return array('status' => 'success', 'distance' => "1", 'time' => "1");
		}
	}
}

if (!function_exists('getSecondsFromTime')) {

	/**
	 * Checks if a value exists in an array in a case-insensitive manner
	 *
	 * @param string $key
	 * The searched value
	 *
	 * @return if key found, return particular value of key. Otherwise return full array.
	 */
	function getSecondsFromTime($time = '') {
		sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
		$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;

		return $time_seconds;
	}
}

if (!function_exists('getTimeFromSeconds')) {

	/**
	 * Checks if a value exists in an array in a case-insensitive manner
	 *
	 * @param string $key
	 * The searched value
	 *
	 * @return if key found, return particular value of key. Otherwise return full array.
	 */
	function getTimeFromSeconds($seconds = '') {

		$hours = floor($seconds / 3600);
		$mins = floor($seconds / 60 % 60);
		$secs = floor($seconds % 60);

		$timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

		return $timeFormat;
	}
}

if (!function_exists('getEmptyUserImageUrl')) {

	/**
	 * Checks if a value exists in an array in a case-insensitive manner
	 *
	 * @param string $key
	 * The searched value
	 *
	 * @return if key found, return particular value of key. Otherwise return full array.
	 */
	function getEmptyUserImageUrl() {
		return url('images/user.png');
	}
}

//Restaurnt Default images

if (!function_exists('getEmptyStoreImage')) {

	/**
	 * Checks if a value exists in an array in a case-insensitive manner
	 *
	 * @param string $key
	 * The searched value
	 *
	 * @return if key found, return particular value of key. Otherwise return full array.
	 */
	function getEmptyStoreImage() {
		return url('images/default-store.jpg');
	}
}

/**
	 * Send OTP
	 *
	 * @param integer $country_code
	 * @param integer $mobile_number
	 * @return Array $response
	 */
	function sendOtp($mobile_number,$message)
	{
        $sms_gateway = resolve("App\Contracts\SMSInterface");
        $response = $sms_gateway->send('+'.$mobile_number,$message);
		return array('status' => $response['status'], 'message' => $response['message']);
	}



/**
	 * Convert Given Array To Object
	 * 
	 * @return Object
	 */
	if (!function_exists('arrayToObject')) {
		function arrayToObject($arr)
		{
			$arr = Arr::wrap($arr);
			return json_decode(json_encode($arr));
		}
	}

	
/**
 * custom sms
 *
 * @return success or fail
 */
if (!function_exists('send_text_message')) {
	function send_text_message($to, $message)
	{
		$url = 'https://rest.nexmo.com/sms/json?' . http_build_query([
			'api_key' 		=> site_setting('nexmo_key'),
			'api_secret' 	=> site_setting('nexmo_secret_key'),
			'to' 			=> $to,
			'from' 			=> site_setting('nexmo_from_number'),
			'text' 			=> $message,
			'type' 			=> 'unicode'
		]);

		$response = @file_get_contents($url);

		$response_data = json_decode($response, true);
		$status = 'Failed';
		$status_message = trans('messages.errors.internal_server_error');

		if (@$response_data['messages']) {
			foreach ($response_data['messages'] as $message) {
				if ($message['status'] == 0) {
					$status = 'Success';
					$status_message = 'Success';
				} else {
					$status = 'Failed';
				if($message['error-text'] == 'Non White-listed Destination - rejected' || $message['error-text'] == 'Quota Exceeded - rejected')
					$status_message = trans('messages.errors.'.$message['error-text']);
				else
					$status_message = $message['error-text'];
				}
			}
		}
		return array('status' => $status, 'message' => $status_message);
	}
}

//random key generation

if (!function_exists('random_num')) {
	function random_num($size) {
		$alpha_key = '';
		$keys = range('A', 'Z');

		for ($i = 0; $i < 2; $i++) {
			$alpha_key .= $keys[array_rand($keys)];
		}

		$length = $size - 2;

		$key = '';
		$keys = range(0, 9);

		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}

		return $alpha_key . $key;
	}
}

if (!function_exists('get_current_login_user')) {
	function get_current_login_user() {
		if (request()->route()->getPrefix() == "admin") {
			if (auth()->guard('admin')->check()) {
				return 'admin';
			}
		} elseif (request()->route()->getPrefix() == "restaurant") {
			if (auth()->guard('restaurant')->check()) {
				return 'restaurant';
			}
		} elseif (auth()->guard('web')->check()) {
			return 'web';
		} elseif (auth()->guard('driver')->check()) {
			return 'driver';
		}
	}
}

if (!function_exists('get_current_root')) {
	function get_current_root() {
		if (request()->route()->getPrefix() == "admin") {
			return 'admin';
		} elseif (request()->route()->getPrefix() == "restaurant") {
			return 'restaurant';
		} elseif (request()->route()->getPrefix() == "api") {
			return 'api';
		} else {
			return 'web';
		}

	}
}

if (!function_exists('check_current_root')) {
	function check_current_root() {
		if (Request::segment(1) == "admin") {
			return 'admin';
		} elseif (Request::segment(1) == "restaurant") {
			return 'restaurant';
		} elseif (Request::segment(1) == "api") {
			return 'api';
		} 
		elseif (Request::segment(1) == "driver") {
			return 'driver';
		}
		else {
			return 'web';
		}

	}
}

if (!function_exists('is_user')) {
	function is_user() {
		return (get_current_login_user() == 'web');
	}
}

if (!function_exists('current_page')) {
	function current_page() {
		if (request()->route()->getPrefix() == '') {
			return 'user';
		} else {
			return request()->route()->getPrefix();
		}

	}
}

if (!function_exists('home_page_link')) {
	function home_page_link() {
		if (request()->route()->getPrefix() == '')
			return route('newhome');
		else if(request()->route()->getPrefix()=='restaurant')
			return route('restaurant.signup');
		else if(request()->route()->getPrefix()=='driver')
			return route('driver.home');

	}
}

if (!function_exists('get_current_login_user_id')) {
	function get_current_login_user_id()
	{
		if (get_current_login_user() == 'admin') {
			return auth()->guard('admin')->user()->id;
		}
		elseif (get_current_login_user() == 'restaurant') {
			return auth()->guard('restaurant')->user()->id;
		}
		elseif (get_current_login_user() == 'web') {
			return auth()->guard('web')->user()->id;
		}
	}
}

if (!function_exists('get_current_login_user_language')) {
	function get_current_login_user_language() {
		if (get_current_login_user() == 'admin') {
			return auth()->guard('admin')->user()->language;
		} elseif (get_current_login_user() == 'restaurant') {
			return auth()->guard('restaurant')->user()->language;
		} elseif (get_current_login_user() == 'web') {
			return auth()->guard('web')->user()->language;
		}
	}
}


if (!function_exists('get_current_login_user_details')) {
	function get_current_login_user_details($detail) {
		if (get_current_login_user() == 'admin') {
			return auth()->guard('admin')->user()->$detail;
		} elseif (get_current_login_user() == 'restaurant') {
			return auth()->guard('restaurant')->user()->$detail;
		} elseif (get_current_login_user() == 'web') {
			return auth()->guard('web')->user()->$detail;
		}
	}
}

if (!function_exists('get_current_store_id')) {
	function get_current_store_id() {
		$user_id = auth()->guard('restaurant')->user()->id;
		$store = $store = Store::where('user_id', $user_id)->first();
		return $store->id;
	}
}

if (!function_exists('get_store_user_id')) {
	function get_store_user_id($store_id, $column = 'user_id') {
		$user_id = Store::find($store_id)->$column;
		return $user_id;
	}
}

if (!function_exists('get_driver_user_id')) {
	function get_driver_user_id($driver_id, $column = 'user_id') {
		$user_id = Driver::find($driver_id)->$column;
		return $user_id;
	}
}

if (!function_exists('get_user_address')) {
	function get_user_address($user_id) {
		$address = UserAddress::where('user_id', $user_id)->where('default', '1')->first();
		return $address;
	}
}

if (!function_exists('get_store_address')) {
	function get_store_address($user_id) {
		$address = UserAddress::where('user_id', $user_id)->first();
		return $address;
	}
}

if (!function_exists('flash_message')) {
	/**
	 * Save Session
	 *
	 * @param String $class
	 * Class name for error mesage
	 *
	 * @param String $message
	 * Error messgae content
	 * */
	// Set Flash Message function
	function flash_message($class, $message) {
		\Session::flash('alert-class', 'alert-' . $class);
		\Session::flash('message', $message);
	}
}

if (!function_exists('time_data')) {

	/**
	 * Checks if a value exists in an array in a case-insensitive manner
	 *
	 * @param integer $key file type id
	 *                     The searched value
	 *
	 * @return if key found, return particular value of key. Otherwise return full array.
	 */
	function time_data($key = '') {
		$time_data = resolve('time_data');
		if ($key != '') {
			return $time_data[$key];
		} else {
			return '';
		}
	}
}

// one week date

if (!function_exists('date_data')) {

	function date_data()
	{
		$current_date = date("Y/m/d");
		$week_date = $current_date;
		$date;
		for ($i = 0; $i <= 6; $i++) {
			$date[date('Y-m-d', strtotime('+' . $i . ' day', strtotime($week_date)))] = date('Y, M d', strtotime('+' . $i . ' day', strtotime($week_date)));
		}

		return $date;
	}
}

if (!function_exists('convert_minutes')) {

	function convert_minutes($str_time) {
		$str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);

		sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

		return $minutes = ($hours * 3600 + $minutes * 60 + $seconds) / 60;
	}
}

if (!function_exists('convert_format')) {
	function convert_format($str_time) {
		$hours = (int) ($str_time / 60);
		$minutes = ($str_time % 60);
		$format = '%02d:%02d:00';

		return sprintf($format, $hours, $minutes);
	}
}

if (!function_exists('time_format')) {

	function time_format($str_time) {
		return date("g:i", strtotime($str_time)).' '.trans('api_messages.monthandtime.'.date("a", strtotime($str_time)));
	}
}

if (!function_exists('promo_calculation')) {
	
	function promo_calculation($delivery_type ='' ,$promo_code = '') {
		$promo_flag = 0;
		if(request()->segment(1) == 'api')
		{
			 $user_id = JWTAuth::toUser(request()->token);
			 $user_id = $user_id->id;
		}
		else
		{
			$user_id = auth()->guard()->user()->id;
		}

		$order_id = Order::where('user_id', $user_id)->status('cart')->first();
		if (!$order_id) {
			return response()->json(
				[

					'status_message' => 'Cart Empty',

					'status_code' => '0',

				]
			);
		} else {
			if($promo_code != '' )
			{
				UsersPromoCode::where('user_id', $user_id)->update(['promo_default' =>0]);	
				UsersPromoCode::WhereHas(
				'promo_code')->where('user_id', $user_id)->where('promo_code_id',$promo_code)->update(['promo_default' =>1]);
				$user_promocode = UsersPromoCode::WhereHas(
				'promo_code')->where('user_id', $user_id)->where('promo_default',1)->first();
			}
			elseif($promo_code == 0 && $promo_code != '' )
			{
				UsersPromoCode::where('user_id', $user_id)->update(['promo_default' =>0]);	
				$user_promocode = UsersPromoCode::WhereHas('promo_code')->where('user_id', $user_id)->where('promo_default',1)->where('order_id', '0')->first();
			}
			else
			{
				$user_promocode = UsersPromoCode::WhereHas('promo_code')->where('user_id', $user_id)->where('promo_default',1)->where('order_id', '0')->first();
			}
			$promo_code_id = 0;
			if ($user_promocode != '') {
				$promo_amount = 0;
				$today = date('Y-m-d');
				if(($user_promocode->promo_code->status != 0) && ($user_promocode->promo_code->start_date <= $today) && ($user_promocode->promo_code->end_date >= $today)){
					$promo_code_id = isset($promo_code) ? $promo_code : $user_promocode->promo_code->id;
					if ($user_promocode->promo_code->promo_type == 0) {
						$promo_amount = $user_promocode->promo_code->price;
						$promo_flag = 1;
					} else {
							$delivery_promo = ($order_id->subtotal + $order_id->tax + $order_id->delivery_fee + $order_id->booking_fee) / 100 * $user_promocode->promo_code->percentage;
							$takeaway_promo = ($order_id->subtotal + $order_id->tax + $order_id->booking_fee) / 100 * $user_promocode->promo_code->percentage;
							$promo_amount = $delivery_type == 'takeaway' ? $takeaway_promo : $delivery_promo;
					}
				}else{
					$user_promocode->delete($user_promocode->id);
				}
			}
			
			$update = Order::where('id', $order_id->id)->status('cart')->first();
			$promo_amount = isset($promo_amount) ? $promo_amount : '';
			$promo_code_id =isset($promo_code_id) ? $promo_code_id  :0;
			$total_amount = $update->user_total;
			$update->promo_id = $promo_code_id;
			$update->promo_amount = number_format_change($promo_amount,2);
			$update->total_amount = $total_amount;
			$update->save();

			$total_ammm = $update->subtotal + $update->tax + $update->delivery_fee + $update->booking_fee + $update->user_penality ;
			if($promo_flag == 1 && $promo_amount >  $total_ammm)
			{
				$promo_amount =  $total_ammm;
			}
			else
			{
				$promo_amount  = $promo_amount ;
			}
			return number_format_change($promo_amount,2);
		}
	}
}

if (!function_exists('promo_id')) {
	function promo_id($delivery_type ='' ,$promo_code = '') {
		if(request()->segment(1) == 'api')
		{
			 $user_id = JWTAuth::toUser(request()->token);
			 $user_id = $user_id->id;
		}
		else
		{
		$user_id = auth()->guard()->user()->id;
		}

		$order_id = Order::where('user_id', $user_id)->status('cart')->first();

		if (!$order_id) {
			return response()->json(
				[

					'status_message' => 'Cart Empty',

					'status_code' => '0',

				]
			);
		} else {
			$user_promocode = UsersPromoCode::WhereHas(
				'promo_code'
			)->where('user_id', $user_id)->where('promo_default',1)->where('order_id', '0')->first();
			$promo_amount = 0;
			$promo_code_id = 0;
			if ($user_promocode != '') {
				$promo_code_id = $user_promocode->promo_code->id;
				if ($user_promocode->promo_code->promo_type == 0) {
					$promo_amount = $user_promocode->promo_code->price;
				} else {
					$promo_amount = ($order_id->subtotal + $order_id->tax + $order_id->delivery_fee + $order_id->booking_fee) / 100 * $user_promocode->promo_code->percentage;
				}
			}
			$update = Order::where('id', $order_id->id)->status('cart')->first();

			$total_amount = $update->user_total;

			return $promo_code_id;
		}
	}
}

if (!function_exists('offer_calculation')) {

	function offer_calculation($store_id, $order_id)
	{
		$store_offer = StoreOffer::activeOffer()
			->where('store_id', $store_id)
			->first();
		$offer = 0;
		if ($store_offer) {
			$offer = $store_offer->percentage;
		}

		$item = DB::table('order_item')
			->selectRaw('sum((price) * ' . $offer . ' / 100 ) as total,sum(tax * ' . $offer . ' / 100 ) as tax,sum(price) as total_amount,sum(tax) as total_tax')
			->where('order_id',$order_id)
			->groupBy('id')
			->get();
			
		
		$offer_amount = $item->sum('total');
		$offer_tax = $item->sum('tax');
		$item_amount = $item->sum('total_amount');
		$item_tax = $item->sum('total_tax');
		// $item_amount

		$order = Order::find($order_id);
		$order->offer_percentage = $offer;
		$order->subtotal = $item_amount ;
		$order->tax = $item_tax - $offer_tax;
		$order->offer_amount = $offer_amount;
		
		$order->save();
		$subtotal = number_format($order->subtotal, 2, '.', '');
		return $subtotal;
	}
}

if (!function_exists('use_wallet_amount')) {
	function use_wallet_amount($order_id, $is_wallet,$tips = 0) {
		$helper = new helper;
		$order_details = Order::find($order_id);		
		if(request()->segment(1) == 'api')
			$order_amount = $order_details->user_total + $tips ;
		else
			$order_amount = $order_details->total_amount + $tips ;			
		$applied_wallet = 0;
		$remaining_wallet = 0;
		$wallet_amount = 0;
		$amount = $order_amount;
		$total_amount_wallet = $amount ;
		if ($is_wallet == 1) {
			$user_id = auth()->guard()->user()->id;
			$wallet = Wallet::where('user_id', $user_id)->first();
			if ($wallet == '') {
				return [
					'amount' => $amount,
					'applied_wallet_amount' => $applied_wallet,
					'remaining_wallet_amount' => $remaining_wallet,
					'wallet' => 0,
				];
			} else {
				$wallet_amount = $wallet->amount;
			}
		
			if ($order_amount >= $wallet_amount) {
				$amount = $total_amount_wallet - $wallet_amount;
				$remaining_wallet = 0;
				$applied_wallet = $wallet_amount;
				$total_amount_wallet = $amount;
			} else if ($order_amount < $wallet_amount) {
				$remain_wallet = $wallet_amount - $order_amount;
				$amount = 0;
				$applied_wallet = $order_amount;

				$remaining_wallet = $helper->itemConvert($order_details->getRawOriginal('currency_code'),$wallet->getRawOriginal('currency_code'),$remain_wallet);
				$total_amount_wallet = $amount;
			} else {
				$amount = $order_amount;
				$total_amount_wallet = $amount;
			}
		}
		$order_details->wallet_amount = $applied_wallet;
		$order_details->total_amount = $total_amount_wallet;
		$order_details->save();
		return [
			'amount' => $amount,
			'applied_wallet_amount' => $applied_wallet,
			'remaining_wallet_amount' => $remaining_wallet,
		];

	}

}

if (!function_exists('replace_null_value')) {

	function replace_null_value($array) {

		return array_map(function ($value) {
			return $value == null ? '' : $value;
		}, $array);

	}
}

if (!function_exists('getWeekDates')) {

	function getWeekDates($year, $week) {
		$from = date("Y-m-d", strtotime("{$year}-W{$week}-1")); //Returns the date of monday in week
		$to = date("Y-m-d", strtotime("{$year}-W{$week}-7")); //Returns the date of sunday in week

		return ['week_start' => $from, 'week_end' => $to];

		//return "Week {$week} in {$year} is from {$from} to {$to}.";
	}

}

function getStaticGmapURLForDirection($origin, $destination, $size = "1350x400") {

	$markers = array();
	$pickup = url('images/map_green.svg');
	$drop = url('images/map.png');

	$markers[] = "markers=icon:" . $pickup . "|" . $origin;

	$markers[] = "markers=icon:" . $drop . "|" . $destination;

	$markers = implode('&', $markers);
	$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origin . "&destination=" . $destination . "&mode=driving&key=" . site_setting('google_server_key');
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, false);
	$result = curl_exec($ch);
	curl_close($ch);
	$googleDirection = json_decode($result, true);
	if ($googleDirection['routes']) {
		$polyline = urlencode($googleDirection['routes'][0]['overview_polyline']['points']);
		$zoom = 12;
	} else {
		logger('Static map issue'.$googleDirection['error_message']);
		return;
	}

	$map = "https://maps.googleapis.com/maps/api/staticmap?center=".$origin."&size=$size&scale=2&zoom=$zoom&maptype=roadmap&path=color:0x000000ff|weight:4|enc:".$polyline."&".$markers."&key=" . site_setting('google_api_key');
	return $map;

}

if (!function_exists('get_status_text')) {

	function get_status_text($status) {
		return (($status == 0) ? trans('admin_messages.inactive') : ($status == 1 ? trans('admin_messages.active') : ''));
	}

}
if (!function_exists('get_status_yes')) {

	function get_status_yes($status) {
		return ($status === 1) ? trans('admin_messages.yes') : trans('admin_messages.no');
	}

}

if (!function_exists('change_date_format')) {

	function change_date_format($value) {
		if (site_setting('site_date_format') == 'm-d-Y' || site_setting('site_date_format') == 'm/d/Y') {
			if ($value) {
				$date = str_replace('/', '-', $value);
				$date = explode('-', $date);
				return $date[1] . '-' . $date[0] . '-' . $date[2];
			}
		} else {
			return str_replace('/', '-', $value);
		}

	}
}

if (!function_exists('set_date_on_picker')) {

	function set_date_on_picker($value,$format='d-m-Y') {
		if ($value) {
			return date($format, strtotime($value));
		}

		return '';
	}
}

if (!function_exists('navigation_active')) {

	function navigation_active($route_name, $type = '')
	{
		if (request()->route()->getName() == $route_name) {
			if ($type == '') {
				return true;
			}
			return (@request()->route()->parameters['user_type'] == $type);
		}
		return false;
	}
}

if (!function_exists('checkPermission')) {

	function checkPermission($permission)
	{
		return auth()->guard('admin')->user()->hasPermission($permission);
	}
}

if (!function_exists('get_image_size')) {
	/**
	 * Get Crop Image size
	 * @return Image size
	 *
	 * */
	function get_image_size($name) {
		$file['site_logo'] = array('width' => '130', 'height' => '65');
		$file['email_logo'] = array('width' => '130', 'height' => '65');
		$file['site_favicon'] = array('width' => '50', 'height' => '50');
		$file['store_logo'] = array('width' => '130', 'height' => '65');
		$file['footer_logo'] = array('width' => '130', 'height' => '65');
		$file['app_logo'] = array('width' => '140', 'height' => '140');
		$file['driver_logo'] = array('width' => '130', 'height' => '65');
		$file['driver_white_logo'] = array('width' => '130', 'height' => '65');
		$file['home_slider'] = array('width' => '1300', 'height' => '500');
		$file['dietary_icon_size'] = array('width' => '256', 'height' => '256');
		$file['item_image_sizes'] = [
			array('width' => '120', 'height' => '120'),
			array('width' => '600', 'height' => '350'),
			array('width' => '520', 'height' => '320'),
		];
		$file['cuisine_image_size'] = array('width' => '260', 'height' => '200');
		$file['store_image_sizes'] = [
			array('width' => '520', 'height' => '280'),
			array('width' => '480', 'height' => '320'),
			array('width' => '215', 'height' => '215'),
			array('width' => '100', 'height' => '100'),
			array('width' => '320', 'height' => '129'),
		];
		return $file[$name];
	}
}

if (!function_exists('driver_default_documents')) {
	/**
	 * Get Crop Image size
	 * @return Image size
	 *
	 * */
	function driver_default_documents() {
		return array(8 => 'licence_front',
			9 => 'licence_back',
			10 => 'registeration_certificate',
			11 => 'insurance',
			12 => 'motor_certiticate');
	}
}

if (!function_exists('currency_symbol')) {
	/**
	 * Get Crop Image size
	 * @return Image size
	 *
	 * */
	function currency_symbol() {
		return DEFAULT_CURRENCY;
	}
}

if (!function_exists('side_navigation')) {
	/**
	 * Get Crop Image size
	 * @return Image size
	 *
	 * */
	function side_navigation()
	{
		//side navigation
		$nav['dashboard'] = array(
			'name' => trans('admin_messages.dashboard'),
			'icon' => 'assessment',
			'has_permission' => true,
			'route' => route('admin.dashboard'),
			'active' => navigation_active('admin.dashboard'),
		);

		$nav['admin_management'] = array(
			'name' => trans('admin_messages.admin_user_management'),
			'icon' => 'supervised_user_circle',
			'has_permission' => checkPermission('view-admin'),
			'route' => route('admin.view_admin'),
			'active' => navigation_active('admin.view_admin')
		);

		$nav['role_management'] = array(
			'name' => trans('admin_messages.role_management'),
			'icon' => 'lock',
			'has_permission' => checkPermission('view-role'),
			'route' => route('admin.view_role'),
			'active' => navigation_active('admin.view_role')
		);

		$nav['user_management'] = array(
			'name' => trans('admin_messages.user_management'),
			'icon' => 'account_circle',
			'has_permission' => checkPermission('view-user'),
			'route' => route('admin.view_user'),
			'active' => navigation_active('admin.view_user')
		);
		
		$nav['driver_management'] = array(
			'name' => trans('admin_messages.driver_management'),
			'icon' => 'drive_eta',
			'has_permission' => checkPermission('view-driver'),
			'route' => route('admin.view_driver'),
			'active' => navigation_active('admin.view_driver')
		);
		
		$nav['home_banner'] = array(
			'name' => trans('admin_messages.home_banner'),
			'icon' => 'merge_type',
			'has_permission' => checkPermission('view-home_banner'),
			'route' => route('admin.home_banner'),
			'active' => navigation_active('admin.home_banner')
		);
		
		$nav['cuisine_management'] = array(
			'name' => trans('admin_messages.cuisine_management'),
			'icon' => 'category',
			'has_permission' => checkPermission('view-category'),
			'route' => route('admin.cuisine'),
			'active' => navigation_active('admin.cuisine')
		);
		
		$nav['restaurant_management'] = array(
			'name' => trans('admin_messages.store_management'),
			'icon' => 'restaurant',
			'has_permission' => checkPermission('view-restaurant'),
			'route' => route('admin.view_restaurant'),
			'active' => navigation_active('admin.view_restaurant')
		);
		
		$nav['send_message'] = array(
			'name' => trans('admin_messages.send_message'),
			'icon' => 'email',
			'has_permission' => checkPermission('manage-send_message'),
			'route' => route('admin.send_message'),
			'active' => navigation_active('admin.send_message')
		);
		
		$nav['order_management'] = array(
			'name' => trans('admin_messages.order_managemnt'),
			'icon' => 'add_shopping_cart',
			'has_permission' => checkPermission('manage-orders'),
			'route' => route('admin.order'),
			'active' => navigation_active('admin.order')
		);
		
		$nav['restaurant_payout_management'] = array(
			'name' => trans('admin_messages.store_payout_management'),
			'icon' => 'euro_symbol',
			'has_permission' => checkPermission('manage-payouts'),
			'route' => route('admin.payout', 1),
			'active' => navigation_active('admin.payout', 1)
		);
		
		$nav['driver_payout_management'] = array(
			'name' => trans('admin_messages.driver_payout_management'),
			'icon' => 'motorcycle',
			'has_permission' => checkPermission('manage-payouts'),
			'route' => route('admin.payout', 2),
			'active' => navigation_active('admin.payout', 2)
		);
		
		$nav['driver_owe_amount'] = array(
			'name' => trans('admin_messages.owe_amount'),
			'icon' => 'attach_money',
			'has_permission' => checkPermission('manage-owe_amount'),
			'route' => route('admin.owe_amount'),
			'active' => navigation_active('admin.owe_amount')
		);
		
		$nav['restaurant_owe_amount'] = array(
			'name' => trans('admin_messages.store_owe_amount'),
			'icon' => 'attach_money',
			'has_permission' => checkPermission('manage-restaurant_owe_amount'),
			'route' => route('admin.restaurant_owe_amount'),
			'active' => navigation_active('admin.restaurant_owe_amount')
		);


		$nav['penality'] = array(
			'name' => trans('admin_messages.penalty'),
			'icon' => 'thumb_down',
			'has_permission' => checkPermission('manage-penality'),
			'route' => route('admin.penality'),
			'active' => navigation_active('admin.penality')
		);
		
		$nav['promo_management'] = array(
			'name' => trans('admin_messages.promo_management'),
			'icon' => 'card_giftcard',
			'has_permission' => checkPermission('view-promo'),
			'route' => route('admin.promo'),
			'active' => navigation_active('admin.promo')
		);
		
		$nav['static_page_management'] = array(
			'name' => trans('admin_messages.static_page_management'),
			'icon' => 'description',
			'has_permission' => checkPermission('view-static_page'),
			'route' => route('admin.static_page'),
			'active' => navigation_active('admin.static_page')
		);
		
		$nav['home_slider'] = array(
			'name' => trans('admin_messages.home_slider'),
			'icon' => 'description',
			'has_permission' => checkPermission('view-restaurant_slider'),
			'route' => route('admin.view_home_slider'),
			'active' => navigation_active('admin.view_home_slider')
		);
		
		$nav['country_management'] = array(
			'name' => trans('admin_messages.country_management'),
			'icon' => 'language',
			'has_permission' => checkPermission('view-country'),
			'route' => route('admin.country'),
			'active' => navigation_active('admin.country')
		);
		
		$nav['currency_management'] = array(
			'name' => trans('admin_messages.currency_management'),
			'icon' => 'euro_symbol',
			'has_permission' => checkPermission('view-currency'),
			'route' => route('admin.currency'),
			'active' => navigation_active('admin.currency')
		);
		
		
		$nav['language_management'] = array(
			'name' => trans('admin_messages.language_management'),
			'icon' => 'translate',
			'has_permission' => checkPermission('view-language'),
			'route' => route('admin.languages'),
			'active' => navigation_active('admin.languages')
		);
		
		$nav['cancel_reason'] = array(
			'name' => trans('admin_messages.cancel_reason'),
			'icon' => 'cancel',
			'has_permission' => checkPermission('view-cancel_reason'),
			'route' => route('admin.order_cancel_reason'),
			'active' => navigation_active('admin.order_cancel_reason')
		);
		
		$nav['review_issue_type'] = array(
			'name' => trans('admin_messages.review_issue_type'),
			'icon' => 'report_problem',
			'has_permission' => checkPermission('view-review_issue_type'),
			'route' => route('admin.issue_type'),
			'active' => navigation_active('admin.issue_type')
		);
		
		$nav['review_vehicle_type'] = array(
			'name' => trans('admin_messages.manage_vehicle_type'),
			'icon' => 'drive_eta',
			'has_permission' => checkPermission('view-vehicle_type'),
			'route' => route('admin.vehicle_type'),
			'active' => navigation_active('admin.vehicle_type')
		);
		
		$nav['food_receiver'] = array(
			'name' => trans('admin_messages.food_receiver'),
			'icon' => 'receipt',
			'has_permission' => checkPermission('view-recipient'),
			'route' => route('admin.food_receiver'),
			'active' => navigation_active('admin.food_receiver')
		);
		
		$nav['help_category'] = array(
			'name' => trans('admin_messages.help_category'),
			'icon' => 'help',
			'has_permission' => checkPermission('view-help_category'),
			'route' => route('admin.help_category'),
			'active' => navigation_active('admin.help_category')
		);
		
		$nav['help_subcategory'] = array(
			'name' => trans('admin_messages.help_subcategory'),
			'icon' => 'help',
			'has_permission' => checkPermission('view-help_subcategory'),
			'route' => route('admin.help_subcategory'),
			'active' => navigation_active('admin.help_subcategory')
		);
		
		$nav['help'] = array(
			'name' => trans('admin_messages.help'),
			'icon' => 'help',
			'has_permission' => checkPermission('view-help'),
			'route' => route('admin.help'),
			'active' => navigation_active('admin.help')
		);
		
		$nav['site_setting'] = array(
			'name' => trans('admin_messages.site_setting'),
			'icon' => 'settings',
			'has_permission' => checkPermission('manage-site_setting'),
			'route' => route('admin.site_setting'),
			'active' => navigation_active('admin.site_setting')
		);


		$nav['support'] = array(
			'name' => trans('admin_messages.support'),
			'icon' => 'support_agent',
			'has_permission' => checkPermission('view-support'),
			'route' => route('admin.support'),
			'active' => navigation_active('admin.support')
		);

		return $nav;
	}
}

if (!function_exists('number_format_change')) {
	/**
	 * Currency Symbol
	 *
	 * @return int currency symbol
	 */
	function number_format_change($value) {
		return number_format((float) $value, 2, '.', '');
	}
}

//push notification function

if (!function_exists('push_notification_for_store')) {


	function push_notification_for_store($order) {
		
		if ($order->schedule_status == 0) {

			$push_notification_title = __('store_api_language.orders.order_created');
			$type = 'new_order';

		} else {

			$push_notification_title = trans('store_api_language.orders.schedule_order_created');
			$type = 'schedule_order';
		}

		$store_user = $order->store->user;
		$push_notification_data = [
			'type' => $type,
			'order_id' => $order->id,
			'order_data' => [
				'id' => $order->id,
				'order_item_count' => $order->order_item->count(),
				'user_name' => $order->user->name,
				'user_image' => $order->user->user_image_url,
				'remaining_seconds' => $order->remaining_seconds,
				'total_seconds' => $order->total_seconds,
				'status_text' => $order->status_text,
			],
		];
		Log::info('User Store ID: '.$order->store->id);
		$firbase = resolve("App\Services\FirebaseService");
		$requestData = json_encode(["custom" => $push_notification_data]);
		push_notification($store_user->device_type, $push_notification_title, $push_notification_data, 1, $store_user->device_id, true);
        $firbase->updateReference("new_order/". $order->store->id,$requestData);

	}
}



//otp genrate function

if (!function_exists('otp_for_forget_user')) {

	function otp_for_forget_user($email, $otp) {
		$return['status'] = 'true';
		$data['subject'] = 'Password Reset';
		$data['otp_code'] = $otp;
		$data['logo'] = site_setting(1, 4);

		\session()->forget('password_code');
		\Session::put('password_code', $data['otp_code']);
		try {
			\Mail::to($email,'')->queue(new ForgotEmail($data));
			
		} catch (\Exception $e) {
			$return['status'] = 'false';
			$return['error'] = $e->getMessage();
		}
		return $return;
	}
}

//Estimation for Delivery the order

if (!function_exists('est_time')) {

	function est_time($preparation, $delivery) {

		$secs = strtotime($preparation) - strtotime("00:00:00");
		$result = date("H:i:s", strtotime($delivery) + $secs);
		$secs = strtotime($result) - strtotime("00:00:00");
		$est_time = date("H:i:s", time() + $secs);

		return $est_time;
	}
}

if (!function_exists('buildExcelFile')) {

	function buildExcelFile($filename, $data, $width = array())
	{
		$excel = app('excel');

		$excel->getDefaultStyle()
			->getAlignment()
			->setHorizontal('left');
		foreach ($data as $key => $array) {
			foreach ($array as $k => $v) {
				if (!$v) {
					$data[$key][$k] = '0';
				}
			}
		}

		return $excel->create($filename, function (LaravelExcelWriter $excel) use ($data, $width) {
			$excel->sheet('exported-data', function (LaravelExcelWorksheet $sheet) use ($data, $width) {
				$sheet->fromArray($data)->setWidth($width);
				$sheet->setAllBorders('thin');
			});
		});
	}
}

if (!function_exists('total_count_card')) {
	function total_count_card() {
		$user_details = auth()->guard('web')->user();
		if ($user_details) {
			$order_data = get_user_order_details('', $user_details->id);
		} else {
			$order_data = get_user_order_details();
		}
		if (isset($order_data['total_item_count'])) {
			return $order_data['total_item_count'];
		}

	}
}

if (!function_exists('get_booking_fee')) {
	function get_booking_fee($subtotal = 0) {
		$booking_percentage = site_setting('booking_fee');
		return number_format_change($subtotal * $booking_percentage / 100);
	}
}
if (!function_exists('calculate_tax')) {
	function calculate_tax($amount, $tax) {

		return number_format_change($amount * $tax / 100);
	}
}

if (!function_exists('get_delivery_fee')) {
	function get_delivery_fee($res_lat, $res_long, $currency_code='',$delivery_type = 'delivery') {
			$helper = new helper;
			$delivery = array();
			if($delivery_type)
			{
				$delivery_type = $delivery_type;
			}
			else{
				$delivery_type = 'delivery';
			}
			
			if (site_setting('delivery_fee_type') == 0) {
				
				$user_location = user_address_details();
				$pickup_fare = 0;
				$drop_fare = 0;
				$distance_fare = 0;
				
				$delivery[] = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$pickup_fare);
				$delivery[] = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$drop_fare);
				$delivery[] = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$distance_fare);
				$delivery_fee = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,site_setting('delivery_fee'));
				$delivery_fee = ($delivery_type == 'delivery') ? $delivery_fee : 0;
			} else {	
				$user_location = user_address_details();
				$pickup_fare = site_setting('pickup_fare');
				$drop_fare = site_setting('drop_fare');
				$distance_fare = site_setting('distance_fare');
				$lat1 = $res_lat;
				$lat2 = isset($user_location['latitude']) ?$user_location['latitude']:'' ;
				$long1 = $res_long;
				$long2 = isset($user_location['longitude']) ?$user_location['longitude']:'';
				$result = get_driving_distance($lat1, $lat2, $long1, $long2);
				$km = floor(@$result['distance'] / 1000) . '.' . floor(@$result['distance'] % 1000);
				// less then 1 km update km as 1 
				$km = $km < 1 ? 1:$km;
				
				if($currency_code) {
					$delivery[] = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$pickup_fare);
					$delivery[] = $helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$drop_fare);
					$delivery[] = ($helper->itemConvert(DEFAULT_CURRENCY,$currency_code,$distance_fare) * $km);	
				}
				if(Auth::user())
				{
					$delivery['distance_fare'] = $delivery[2];
				}
				else
				{
					$delivery['distance_fare'] = 0;
				}
				if($delivery_type == 'takeaway')
				{
					$delivery_fee = 0;
				}
				else 
				{	
					$delivery_fee = number_format_change($delivery[0] + $delivery[1] + ($delivery[2]));
				}
			}
			if($currency_code) {
			$delivery[] = $delivery_fee;
			return $delivery;
			} else {
				return $delivery_fee;
			}
	}
}

if (!function_exists('add_order_data')) {

	function add_order_data() {

		if(session('order_data') == null) {
			return '';
		}
		$store_id = Session::get('order_data')['store_id'];
		$store_detail = Store::find($store_id);
		$order_data = session('order_data');
		$schedule_data = session::get('schedule_data');
		$schedule_status = 0;
		$schedule_datetime = null;
		if($schedule_data['status'] == 'Schedule') {
			$schedule_status = 1;
			$schedule_datetime = $schedule_data['date'] . ' ' . $schedule_data['time'];
		}

		$return_order_data = [];
		$data_id = '';
		$user_id = get_current_login_user_id();

		//create or update order
		$order = Order::where('user_id', $user_id)->status()->first();

		if($order) {
			$order_item_ids = $order->order_item->pluck('id')->toArray();
			$order_items = OrderItem::whereIn('id',$order_item_ids)->get();
			foreach($order_items as $order_item) {
				$orderitem_modifier_ids = $order_item->order_item_modifier->pluck('id')->toArray();
				OrderItemModifierItem::whereIn('order_item_modifier_id',$orderitem_modifier_ids)->delete();
				OrderItemModifier::whereIn('id',$orderitem_modifier_ids)->delete();
				$order_item->delete();
			}
		} 
		else {
			$order = new Order;
		}
		
		$ordered_currency = $order_data['currency_code'];
		$order->store_id = $store_id;
		$order->user_id = $user_id;
		$order->currency_code = $ordered_currency  ;
		$order->schedule_status = $schedule_status;
		$order->schedule_time = $schedule_datetime;
		$order->status = 0;
		$order->save();
		$quan = $order_data['total_item_count'];
		foreach($order_data['items'] as $item) {
			$item_id = $item['item_id'];
			$menu_item = MenuItem::find($item_id);
			$modifier_price = 0;
			foreach($item['modifier'] as $modifier_item) {
				logger("modifiers item ".json_encode($modifier_item));
				if($modifier_item['is_selected']) {
					$modifier_price += (($modifier_item['item_count'] * $quan ) * $modifier_item['price']);
					// $modifier_price += $modifier_item['price'];
				}
			}
			
			$price_sum = $menu_item->price;
			$t_menu_price = $menu_item->offer_price > 0 ? $menu_item->offer_price : $price_sum;
		
			$total_amount = ($quan * $t_menu_price ) + $modifier_price;
		
			if($menu_item->menu->menu_closed == 1) {
				$orderitem = new OrderItem;
				$orderitem->order_id = $order->id;
				$orderitem->menu_item_id = $menu_item->id;
				$orderitem->menu_name = $menu_item->name;
				$orderitem->price = $price_sum;
				$orderitem->quantity = $item['item_count'];
				$orderitem->notes = $item['item_notes'];
				$orderitem->currency_code = $ordered_currency;
				$orderitem->total_amount =$item['item_total'];
				$orderitem->modifier_price = $modifier_price;
				$orderitem->tax = calculate_tax($total_amount, $menu_item->tax_percentage);
				$orderitem->save();
				
				if(is_array($item['modifier'])) {
					foreach($item['modifier'] as $modifier_item) {	
						if($modifier_item['is_selected'] == true) {
							$orderitem_modifier = OrderItemModifier::firstOrCreate([
								'order_item_id' => $orderitem->id,
								'modifier_id' 	=> $modifier_item['menu_item_modifier_id']
							]);
							$modifier = MenuItemModifier::find($modifier_item['menu_item_modifier_id']);
							$orderitem_modifier->modifier_name = $modifier->name;
							$orderitem_modifier_item = new OrderItemModifierItem;
							$orderitem_modifier_item->order_item_modifier_id = $orderitem_modifier->id;
							$orderitem_modifier_item->menu_item_modifier_item_id = $modifier_item['id'];
							$orderitem_modifier_item->modifier_item_name = $modifier_item['name'];
							$orderitem_modifier_item->price = number_format_change($modifier_item['price']);
							$orderitem_modifier_item->count = $quan * $modifier_item['item_count'];
							$orderitem_modifier_item->currency_code = $ordered_currency;
							$orderitem_modifier_item->save();
						}	

					}
				}
			}
		}
		$orderitem = OrderItem::where('order_id', $order->id)->get();
	
		$order_detail_data = clone $order;
		$subtotal 	= $order_data['subtotal'];
	
		$order_tax 	= $order_data['tax'];
		$booking_fee = get_booking_fee($subtotal);

		list($pickup_fare,$drop_fare,$distance_fare,$delivery_fee) = get_delivery_fee($store_detail->user_address->latitude, $store_detail->user_address->longitude, $ordered_currency);
		// $kilo_meter = get_kilometer($store_detail->user_address->latitude, $store_detail->user_address->longitude);
		
		$order_detail_data->subtotal 	= $subtotal;
		$order_detail_data->tax 		= $order_tax;
		$order_detail_data->booking_fee = $booking_fee;
		$order_detail_data->delivery_fee= $delivery_fee;
		$order_detail_data->total_amount= $subtotal + $order_tax + $booking_fee + $delivery_fee;
		$order_detail_data->save();

		$user_id = get_current_login_user_id();

		// create user_address
		if($user_id) {
			$user_address = UserAddress::where('user_id', $user_id)->first();
			if (!$user_address) {
				$user_address = new UserAddress;
				$user_address->user_id = $user_id;
				$user_address->default = 1;
				$user_address->type = 0;
			}
			$country_name = '';
			if (session('country')) {
				$country_name = Country::where('code', session('country'))->first()->name;
			}
			$user_address->address = session('country');
			$user_address->street = session('address1');
			$user_address->city = session('city');
			$user_address->state = session('state');
			$user_address->country = session('country');
			$user_address->country_code = session('country');
			$user_address->postal_code = session('postal_code');
			$user_address->latitude = session('latitude');
			$user_address->longitude = session('longitude');

			$user_address->save();
		}

		$order_delivery = OrderDelivery::where('order_id', $order->id)->first();
		if(!$order_delivery) {
			$order_delivery = new OrderDelivery;
		}

		$subtotal = offer_calculation($store_id, $order->id);
		
		$order_delivery->order_id = $order_detail_data->id;
		$order_delivery->pickup_fare = $pickup_fare;
		$order_delivery->drop_fare = $drop_fare;
		$order_delivery->distance_fare = $distance_fare;
		$order_delivery->total_fare = $delivery_fee ;
		$order_delivery->currency_code = $ordered_currency;
		$order_delivery->save();
		session()->forget('order_data');
		return 'success';
	}
}

if (!function_exists('get_user_order_details')) {
	
	function get_user_order_details($store_id = null, $user_id = null,$delivery_type = 'delivery', $promo_code = null) {
		$order_data = [];
		if($user_id) {
			$user = User::find($user_id);
			$order = Order::where('user_id', $user_id)->status('cart')->first();			//check store is open or not
			$is_available = 0;
			if (isset($order->store->store_all_time[0])) {
				$is_available = $order->store->store_all_time[0]->is_available;
			}
			if ($is_available != 1) {
				session()->forget('order_data');
				return '';
			}
			if ($order != '') {
				$order_detail_data = clone $order;
				foreach ($order_detail_data->order_item as $value) {
					// if order contains modifier
					$order_item_modifier = collect($value['order_item_modifier']);
					$results = $new_result = array();
					$order_item_modifier->map(function($item) use (&$results,$user,$value,$order_detail_data,&$new_result) {
						$order_item_modifier_item = collect($item['order_item_modifier_item']);
						$results = $order_item_modifier_item->map(function($item) use (&$results,$user,$value,$order_detail_data) {
							// if session change currency code
							if($user->currency_code->code!=$value->original_currency_code) {
								$helper = new helper;

								$modifi_item = OrderItemModifierItem::find($item['id']);
								$menu_modifier = MenuItemModifierItem::find($modifi_item->menu_item_modifier_item_id);

								// if session has store currency code
								if($user->currency_code->code==$menu_modifier->currency_code) {
									$menu_modifier_price = $menu_modifier->price;
									$modifi_item->price = $menu_modifier_price;
								} else {
									// if session has other currency code convert it
									$modifi_item->price = $helper->itemConvert($value->original_currency_code,$user->currency_code->code,$modifi_item->getOriginal('price'));
								}

								$modifi_item->currency_code = $user->currency_code->code;
								$modifi_item->save();

								// return updated value
								$item = OrderItemModifierItem::find($item['id'])->toArray();
							}
							
							return [
								'id' 	=> $item['id'],
								'count' => $item['count'],
								'item_count' => intval($item['count']),
								'menu_item_modifier_item_id' => $item['menu_item_modifier_item_id'],
								'price' => (string) number_format($item['price'] * $item['count'],'2'),
								'original_price' => (string) number_format($item['price']),
								'name'  => $item['modifier_item_name'],
								'currency_code'  => $item['currency_code'],
							];
						});

						$total_modifier_price = 0;
						foreach($results as $res) {
								$new_result[] = $res;
								$price_data = floatval(str_replace(',', '', $res['price']));
								$total_modifier_price += $price_data ;
						}
						$modifierItem = OrderItemModifier::find($item['id']);
						$modifierItem->modifier_price = $total_modifier_price;
						$modifierItem->save();

						$new_result = array_unique($new_result, SORT_REGULAR);
						return $new_result;
					});

					$orderitem_modifier_ids = $value->order_item_modifier->pluck('id')->toArray();
					$orderitem_modifiers 	= OrderItemModifier::whereIn('id',$orderitem_modifier_ids);

					// update modifier price
					$value->modifier_price = $orderitem_modifiers->sum('modifier_price');
					$value->save();

					// return updated value
					$value = $order_detail_data->order_item->find($value->id);
					// if session change currency code
					if($user->currency_code->code!=$value->original_currency_code) {
							$helper = new helper;
							$menu = MenuItem::find($value->menu_item_id);
							// if session has store currency code
							if($user->currency_code->code==$menu->currency_code) {
							
								$menu_price 	= $menu->price;
								$t_menu_price =  $menu->offer_price > 0 ? $menu->offer_price : $menu_price;
								$total_amount 	= $value->quantity * $t_menu_price + $value->modifier_price;
								$tax 			= ($total_amount * $menu->tax_percentage / 100);

								$value->price = $t_menu_price;
								$value->total_amount = $total_amount;
								$value->tax = $tax;

							} else {	
								// if session has other currency code convert it
								$value->price = $helper->itemConvert($value->original_currency_code,$user->currency_code->code,$value->getOriginal('price'));
								$value->total_amount = $converted_total_amount = $helper->itemConvert($value->original_currency_code,$user->currency_code->code,$value->getOriginal('total_amount'));
								$value->tax = $helper->itemConvert($value->original_currency_code,$user->currency_code->code,$value->getOriginal('tax'));

								// while convert if the tax is zero calculate new tax
								if($value->tax<=0) {
									$menu = MenuItem::find($value->menu_item_id);
									$value->tax = ($converted_total_amount * $menu->tax_percentage / 100);
								}

							}

							// save new session currency code
							$value->currency_code = $user->currency_code->code;
							$value->save();

							// return updated value
							$value = $order_detail_data->order_item->find($value->id);
						}
					if(isset($value->menu_item->menu)){
						
						if ($order->store->status != 0 && $value->menu_item->menu->menu_closed == 1 && $value->menu_item->status == 1 && $value->menu_item->is_visible == 1) {
							
							$orderitem_modifier_ids = $value->order_item_modifier->pluck('id')->toArray();
							$orderitem_modifiers = OrderItemModifier::whereIn('id',$orderitem_modifier_ids)->get();

							$price_sum = ($value->menu_item->offer_price != 0) ? $value->menu_item->offer_price : $value->menu_item->price;
							$price_tot = $value->quantity * $price_sum + $orderitem_modifiers->sum('modifier_price');
							logger($price_tot);
							$order_data[] = array(
								'order_item_id' => $value->id,
								'name' => $value->menu_name,
								'item_notes' => $value->notes,
								'item_id' => $value->menu_item_id,
								'item_count' => $value->quantity,
								'tax' => calculate_tax($price_tot, $value->menu_item->tax_percentage),
								'item_total' => number_format_change($price_tot),
								'item_price' => $price_sum,
								'modifier' => $new_result
							);

						} else {
							try {
								$order_item_modifiers = OrderItemModifier::whereIn('order_item_id',[$value->id])->get();
								if($order_item_modifiers) {
									foreach ($order_item_modifiers as $key => $order_item_modifier) {
										OrderItemModifierItem::whereIn('order_item_modifier_id',[$order_item_modifier->id])->delete();
									}
									OrderItemModifier::whereIn('order_item_id',[$value->id])->delete();
								}
								$value->delete();
							} catch(\Exception $e) {}
						}
					}

				}
				$order_detail_data = clone $order;
				$subtotal 	= number_format_change($order_detail_data->order_item->sum('total_amount'));
				// dd($subtotal);
				$order_tax 	= $order_detail_data->order_item->sum('tax');	
				$order_quantity = $order_detail_data->order_item->sum('quantity');
				$tips 	= number_format_change($order_detail_data->tips);
				$booking_fee = get_booking_fee($subtotal);
				// if($order_detail_data->wallet_amount > 0)
				// 	$total_orer_amount = $order_detail_data->user_total;
				// else 
				// {
				$total_orer_amount = number_format_change($order_detail_data->user_total);
				// }				
				list($pickup_fare,$drop_fare,$distance_fare,$delivery_fee) = get_delivery_fee($order->store->user_address->latitude, $order->store->user_address->longitude, $user->currency_code->code,$delivery_type);
				$penalty = $order_detail_data->user->penalty ? $order_detail_data->user->penalty->remaining_amount : 0;
				$order_detail_data->subtotal = $subtotal;
				$order_detail_data->tax = $order_tax;
				$order_detail_data->booking_fee = $booking_fee;
				$order_detail_data->delivery_fee = $delivery_fee;
				$order_detail_data->total_amount = $subtotal + $order_tax + $booking_fee + $order_detail_data->delivery_fee;
				$order_detail_data->delivery_type = $delivery_type;
				$order_detail_data->currency_code =  $user->currency_code->code ;
				$order_detail_data->tips = $tips ;	
				$order_detail_data->save();	
				// $promo_amount = promo_calculation();
				$promo_amount = promo_calculation($delivery_type,$promo_code);
				$promo_id = promo_id();
				$promo_total_amount = $order_detail_data->total_amount - $promo_amount;
				$promo_total_amount = $promo_total_amount + $penalty;
				$order_detail_data->total_amount = $promo_total_amount > 0 ? $promo_total_amount : 0;
				$order_detail_data->save();
				
				$order->order_delivery->pickup_fare = $pickup_fare;
				$order->order_delivery->drop_fare = $drop_fare;
				
				$order->order_delivery->distance_fare = $distance_fare;
				$order->order_delivery->total_fare = $delivery_fee ;
				$order->order_delivery->currency_code = $user->currency_code->code;
				$order->order_delivery->tips = $tips ;
				$order->order_delivery->save();
				$promo_amount = promo_calculation($delivery_type,$promo_code);	
				if($tips > 0 )
				{
					$tips_total = ($tips + $order_detail_data->total_amount);
					$order_detail_data->total_amount = $tips_total;
				}
				$amount_total = $order->wallet_amount > 0 ? ($order_detail_data->total_amount - $order->wallet_amount) : $order_detail_data->total_amount;
				$amount_total  =  $amount_total > 0 ? $amount_total : 0; 

			
				if($order_quantity > 0) {
					return array(
						'order_id' 		=> $order->id,
						'store_id' 		=> $order_detail_data->store_id,
						'items' 		=> $order_data,
						'total_price' 	=> $amount_total ,
						'delivery_fee' 	=> number_format_change($order_detail_data->delivery_fee),
						'booking_fee' 	=> $order_detail_data->booking_fee,
						'tax' 			=> $order_detail_data->tax,
						'subtotal' 		=> number_format_change($order_detail_data->subtotal),
						'total_item_count' => $order_quantity,
						'promo_amount' 	=> number_format_change($promo_amount),
						'penalty' 		=> $order_detail_data->user_penality,
						'promo_id' 		=> $promo_id,
						'tips'          => $order->tips,
						'delivery_type' =>$order_detail_data->delivery_type,
						'wallet_amount'	=> $order->wallet_amount,
					);
				}
                  
				if($order->order_delivery) {
					$order->order_delivery->delete();
				}

				if($order->payment) {
					$order->payment->delete();                    	
				}

				$order->delete();
				return '';
			}
		} else {

			if(session('order_data') == null) {
				return '';
			}
			try{
				$store_id = $store_id ? $store_id : Session::get('order_data')['store_id'];

				$store_detail = Store::find($store_id);

				$order_data = session('order_data');
				$return_order_data = [];
				$data_id = '';

				$is_available = 0;
				
				if(isset($store_detail->store_all_time[0])) {
					$is_available = $store_detail->store_all_time[0]->is_available;
				}
				if($is_available != 1) {
					session()->forget('order_data');
					return '';
				}
				$quan = $order_data['total_item_count'];
				
				foreach($order_data['items'] as $item) {
					$current_item_count = $item['item_count'];
					$modifier_price = 0;
					foreach ($item['modifier'] as $key => $modifier_item) {
						if($modifier_item['is_selected'] == true) {
							$item['modifier'][$key]['currency_code'] = session('currency');
						}
						$item['modifier'][$key]['item_count'] = $item['modifier'][$key]['org_item_count'] * $current_item_count;

						$modifier_price +=  $item['modifier'][$key]['item_count'] * $modifier_item['price'];
					}
					$item_id = $item['item_id'];
					$menu_item = MenuItem::find($item_id);
					if ($menu_item->menu->menu_closed == 1 && $menu_item->status != 0 && $menu_item->is_visible == 1 && $store_detail->status != 0) {
						$price_sum = $menu_item->offer_price != 0 ? $menu_item->offer_price : $menu_item->price;
						$price_tot = number_format_change(($item['item_count'] * $price_sum) + $modifier_price );
						$return_order_data[] = array(
							'name' => $menu_item->name,
							'item_notes' => $item['item_notes'],
							'item_id' => $menu_item->id,
							'item_count' => $item['item_count'],
							'tax' => calculate_tax($price_tot, $menu_item->tax_percentage),
							'item_total' => $price_tot,
							'item_price' => $price_sum,
							'currency_code'=> session('currency'),
							'modifier' 	=> $item['modifier'],
						);
					}
				}
				list($pickup_fare,$drop_fare,$distance_fare,$delivery_fee) = get_delivery_fee($store_detail->user_address->latitude, $store_detail->user_address->longitude, session('currency') ,$delivery_type);
				$subtotal = array_sum(array_map(function($item) {
					return $item['item_total'];
				}, $return_order_data));

				$total_count = array_sum(array_map(function($item) {
					return $item['item_count'];
				}, $return_order_data));

				$tax = array_sum(array_map(function($item) {
					return $item['tax'];
				}, $return_order_data));

				$subtotal 	= number_format_change($subtotal);
				$tax 		= number_format_change($tax);
				$booking_fee= number_format_change(get_booking_fee($subtotal));
				$total 		= number_format_change($subtotal + $tax + $booking_fee + $delivery_fee);

				if($total_count > 0) {
					$data =  array('store_id' => $order_data['store_id'], 'items' => $return_order_data, 'total_price' => $total, 'delivery_fee' => $delivery_fee, 'booking_fee' => $booking_fee, 'tax' => $tax, 'subtotal' => $subtotal, 'total_item_count' => $total_count, 'currency_code'=> session('currency'),);
					Session::put('order_data', $data);
					return $data;
				}else{
					session()->forget('order_data');
					return '';
				}
			}
			catch (\Exception $e) {
				session()->forget('order_data');
				return '';
			}
		}
	}

}

if (!function_exists('schedule_data_update')) {

	function schedule_data_update($update = '') {
		// auto update schedule details
		$schedule_data = session('schedule_data');
		if ($schedule_data) {
			if ($schedule_data['status'] == 'Schedule') {
				$schedule_time = $schedule_data['date'] . ' ' . $schedule_data['time'];
				if (strtotime($schedule_time) < time() || $update != '') {
					$schedule_data = array('status' => 'ASAP', 'date' => '', 'time' => '');
					session::put('schedule_data', $schedule_data);
					$schedule_update = UserAddress::where('user_id', get_current_login_user_id())->default()->first();
					if ($schedule_update) {
						$schedule_update->delivery_time = '';
						$schedule_update->delivery_options = '';
						$schedule_update->order_type = '';
						$schedule_update->save();
					}
				}
			}
		}
	}
}

if (!function_exists('numberFormat')) {

	function numberFormat($amount) {

		return number_format($amount, 2, '.', '');

	}
}

if (!function_exists('priceRatingList')) {

	function priceRatingList() {
		if(session('symbol'))
			$symbol = session('symbol');
		else
			$symbol = Currency::where('code', site_setting('default_currency'))->first()->original_symbol;
		$array[1] = $symbol;
		$array[2] = $symbol . $symbol;
		$array[3] = $symbol . $symbol . $symbol;
		$array[4] = $symbol . $symbol . $symbol . $symbol;
		return $array;

	}
}

if (!function_exists('default_currency_symbol')) {

	function default_currency_symbol() {
		$symbol = Currency::where('code', site_setting('default_currency'))->first()->original_symbol;

		return $symbol;

	}
}

if (!function_exists('check_menu_available')) {

	function check_menu_available($order_id, $date) {

		$timestamp = strtotime($date);

		$day = date('N', $timestamp);
		$time = date('h:i a', strtotime($date));

		$order_item = OrderItem::where('order_id', $order_id)->get();

		$unavailable = [];

		foreach ($order_item as $menu_item) {

			$menu = MenuItem::where('id', $menu_item->menu_item_id)->first();
			if($menu == '') {
				return ['status' => false,'status_message' => __('api_messages.cart.cart_not_available')];
			}

			$store_time = StoreTime::where('day', $day)->where('store_id', $menu->menu->store_id)->where('status', 1)->first();

			if ($store_time) {

				$atleast = MenuTime::where('menu_id', $menu->menu_id)->first();

				$menu_time = MenuTime::where('day', $day)->where('menu_id', $menu->menu_id)->first();

				if ($menu_time) {

					if (strtotime($time) >= strtotime($menu_time->start_time) &&
						strtotime($time) <= strtotime($menu_time->end_time) && $menu->is_visible == 1) {

					} else {

						$unavailable[] = array('id' => $menu_item->id, 'name' => $menu->name, 'order_id' => $menu_item->order_id);
					}
				} else if ($atleast) {

					$unavailable[] = array('id' => $menu_item->id, 'name' => $menu->name, 'order_id' => $menu_item->order_id);

				} else {

					if (isset($store_time)) {
						
						
						if (strtotime($time) >= strtotime($store_time->start_time_for_english) &&
							strtotime($time) <= strtotime($store_time->end_time_for_english) && $menu->is_visible == 1) {

						} else {

							$unavailable[] = array('id' => $menu_item->id, 'name' => $menu->name, 'order_id' => $menu_item->order_id);

						}

					} else {

						$unavailable[] = array('id' => $menu_item->id, 'name' => $menu->name, 'order_id' => $menu_item->order_id);

					}

				}

			} else {

				$unavailable[] = array('id' => $menu_item->id, 'name' => $menu->name, 'order_id' => $menu_item->order_id);

			}
		}

		if (count($unavailable) > 0) {
			$input = array_map("unserialize", array_values(array_unique(array_map("serialize", $unavailable))));
		} else {
			$input = [];
		}

		return $input;
	}
}

if (!function_exists('store_search')) {

	function store_search($user_details, $address_details , $search = '',$service_type = '',$delivery_type = '',$language ='en',$page = '',$cateogry = '')
	{
		$latitude = isset($address_details['latitude']) ? $address_details['latitude'] : '';
		$longitude = isset($address_details['longitude']) ? $address_details['longitude'] : '';
		if($latitude == "" && $longitude =='' )
		{
			return response()->json(
				[
					'status_message' => "Success",
					'status_code' => '1',
					'total_page' => 0,
					'current_page' => (int) 1,
					'category' => null,
					'count' => 0,
				]
			);
		}
		if($search == 'All')
		{
			$store = Store::where(function ($query) {
				$query->with(['store_cuisine', 'cuisine']);
			})->orWhereHas('store_menu', function ($query){
				$query->WhereHas('menu_category', function ($query){
					$query->with('menu_item');
				});
			})
			->UserStatus()
			->location($latitude, $longitude)
			->whereHas('store_time')
			->where('service_type',$service_type);
		}	
		else{
			
			$store = Store::where(function ($q) use ($search,$language,$cateogry) {
				if($cateogry){
					$q->whereHas('store_cuisine',function ($q) use ($search,$language,$cateogry) {
						$q->where('cuisine_id',$cateogry);
					});
				}
				$q->where(function($q) use ($search,$language) { 
					$q->where(function($q) use ($search,$language) { 
						$q->whereHas('store_cuisine',function ($q) use ($search,$language) {
							$q->WhereHas('cuisine',function ($q) use ($search,$language) {
								if($language =='en') {
										$q->where('name', 'like', '%' . $search . '%');
								}
								else {
										$q->whereHas('language_cuisine',function($q) use ($search,$language) {
											$q->where('name', 'like', '%' . $search . '%')->where('locale',Session::get('language'));
										});
								}
							});
						})
						->orWhereHas('store_menu', function ($q) use ($search) {
							$q->WhereHas('menu_category', function ($q) use ($search) {
								$q->WhereHas('menu_item',function ($q) use ($search) {
									$q->where('name', 'like', '%' . $search . '%')
									->orWhereHas('language_menu',function($q) use ($search) {
										$q->where('name', 'like', '%' . $search . '%')
										->where('locale',Session::get('language'));
									});
								});
							});
						});
					})->orWhere('name', 'like', '%' . $search . '%');
				});
			})
			->UserStatus()
			->location($latitude, $longitude)
			->whereHas('store_time')
			->where('service_type',$service_type);

			// logger($store->toSql());
		}
		
		if(isset($delivery_type) && $delivery_type != '' )
		{
			if($delivery_type != 'both'){
				$store = $store->Where('delivery_type', 'like', '%' . $delivery_type . '%');
			}
		}
		$store = $store->paginate(PAGINATION);

		$total_page = $store->lastPage();

		$user = Store::where(function ($query) {
			$query->with(['store_cuisine', 'store_time']);
		})
		->whereIn('id', $store->pluck('id'))
		->get();

		$user = $user->map(
			function ($item) use ($user_details) {
				$store_cuisine = $item['store_cuisine']->map(
					function ($item) {
						return $item['cuisine_name'];
					}
				)->toArray();
			
				$return_data = [
					// 'order_type' => $order_type,
					// 'delivery_time' => $delivery_time,
					'store_id' => $item['id'],
					'name' => $item['name'],
					'category' => implode(',', $store_cuisine),
					'banner' => $item['banner'],
					'min_time' => $item['convert_mintime'],
					'max_time' => $item['convert_maxtime'],
					'store_rating' => $item['review']['store_rating'],
					'price_rating' => $item['price_rating'],
					'average_rating' => $item['review']['average_rating'],

					'status' => $item['status'],
					'store_open_time' => $item['store_time']['start_time'],
					'store_closed' => $item['store_time']['closed'],
					'store_next_time' => $item['store_next_opening'],
					'delivery_type' => $item['delivery_type'],
					'store_offer' => $item['store_offer']->map(
						function ($item) {
							return [
								'title' => $item->offer_title,
								'description' => $item->offer_description,
								'percentage' => $item->percentage,

							];
						}
					),
				];
				if ($user_details) {
					$return_data['wished'] = $item->wishlist($user_details->id, $item['id']);
				}
				return $return_data;
			}
		);

		return response()->json(
			[
				'status_message' => "Success",
				'status_code' => '1',
				'total_page' => $total_page,
				'current_page' => (int) $page,
				'category' => $user,
				'count' => $user->count(),
			]
		);
	}
}

if (!function_exists('user_address_details')) {
	function user_address_details() {
		if (auth()->guard('web')->user()) {
			$user_details = auth()->guard('web')->user();
			$user = User::where('id', $user_details->id)->first();
			if ($user->user_address) {
				return list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type, 'delivery_time' => $delivery_time) =
				collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_time'])->toArray();
			} else {
				$session = Session::all();
				$session['order_type'] = $session['schedule_data']['status'];
				$session['delivery_time'] = $session['schedule_data']['status'] == 'Schedule' ? $session['schedule_data']['date'] . ' ' . $session['schedule_data']['time'] : '';
				return $session;
			}
		} else {
			$session = Session::all();
			$session['latitude'] =  isset($session['latitude']) ? $session['latitude']:'';
			$session['longitude'] =  isset($session['longitude']) ? $session['longitude']:'';
			$session['order_type'] =  $session['schedule_data']['status'];
			$session['delivery_time'] = $session['schedule_data']['status'] == 'Schedule' ? $session['schedule_data']['date'] . ' ' . $session['schedule_data']['time'] : '';
			return $session;
		}
	}
}

if (!function_exists('menu_category')) {
	function menu_category($key) {
		$data['most_popular'] = Cuisine::Active()->where(function($q){
				$q->where('most_popular', 1);
			})
			->where('service_type',session('service_type'))
			->get();
		$data['recommended'] = Cuisine::Active()->where(function($q){
				$q->where('is_top', '1');
			})
			->where('service_type',session('service_type'))
			->get();
		return $data[$key];

	}
}
if (!function_exists('penality')) {

	function penality($order_id) {

		$penality_amount = 0;

		$user = User::find(get_current_login_user_id());
		$order = Order::find($order_id);
		$penality = Penality::where('user_id', get_current_login_user_id())->first();

		if ($penality) {

			if ($penality->remaining_amount != 0) {

				$penality_amount = $penality->remaining_amount;

				$penality_apply_order = PenalityDetails::where('order_id', $order_id)->first();

				if ($penality_apply_order) {

					if ($user->type == 0) {

						$penality_apply_order->previous_user_penality = $penality_amount;

					} else if ($user->type == 1) {

						$store_total = $order->subtotal + $order->tax - $order->store_commision_fee;

						if ($penality_amount >= $store_total) {

							$penality_apply_order->previous_store_penality = $store_total;

							$remaining_penality = $penality_amount - $store_total;

							//penality table

							$penality->remaining_amount = $remaining_penality;
							$penality->paid_amount = $penality->paid_amount + $store_total;
							$penality->save();

							$penality_amount = $store_total;

						} else {

							$penality_apply_order->previous_store_penality = $penality_amount;

							//penality table
							$penality->remaining_amount = 0;
							$penality->paid_amount = $penality->paid_amount + $penality_amount;
							$penality->save();
						}

					} else {
						$penality_apply_order->previous_driver_penality = $penality_amount;
					}

				} else {

					$penality_apply_order = new PenalityDetails;

					if ($user->type == 0) {

						$penality_apply_order->previous_user_penality = $penality_amount;

					} else if ($user->type == 1) {

						$store_total = $order->subtotal + $order->tax - $order->store_commision_fee;

						if ($penality_amount >= $store_total) {

							$penality_apply_order->previous_store_penality = $store_total;

							$remaining_penality = $penality_amount - $store_total;

							//penality table

							$penality->remaining_amount = $remaining_penality;
							$penality->paid_amount = $penality->paid_amount + $store_total;
							$penality->save();

							$penality_amount = $store_total;

						} else {

							$penality_apply_order->previous_store_penality = $penality_amount;

							//penality table

							$penality->remaining_amount = 0;
							$penality->paid_amount = $penality->paid_amount + $penality_amount;
							$penality->save();
						}

					} else {
						$penality_apply_order->previous_driver_penality = $penality_amount;
					}

					$penality_apply_order->order_id = $order_id;

				}

				$penality_apply_order->save();

			}

		}

		return $penality_amount;

	}
}
if (!function_exists('revertPenality')) {

	function revertPenality($order_id) {

		//Revert Penality amount if exists

		$order = Order::find($order_id);

		$revert_penality = PenalityDetails::where('order_id', $order->id)->first();
		if ($revert_penality) {
			$user_penality = Penality::where('user_id', $order->user_id)->first();
			if ($user_penality) {

				$user_penality->remaining_amount = $revert_penality->previous_user_penality + $user_penality->remaining_amount;
				$user_penality->paid_amount = abs($revert_penality->previous_user_penality - $user_penality->paid_amount);

				$user_penality->save();

				if ($order->total_amount != 0 && $order->payment_type == 0) {

					if ($order->total_amount >= $revert_penality->previous_user_penality) {

						$order->total_amount = $order->total_amount - $revert_penality->previous_user_penality;
						$order->save();

					}

				}
			}
			// store penality
			$store_user_id = get_store_user_id($order->store_id);
			$store_penality = Penality::where('user_id', $store_user_id)->first();
			if ($store_penality) {

				$store_penality->remaining_amount = $revert_penality->previous_store_penality + $store_penality->remaining_amount;
				$store_penality->paid_amount = abs($revert_penality->previous_store_penality - $store_penality->paid_amount);

				$store_penality->save();
			}
			$store_owe_amount = StoreOweAmount::where('user_id', $store_user_id)->first();
			// if ($store_owe_amount) {
			// 	$store_owe_amount->amount = $store_owe_amount->amount ;
			// 	$store_owe_amount->save();
			// } 
			// else
			// {
			// }
			// Driver penality
			if ($order->driver_id) {
				$get_driver_user_id = get_store_user_id($order->driver_id);
				$owe_amount = DriverOweAmount::where('user_id', $get_driver_user_id)->first();
				if ($owe_amount) {
					$owe_amount->amount = $owe_amount->amount + $revert_penality->previous_driver_penality;
				} else {
					$owe_amount = new DriverOweAmount;
					$owe_amount->amount = $revert_penality->previous_driver_penality;
				}
				$owe_amount->save();
			}
			$revert_penality->delete();
		}

	}
}

//session clear for menu's
if (!function_exists('session_clear_all_data')) {
	function session_clear_all_data() {
		$session_data = session('order_data');
		$user_id = get_current_login_user_id();

		if ($user_id) {
			$order = Order::where('user_id', $user_id)->status('cart')->first();
			if($order == '') {
				return 'failed';
			}

			$order_items = OrderItem::where('order_id', $order->id)->get();
			foreach ($order_items as $key => $order_item) {
				$order_item_modifiers = OrderItemModifier::where('order_item_id',$order_item->id)->get();
				foreach($order_item_modifiers as $modifier_item) {
					OrderItemModifierItem::whereIn('order_item_modifier_id',[$modifier_item->id])->delete();
					$modifier_item->delete();
				}
				$order_item->delete();
			}

			$order->order_delivery()->delete();
			$order->delete();
			return 'success';
		}
		
		session()->forget('order_data');

		return 'success';
	}
}

/**
 * Get Langugage Code
 *
 * @return String $lang_code 
 */
if (!function_exists('getLangCode')) {

	function getLangCode()
	{
		$language = Language::whereValue(session('language'))->first();

		if($language) {
			$lang_code = $language->value;
		}
		else {
			$lang_code = Language::where('default_language',1)->first()->value;
		}
		return $lang_code;
	}
}

/**
 * Check if a string is a valid timezone
 *
 * @param string $timezone
 * @return bool
 */
if (!function_exists('isValidTimezone')) {
	function isValidTimezone($timezone)
	{
		return in_array($timezone, timezone_identifiers_list());
	}
}

/**
 * File Get Content by using CURL
 *
 * @param  string $url  Url
 * @return string $data Response of URL
 */
if (!function_exists('file_get_contents_curl')) {

	function file_get_contents_curl($url)
	{
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;
	}
}

/**
 * Check Given Request is from API or not
 *
 * @return Boolean
 */
if (!function_exists('isApiRequest')) {

	function isApiRequest()
	{
	    return request()->segment(1) == 'api';
	}
}

/**
 * Convert underscore_strings to camelCase (medial capitals).
 *
 * @param {string} $str
 *
 * @return {string}
 */
if (!function_exists('snakeToCamel')) {
	function snakeToCamel ($str) {
	  return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
	}
}

/**
 * Check Current Environment
 *
 * @return Boolean true or false
 */
if (!function_exists('isLiveEnv')) {
	function isLiveEnv($environments = [])
	{
		if(count($environments) > 0) {
			array_push($environments, 'live');
			return in_array(env('APP_ENV'),$environments);
		}
		return env('APP_ENV') == 'live';
	}
}

/**
 * Check Current Environment
 *
 * @return Boolean true or false
 */
if (!function_exists('canDisplayCredentials')) {
	function canDisplayCredentials()
	{
		// return env('APP_ENV') == 'live';
		return env('SHOW_CREDENTIALS','false') == 'true';
	}
}

/**
 * get protected String or normal based on env
 *
 * @param {string} $str
 *
 * @return {string}
 */
if (!function_exists('protectedString')) {
    
    function protectedString($str) {
        if(isLiveEnv()) {
            return substr($str, 0, 1) . '****' . substr($str,  -4);
        }
        return $str;
    }
}

if ( ! function_exists('updateEnvConfig')) {
    function updateEnvConfig($envKey, $envValue)
    {
    
       $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        try {
	        $str .= "\n";
            $keyPosition = strpos($str, "{$envKey}=");
            $endOfLinePosition = strpos($str, "\n", $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

            if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                $str .= "{$envKey}={$envValue}\n";
            }
            else {
                $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
            }
            $str = substr($str, 0, -1);
    		file_put_contents($envFile, $str);
        }
        catch (\Exception $e) {
        	// \Log::error($e->getMessage());
        }
    }
}

/**
 * Convert Given Array To Object
 * 
 * @return Object
 */
if (!function_exists('arrayToObject')) {
	function arrayToObject($arr)
	{
		$arr = Arr::wrap($arr);
		return json_decode(json_encode($arr));
	}
}


/**
 * Generate Apple Client Secret
 *
 * @return String $token
 */
if (!function_exists('getAppleClientSecret')) {
	function getAppleClientSecret()
    {
        $key_file = public_path(site_setting('apple_key_file'));
        $algorithmManager = new AlgorithmManager([new ES256()]);
        $jwsBuilder = new JWSBuilder($algorithmManager);
        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode([
                'iat' => time(),
                'exp' => time() + 86400*180,
                'iss' => site_setting('apple_team_id'),
                'aud' => 'https://appleid.apple.com',
                'sub' => site_setting('apple_service_id'),
            ]))
            ->addSignature(JWKFactory::createFromKeyFile($key_file), [
                'alg' => 'ES256',
                'kid' => site_setting('apple_key_id')
            ])
            ->build();

        $serializer = new CompactSerializer();
        $token = $serializer->serialize($jws, 0);
        
        return $token;
    }
}


/**
 * Get a Apple Login URL
 *
 * @return URL from Apple Login  API
 */

if (!function_exists('getAppleLoginUrl')) {
	function getAppleLoginUrl()
	{
		$params = [
			'response_type' 	=> 'code',
			'response_mode' 	=> 'form_post',
			'client_id' 		=> site_setting('apple_service_id'),
			'redirect_uri' 		=> url('apple_callback'),
			'state' 			=> bin2hex(random_bytes(5)),
			'scope' 			=> 'name email',
		];

		$authorize_url = 'https://appleid.apple.com/auth/authorize?'.http_build_query($params);

		return $authorize_url;
	}
}



if (!function_exists('checkStoreDelieryType')) {
	function checkStoreDelieryType($storeId,$deliverytype) {
		$deliveryTyparr = Store::where('id',$storeId)->select('delivery_type')->first();
		$deliveryTyparr = explode(",",$deliveryTyparr->delivery_type);
		if (in_array($deliverytype, $deliveryTyparr)) {
			return true;
		}
	}
}


/**
 * Process CURL With POST
 *
 * @param  String $url  Url
 * @param  Array $params  Url Parameters
 * @return string $data Response of URL
 */
if (!function_exists('curlPost')) {

	function curlPost($url,$params)
	{
		$curlObj = curl_init();

		curl_setopt($curlObj,CURLOPT_URL,$url);
		curl_setopt($curlObj,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curlObj,CURLOPT_HEADER, false); 
		curl_setopt($curlObj,CURLOPT_POST, count($params));
		curl_setopt($curlObj,CURLOPT_POSTFIELDS, http_build_query($params));    
		curl_setopt($curlObj, CURLOPT_HTTPHEADER, [
	        'Accept: application/json',
	        'User-Agent: curl',
	    ]);
		$output = curl_exec($curlObj);

		curl_close($curlObj);
		return json_decode($output,true);
	}
}


/**
 * Process CURL With POST
 *
 * @param  String $url  Url
 * @param  Array $params  Url Parameters
 * @return string $data Response of URL
 */
if (!function_exists('getDefautlPaymentMethod')) {

	function getDefautlPaymentMethod()
	{
		$payment_methods = site_setting('payment_methods');
		$payment = explode(',',$payment_methods);
		return $payment[0];

	}
}

//Serivce Type is active Or Not 

if (!function_exists('checkActiveServiceType')) {
	function checkActiveServiceType($store_id) {
		$store = Store::where('id',$store_id)->first();
		$serviceType = ServiceType::where('id',$store->service_type)->Active();
	 	$service_type = $serviceType->first();	
		return $service_type;
	}
}

if (!function_exists('supports')) {
	function supports($key = '') {
		$data = resolve('supports');
		if (isset($data[$key])) {
			return $data[$key];
		}
	}
}




if (!function_exists('menu_category')) {

	function menu_category($key) {
		$data['most_popular'] = Cuisine::Active()->where(function($q){
				$q->where('most_popular', 1);
			})
			->where('service_type',session('service_type'))
			->get();
		$data['recommended'] = Cuisine::Active()->where(function($q){
				$q->where('is_top', '1');
			})
			->where('service_type',session('service_type'))
			->get();
		return $data[$key];

	}
}


if (!function_exists('replace_promo')) {
	function replace_promo($order_id) {
		$user_promo = UsersPromoCode::where('order_id',$order_id)->update(['order_id' => 0]);
	}
}


if (!function_exists('addwalletAmount')) {
	function addwalletAmount($user_details,$amount,$currency_code, $customerId) {
			$wallet = Wallet::where('user_id', $user_details->id)->first();
			if ($wallet) {
				$amount = $wallet->amount + $amount;
			}
			$user_wallet = Wallet::firstOrNew(['user_id' => $user_details->id]);
			$user_wallet->user_id = $user_details->id;
			$user_wallet->amount = $amount;
			$user_wallet->currency_code = $currency_code;
			$user_wallet->save();
			$payment = new Payment;
			$payment->user_id = $user_details->id;
			$payment->transaction_id = $customerId;
			$payment->amount = $amount;
			$payment->status = 1;
			$payment->type = 1;
			$payment->currency_code = $currency_code;
			$payment->save();
			$wallet_details = Wallet::where('user_id', $user_details->id)->first();
			return $wallet_details;
	}

if (!function_exists('get_kilometer')) {
	function get_kilometer($res_lat,$res_long)
	{	
		$user_location = user_address_details();
		$lat1 = $res_lat;
		$lat2 = $user_location['latitude'];
		$long1 = $res_long;
		$long2 = $user_location['longitude'];
		$result = get_driving_distance($lat1, $lat2, $long1, $long2);
		$km = floor(@$result['distance'] / 1000) . '.' . floor(@$result['distance'] % 1000);
		return ($km < 1 ? 1:$km);
	}

}

if(!function_exists('total_pagination')) {
	function total_pagination($count)
	{	
		return (int) (($count->count() <= PAGINATION) ? '1' : ceil($count->count() /PAGINATION));
	}
}

if(!function_exists('getDriverCurrency')) {
	function getDriverCurrency($code)
	{	
		$session_code = session()->has('driver_currency') ? session('driver_currency'):DEFAULT_CURRENCY; 
		return $session_code==$code ? "selected":'';
	}
}

if(!function_exists('getServiceType')) {
	function getServiceType()
	{	
		$service_type = ServiceType::whereHas('category',function($query){})->active()->pluck('id')->first();
		return $service_type;
	}
}
	


}
