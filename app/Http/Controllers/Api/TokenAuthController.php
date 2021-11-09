<?php

/**
 * TokenAuth Controller
 *
 * @package     GoferEats
 * @subpackage  Controller
 * @category    Datatable Base
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Driver;
use App\Models\Store;
use App\Models\Currency;
use App\Models\User;
use App\Models\VehicleType;
use App\Traits\AddOrder;
use App;
use JWTAuth;
use Session;
use Validator;
use App\Models\Country;
use App\Models\ServiceType;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use App\Traits\FileProcessing;
use App\Http\Controllers\Admin\LocaleFileController;
use App\Models\Language;
use DB;
use App\Models\Support;

class TokenAuthController extends Controller
{
	use AddOrder,FileProcessing;

	/**
	 * User or store or driver Resister
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function register(Request $request) {
		$language = isset($request->language) ? $request->language : "en";
        App::setLocale($language);
		if(isset($request->language)) {
            Session::put('language', $language);
        }	
       
		$rules = array(
			'auth_type'    	=> 'required|in:facebook,google,apple,mobile_number',
			'mobile_number' => 'required_if:auth_type,==,mobile_number|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,1,2',
			'password' => 'required_if:auth_type,==,mobile_number|min:6',
			'first_name' => 'required',
			'last_name' => 'required',
			'country_code' => 'required_if:auth_type,==,mobile_number',
		);

		if(in_array($request->auth_type,['facebook','google','apple'])) {
			$social_signup = true;
			$rules['auth_id'] = 'required';
		}
		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required').'', 
            'password.required_if' => ':attribute '.trans('user_api_language.register.field_is_required').''
        );
		
		
		$attributes = array(
			'mobile_number' => trans('user_api_language.register.mobile_number'),
			'type'=>trans('user_api_language.register.type'),
			'password'=>trans('user_api_language.register.password'),
			'first_name' => trans('user_api_language.register.first_name'),
			'last_name' => trans('user_api_language.register.last_name'),
			'country_code' => trans('user_api_language.register.country_code'),
			//'dob' => trans('api_messages.register.dob'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);
		
		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		if( $request->auth_type == 'mobile_number')
		{
			
			$check_user = User::validateUser($request->type,$request->country_code,$request->mobile_number,$request->email)->count();
			// $auth_column = trans('api_messages.register.email_mobile');
		}
		else
		{
			$auth_column = $request->auth_type.'_id';
			$check_user = User::where('auth_type',$request->auth_type)->where($auth_column,$request->auth_id)->count();
		}
		
		if ($check_user > 0) {
			return response()->json([
				'status_code' => '0',
				'status_for_already' =>'Already you have an account,please login...',
				'status_message' => trans('user_api_language.register.already_account'),
			]);
		}

		$name = html_entity_decode($request->first_name) .'~'. html_entity_decode($request->last_name);

		$user = new User;
		$user->mobile_number = $request->mobile_number;
		$user->name = $name;
		$user->user_first_name = html_entity_decode($request->first_name);
		$user->user_last_name = html_entity_decode($request->last_name);
		$user->type = $request->type;
		if( $request->auth_type == 'mobile_number')
		{
			$user->password = bcrypt($request->password);
		}
		$user->country_code = Country::whereCode($request->country_code)->value('phone_code');
		$user->country_id 	= Country::whereCode($request->country_code)->value('id');
		$user->currency_code = DEFAULT_CURRENCY;
		$user->email = $request->email;
		$user->auth_type = $request->auth_type;
		if($request->auth_type == 'facebook') {
            $user->facebook_id	 = $request->auth_id;
        }
        else if($request->auth_type == 'google') {
            $user->google_id	 = $request->auth_id;
        }
        else if($request->auth_type == 'instagram') {
        	$user->instagram_id	 = $request->auth_id;
        }
        else if($request->auth_type == 'apple') {
            // $auth_column = 'apple_id';
            $user->apple_id	 = $request->auth_id;
        }
		
		
		$user->status = $request->type == 2 ? $user->statusArray['vehicle_details'] : $user->statusArray['active'];
		$user->language = $language;

		if(isset($request->dob)){
			if($request->dob !=''){
				$user->date_of_birth = date('Y-m-d',strtotime($request->dob));
			}
		}

		$user->save();
		Log::info("Curl_ Result".json_encode($request->all()));
		$user_image = urldecode($request->user_image);
		if($request->user_image)
		{
			$this->fileSave('social_login', $user->id, $request->user_image, '1');
		}
		

		if($request->type == 2) {
			$driver = new Driver;
			$driver->user_id = $user->id;
			$driver->save();
		}

		if( $request->auth_type == 'mobile_number'){
			$credentials = $request->only('mobile_number', 'password', 'type');
		}
		else
		{
			$credentials = $request->only('auth_type', 'auth_id', 'type');
			// $token = JWTAuth::fromUser($user);
		}
		// dd($token);


		try {
			$token = JWTAuth::fromUser($user);
			// if (!$token = JWTAuth::attempt($credentials)) {
			// 	return response()->json(['error' => 'invalid_credentials']);
			// }
		} catch (JWTException $e) {
			return response()->json(['error' => 'could_not_create_token']);
		}

		// if no errors are encountered we can return a JWT
		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if($request->order) {
			$request['token'] = $token;
			$data =  $this->add_cart_item($request,0);
			if($data['status_code'] != 1) {
				return response()->json([
					'status_code' => $data['status_code'],
					'status_message' => $data['status_message'],
				]);
	       	}
		}

		// get firebase token
        $firbase = resolve("App\Services\FirebaseService");
        $firbase_token = $firbase->createCustomToken($user->email);
        $return_data['firbase_token'] = $firbase_token;
        	
		$register = array(
			'status_code' 	 => '1',
			'status_message' =>  trans('user_api_language.register.register_success'),
			'access_token'  	=> $token,
			'user_details' 	=> $user,
			'user_data' 	=> $user,
			'vehicle_type' 	=> $vehicle_type,
			'firbase_token' => $firbase_token,
		);

		return response()->json($register);
	}

	public function number_validation(Request $request) {

        $language = $request->language ?? "en";
        App::setLocale($language);
        $otp = '';
        $mobile_number = $request->mobile_number;
		$country_code = Country::whereCode($request->country_code)->value('phone_code');

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,id,type,'.$request->type.',country_code,'.$country_code,
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required'),
          	'mobile_number.unique' => trans('user_api_language.register.mobile_number_already_taken'), 
        );

		$attributes = array(
			'mobile_number' => trans('user_api_language.register.mobile_number'),
			'type'=>trans('user_api_language.register.type'),			
			'country_code' => trans('user_api_language.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules, $messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_for_already' =>2,
                'status_message' => $validator->messages()->first(),
            ]);
		}

		if(site_setting('otp_verification')=='Yes')
			$otp = rand(1000, 9999);

		$message = trans('user_api_language.register.verification_code') . $otp;

		$phone_number = $country_code  . $request->mobile_number;
		if(site_setting('otp_verification')=='Yes')	
			$message_send = sendOtp($phone_number, $message);
		
		return response()->json([
			'status_code' => '1',
			'status'=> true,
			'status_message'=>trans('user_api_language.message_send'),
			'otp' => (string) $otp,
		]);
	}



	public function forgot_password(Request $request)
	{
		$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required').'', 
        );		

		$attributes = array(
			'mobile_number' => trans('user_api_language.register.mobile_number'),
			'type'=>trans('user_api_language.register.type'),			
			'country_code' => trans('user_api_language.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		$country_code = Country::whereCode($request->country_code)->value('phone_code');

		$user = User::where('mobile_number', $mobile_number)->where('type', $request->type)->count();

		if ($user == 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('user_api_language.register.number_not_found'),
			]);
		}

		$otp = rand(1000, 9999);
		$message = trans('user_api_language.register.verification_code') . $otp;
		$phone_number = $country_code . $request->mobile_number;
		$message_send = sendOtp($phone_number, $message);
		
		return json_encode(['status'=>true,'status_message'=>trans('user_api_language.message_send'),'otp' => (string) $otp, 'status_code' => '1']);

	}

	public function reset_password(Request $request)
	{
		$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
			'password' => 'required',
		);

		$messages = array(
            'required'                => ':attribute '.trans('user_api_language.register.field_is_required').'', 
        );
		$attributes = array(
			'mobile_number' => trans('user_api_language.register.mobile_number'),
			'type'=>trans('user_api_language.register.type'),
			'password'=>trans('user_api_language.register.password'),			
			'country_code' => trans('user_api_language.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		$country_code = Country::whereCode($request->country_code)->value('phone_code');	
		
		$user = User::where('mobile_number', $mobile_number)->where('country_code', $country_code)->where('type', $request->type)->first();
		
		if (!isset($user)) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('user_api_language.register.reset_password'),
			]);
		}
		
		$user->password = bcrypt($request->password);
		$user->save();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.register.success'),
		]);
	}

	/**
	 * User Login
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function login(Request $request)
	{
		if(isset($request->language)) {
			$getLanguage = $request->language;
			App::setLocale($request->language);
		} else {
        	$getLanguage = 'en';
            App::setLocale('en');
        }

		$user_id = $request->mobile_number;

		$rules = array(
			'type' => 'required|in:0,1,2',
			'password' => 'required|min:6',
		);

		if($request->type == '1') {
			$rules['email'] = 'required';
			$db_id = 'email';
			$user_id = $request->email;
		} else {
			$rules['mobile_number'] = 'required|regex:/^[0-9]+$/|min:6';
			$rules['country_code'] = 'required';
			$db_id = 'mobile_number';
		}

		$messages = array(
            'required' => ':attribute '.trans('api_messages.register.field_is_required'), 
            'regex' => ':attribute '.trans('api_messages.register.format_field')
        );

		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),
			'password'=>trans('api_messages.register.password'),			
			'email'=>trans('api_messages.register.email'),
			'country_code' => trans('api_messages.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);
		
		if($validator->fails()) {
			return [
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ];
		}

		if($request->type == '1') {
			$credentials = $request->only('email', 'password', 'type');
		} else {

			$credentials = $request->only('mobile_number', 'password', 'type');
			$country_code =  $country_code = Country::whereCode($request->country_code)->value('phone_code');
        	$credentials['country_code'] = $country_code;

		}
		// dd($credentials);
		try {
			if(!$token = JWTAuth::attempt($credentials)) {
				return response()->json([
					'status_code' => '0',
					'status_message' => trans('api_messages.register.credentials_not_right'),
				]);
			}
		} catch(JWTException $e) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.could_not_create_token'),
			]);
		}

		$user_check = $user = User::where($db_id, $user_id)->where('type', $request->type)->first();

		$original_currency_code = $user->getAttributes()['currency_code'];
			
		if(!$original_currency_code) {
			$user->currency_code = $user->currency_code->code;
			$user->save();
		}

		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if($user_check->status_text == 'inactive') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.account_deactivated'),
			]);
		}

		if($request->order) {
			$request['token'] = $token;
			$data =  $this->add_cart_item($request,0);
			if($data['status_code'] != 1) {
				return response()->json([
					'status_code' => $data['status_code'],
					'status_message' => $data['status_message'],
				]);
	       	}
		}

		$firbase = resolve("App\Services\FirebaseService");
        $firbase_token = $firbase->createCustomToken($user_check->email);

		$return_data = array(
			'status_code' 		=> '1',
			'status_message' 	=> trans('api_messages.register.login_success'),
			'access_token' 		=> $token,
			'user_data' 		=> $user_check,
			'vehicle_type'		=> $vehicle_type,
			'firbase_token'     => $firbase_token,
		);

		if($request->type == 2)
		{	
			Driver::where('user_id',$user_check->id)->update(array(
                         'status'=> '1',
				));
		}
		if ($request->type == 1) {
			$store_name = Store::where('user_id', $user_check->id)->first();
			$return_data['store_name'] = $store_name->name;
			$return_data['store_id'] = $store_name->id;
		}

		//change Language
		User::where($db_id, $user_id)->update(['language' => $getLanguage]);
		return response()->json($return_data);
	}

	/**
	 * Update Device ID and Device Type
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function update_device_id(Request $request)
	{
		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;
		$default_currency_symbol = html_entity_decode($default_currency_symbol);
		
		$user_details = JWTAuth::parseToken()->authenticate();
		$rules = array(
			'type' => 'required|in:0,1,2',
			'device_type' => 'required',
			'device_id' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
                'default_currency_code' => $default_currency_code,
                'default_currency_symbol'=>$default_currency_symbol,
            ]);
		}

		$user = User::where('id', $user_details->id)->first();

		if(isset($user->currency_code)) {
			$default_currency_code = $user->currency_code->code;
			$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
		}

		if ($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.invalid_credentials'),
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]);
		}

		User::whereId($user_details->id)->update(['device_id' => $request->device_id, 'device_type' => $request->device_type]);

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.register.update_success'),
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
		]);
	}

	public function get_profile() {

		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();

		if ($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.invalid_credentials'),
			]);
		}

		$name = explode(' ', $user->name);

		$data = [array('key'=>trans('api_messages.register.first_name'),'value'=> $user->user_first_name), array('key'=>trans('api_messages.register.sur_name'),'value' => $user->user_last_name), array('key'=> trans('api_messages.register.phone_number') ,'value' => $user->mobile_number), array('key' => trans('api_messages.register.email_address') ,'value' => $user->email)];

		$payment_methods = site_setting('payment_methods');

		$payment_methods = explode(',', $payment_methods);
		
		// $wallet = 0;
		// check availability

		$wallet = in_array("Wallet", $payment_methods) ? '1' : '0';

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.register.profile_success'),
			'user_details' => $user,
			'user_array' => $data,
			'wallet' => $wallet,
		]);
	}

	public function change_mobile(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		$mobile_number = $request->mobile_number;
		$country_code = $request->country_code;

		$country_id 	= Country::whereCode($request->country_code)->value('id');
		$country_code = Country::whereCode($request->country_code)->value('phone_code');	

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,'.$user_details->id.',id,type,'.$request->type.',country_code,'.$country_code,
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$messages = array(
          	 'mobile_number.unique' => trans('user_api_language.register.mobile_number_already_taken'), 
        );

		$attributes = array(
			'mobile_number' => trans('user_api_language.register.mobile_number'),
		);

		$validator = Validator::make($request->all(), $rules, [], $attributes);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		// $user_details = User::validateUser($request->type,$country_code,$request->mobile_number)->count();
		// if ($user_details == 0) {

			$user->mobile_number = $mobile_number;
			$user->country_code = $country_code;
			$user->country_id = $country_id;
			$user->save();

			if ($request->type == 2) {

				$driver = Driver::authUser()->first();
				$user = $driver->user;

				$user = collect($user)->except(['user_image', 'users_image']);

				$user_address = collect($driver->user_address)->except(['id', 'latitude', 'longitude', 'default', 'delivery_options', 'apartment', 'delivery_note', 'type', 'static_map', 'country_code']);

				if (!$user_address->count()) {
					$user_address = collect([
						"user_id" => $driver->user_id,
						"street" => "",
						"area" => "",
						"city" => "",
						"state" => "",
						"postal_code" => "",
						"address" => "",
					]);
				}

				$driver_documents = $driver->documents->flatMap(function($document) {
					return [
						$document->fileTypeArray->search($document->type) => $document->image_name,
					];
				});

				$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name']);

				$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);

				return response()->json([
					'status_message' => trans('api_messages.register.update_success'),
					'status_code' => '1',
					'driver_profile' => $driver_profile,
				]);
			} else {
				return response()->json([
					'status_message' => trans('api_messages.register.update_success'),
					'status_code' => '1',
					'user_details' => $user,
				]);
			}
		// } else {
		// 	return response()->json([
		// 		'status_code' => '0',
		// 		'status_for_already' =>'Already you have an account,please login...',
		// 		'status_message' => trans('user_api_language.register.already_account'),
		// 	]);
		// }
	}

	public function logout(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		// dd($user->id );	
		if ($user->type == '2') {
			$driver = Driver::authUser()->first();
			if ($driver->status == 2) {
				return response()->json([
					'status_code' => '2',
					'status_message' => trans('api_messages.register.complete_your_trip'),
				]);
			}
			Driver::where('id',$driver->id)->update(array(
                         'status'=> '0',
				));
		}

		$user->device_type = '';
		$user->device_id = '';
		$user->save();

		JWTAuth::invalidate($request->token);

		return response()->json([
			'status_code' => '1',
			'status_message' => "Logout Successfully",
		]);
	}

	public function language(Request $request)
    {
    	try {
        	$user_details = JWTAuth::parseToken()->authenticate();
    	}
    	catch(\Exception $e) {
    		return response()->json([
                'status_code' => '0',
                'status_message' => $e->getMessage(),
            ]);
    	}

    	$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(			
			'type' => 'required|in:0,1,2',			
		);
		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required').'', 
            
        );
		$attributes = array(			
			'type'=>trans('user_api_language.register.type'),			
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$user= User::find($user_details->id);
		$user->language =$request->language;
		$user->type = $request->type;
		$user->save();
            
        return response()->json([
	        'status_code'       =>  '1',
	        'status_message'    => trans('user_api_language.register.update_success'),
        ]);
	}

	public function common_data(Request $request)
    {

    	try {
        	$user_details = JWTAuth::parseToken()->authenticate();
    	}
    	catch(\Exception $e) {
    		return response()->json([
                'status_code' => '0',
                'status_message' => $e->getMessage(),
            ]);
    	}
    	
        $status_code = '1';
        $status_message = trans('user_api_language.listed_successfully');
        $stripe_publish_key = site_setting('stripe_publish_key');

        $firebase = resolve("App\Services\FirebaseService");

        $firebase_token = $firebase->createCustomToken($user_details->email.' - '.$user_details->user_type);
        
        return response()->json(compact(
    		'status_code',
    		'status_message',
    		'stripe_publish_key',
    		'firebase_token'
    	));
    }

    /**
	* Display Country List
	*
	* @param Get method request inputs
	* @return @return Response in Json
	*/
	public function country_list(Request $request)
	{

		$country_data = Country::select(
		'id as country_id',
		'name as country_name',
		'code as country_code'	
		)->where('stripe_country','1')->get();

		$country_list = $country_data->map(function($data){
            return [
                'country_id'    => $data->country_id,
                'country_name'  => $data->country_name,
                'country_code'  => $data->country_code,
                'currency_code' => $this->countryCurrency($data->country_code),
           		 ];
	        });

		return response()->json([
			'status_code' => '1',
			'status_message' =>  __('user_api_language.country_listed') ,
			'country_list' => $country_list,
			]);
	}


	public  function countryCurrency($data)
	{
		$currency_code=[];
		$data1 = Country::getCurrency();
		foreach ($data1 as $key => $value) {
			if($key == $data)
			{
				$currency_code = $value  ;
			}
		}
		return $currency_code;
	}
	
	public function serviceType(Request $request)
	{
		if($request->token)
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$language = $user_details->language;
		}
		else{
			$language = $request->language ?? "en";
		}
        App::setLocale($language);
       
		$service_type = ServiceType::active()->get();
		foreach ($service_type as $key => $value) {
			$service[$key]['id'] = $value->id;
			$service[$key]['service_name'] = $value->service_name;
			$service[$key]['has_addon'] = $value->has_addon;
			$service[$key]['service_image'] = $value->mobile_service_image;
		}
		return response()->json([
			'status_code' => '1',
			'status_message' =>  __('user_api_language.service_type_listed'),
			'service_type' => $service,
			]);
	}

	public function GetCurrencyLists() {

		$user_details = JWTAuth::parseToken()->authenticate();
        
        $lists = Currency::active()->get()->toArray();

        $currencies = array();
        foreach($lists as $list) {
        	if($list['code']==$user_details->currency_code->code) {
        		$list['default_currency'] = '1';
        	} else {
        		$list['default_currency'] = '0';
        	}
        	$currencies[] = $list;
        }

        $status_code = '1';
        $status_message =  __('user_api_language.listed_successfully');
        return response()->json(compact('status_code','status_message','currencies'));
    }

    	/**
	 * Update Currency
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function UpdateCurrency(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$rules = array(
			'currency_code' => 'required'
		);

		$validator = Validator::make($request->all(), $rules);

		if($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
		}
		
		$user = User::where('id', $user_details->id)->first();
		// dd($user);
		if($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' =>__('user_api_language.invalid_request'),
			]);
		}
		$currency_code = $request->currency_code;
		User::whereId($user_details->id)->update(['currency_code' => $currency_code]);
		// Wallet::where('user_id',$user_details->id)->update(['currency_code' =>$currency_code]);	
		if($user_details->type=='1')
			Store::where('user_id', $user_details->id)->update(['currency_code' => $currency_code]);
		
		$from = $user->currency_code->code;
		$to = $currency_code;
		$amount = $request->amount;
		$converted_amount = 0;
		
		if($amount)
			$converted_amount = currencyConvert($from,$to,$amount);
		
		return response()->json([
			'status_code' => '1',
			'status_message' => __('user_api_language.register.update_success'),
			'amount' => $converted_amount,
		]);
	}

	public function currency_conversion(Request $request)
    {
    	$user_details   = JWTAuth::parseToken()->authenticate();
    	$rules = array(
			'amount' => 'required'
		);

		$validator = Validator::make($request->all(), $rules);
		if($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
		}

		$user = User::where('id', $user_details->id)->first();
		if($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('user_api_language.register.invalid_credentials'),
			]);
		}
    	$currency_code  = $user_details->currency_code->code;
        $payment_currency = PAYPAL_CURRENCY_CODE;
        $price = floatval(str_replace(',', '',$request->amount)); 
        $converted_amount = currencyConvert($currency_code,$payment_currency,$price);

        $customer_id = $user_details->id.$user_details->mobile_number;
        $gateway = resolve('braintree_paypal');
        try {
            $customer = $gateway->customer()->find($customer_id);
        }
        catch(\Exception $e) {
            try {
                $newCustomer = $gateway->customer()->create([
                    'id'        => $customer_id,
                    'firstName' => $user_details->first_name,
                    'lastName'  => $user_details->last_name,
                    'email'     => $user_details->email,
                    'phone'     => $user_details->phone_number,
                ]);

                if(!$newCustomer->success) {
                    return response()->json([
                        'status_code' => '0',
                        'status_message' => $newCustomer->message,
                    ]);
                }
                $customer = $newCustomer->customer;
            }
            catch(\Exception $e) {
                if($e instanceOf \Braintree\Exception\Authentication) {
                    return response()->json([
                        'status_code' => '0',
                        'status_message' => __('user_api_language.register.auth_failed'),
                    ]);
                }
                return response()->json([
                    'status_code' => '0',
                    'status_message' => $e->getMessage(),
                ]);
            }
        }

        $bt_clientToken = $gateway->clientToken()->generate([
            "customerId" => $customer->id
        ]);

        return response()->json([
            'status_code'    => '1',
            'status_message' => __('user_api_language.amount_converted'),
            'currency_code'  => $payment_currency,
            'amount'         => $converted_amount,
            'braintree_clientToken' => $bt_clientToken,
        ]);
    }

    public function socialSignup(Request $request)
    {

    	$rules = array(
            'auth_type'   => 'required|in:facebook,apple,google',
            'auth_id'     => 'required',
        );
        
    	$validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
        }

        if($request->auth_type == 'facebook') {
            $auth_column = 'facebook_id';
        }
        else if($request->auth_type == 'google') {
            $auth_column = 'google_id';
        }
        else if($request->auth_type == 'apple') {
            $auth_column = 'apple_id';
        }

        $user = User::where($auth_column,$request->auth_id)->first();
        
        if($user == '') {
            return response()->json([
                'status_code'    => '2',
                'status_message' => 'New User',
                'auth_type'      =>$request->auth_type,
                'auth_id'        =>$request->auth_id,
            ]);
        }

        $token = JWTAuth::fromUser($user);

        if(isset($request->language)) {
			$getLanguage = $request->language;
			App::setLocale($request->language);
		} else {
        	$getLanguage = 'en';
            App::setLocale('en');
        }

        $original_currency_code = $user->getAttributes()['currency_code'];
			
		if(!$original_currency_code) {
			$user->currency_code = $user->currency_code->code;
			$user->save();
		}

		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if ($request->type == 1) {
			$store_name = Store::where('user_id', $user->id)->first()->name;
			$return_data['store_name'] = $store_name;
		}

		//change Language
		User::where('id', $user->id)->update(['language' => $getLanguage]);

	
        $return_data = array(
            'status_code'       => '1',
            'status_message'    => trans('user_api_language.register.login_success'),
            'access_token'      => $token,
            'user_data' 		=> $user
        );

        // $user_data = $user;

        return response()->json($return_data);
    }


 	public function apple_callback(Request $request) 
    {
        $client_id = site_setting('apple_service_id');

        $client_secret = getAppleClientSecret();
       
        $params = array(
            'grant_type' 	=> 'authorization_code',
            'code' 		 	=> $request->code,
            'redirect_uri'  => url('api/apple_callback'),
            'client_id' 	=> $client_id,
            'client_secret' => $client_secret,
        );
        
        $curl_result = curlPost("https://appleid.apple.com/auth/token",$params);

        // Log::info("Curl_ Result".json_encode($curl_result));
        if(!isset($curl_result['id_token'])) {
        // if(!is_null($curl_result['id_token'])) {
            $return_data = array(
                'status_code'       => '0',
                'status_message'    => $curl_result['error'],
            );

            return response()->json($return_data);
        }

        $claims = explode('.', $curl_result['id_token'])[1];
        $user_data = json_decode(base64_decode($claims));

        $user = User::where('apple_id', $user_data->sub)->first();

        if($user == '') {
            $return_data = array(
                'status_code'       => '1',
                'status_message'    => trans('user_api_language.register.new_user'),
                'email_id'          => optional($user_data)->email ?? '',
                'apple_id'          => $user_data->sub,
            );

            return response()->json($return_data);
        }

        $token = JWTAuth::fromUser($user);

        if(isset($request->language)) {
			$getLanguage = $request->language;
			App::setLocale($request->language);
		} else {
        	$getLanguage = 'en';
            App::setLocale('en');
        }

        $original_currency_code = $user->getAttributes()['currency_code'];
			
		if(!$original_currency_code) {
			$user->currency_code = $user->currency_code->code;
			$user->save();
		}

		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if ($request->type == 1) {
			$store_name = Store::where('user_id', $user->id)->first()->name;
			$return_data['store_name'] = $store_name;
		}

		//change Language
		User::where('id', $user->id)->update(['language' => $getLanguage]);

        $return_data = array(
            'status_code'       => '2',
            'status_message'    => 'Login Successfully',
            'email_id'       	=> optional($user_data)->email ?? '',
            'apple_id'          => $user_data->sub,
            'access_token'      => $token,
        );
        $user_array = $user->toArray();
        return response()->json(array_merge($return_data,$user_array));
    }

    public function appleServiceId()
    {
    	$service_id = site_setting('apple_service_id');
    	$support = Support::where('status','Active')->get();		
		$supportArr = $support->map(function($value){
			return [
				'id'	=> $value->id,
				'name'	=> $value->name,
				'link'	=> $value->link ?? '',
				'image' =>$value->support_image ?? '',
			];
		});
    	$return_data = array(
            'status_code'       => '1',
            'status_message'    => __('user_api_language.apple_service_id'),
            'apple_service_id'  => $service_id,
            'apple_login'			=> site_setting('apple_login') == 'Yes' ? true : false,
			'facebook_login'		=> site_setting('facebook_login') == 'Yes' ? true : false, 
			'google_login'			=> site_setting('google_login') == 'Yes' ? true : false, 
			'otp_enabled'			=> site_setting('otp_verification') == 'Yes' ? true : false, 
			'support'				=> $supportArr ?? [],
			// 'force_update'		=>  site_setting('force_update') == 'Yes' ? true : false,
        );
        return response()->json(array_merge($return_data));
    }


    public function apiContent(Request $request,LocaleFileController $lang_controller)
    { 
        
            $event = Language::select('value As key','name AS Lang',DB::raw('(CASE WHEN language.value = "ar" OR language.value="fa" THEN true ELSE false END) AS is_rtl'))->where('status','Active')->get();
            $default= Language::where('default_language',1)->first();

            if(strtolower($request->user_type) == 'user')
            {
                $user_lan_file = 'user_api_language';
            }
            elseif(strtolower($request->user_type) == 'store')
            {
                $user_lan_file = 'store_api_language';
            }   
            else
            {
                $user_lan_file = 'driver_api_language';                
            }
            
            $user_language = $request->language ?? 'en';
            $data = $lang_controller->get_language_data($user_language,$user_lan_file);
             return response()->json([
                'status_code' => '1',
                'status_message' => __('user_api_language.listed_successfully'),
                'default_language'=>  $default->value,
                'current_language' =>$user_language,
                'language' =>$event,
                
             ]+$data);
             

    }

 	public function emailValidation(Request $request) {

        $language = $request->language ?? "en";
        App::setLocale($language);

		$rules = array(
			'email' => 'required|regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/|min:6|unique:user,email,NULL,id,type,'.$request->type,
			// 'email' => 'required|email',
			'type' => 'required|in:0,2',
		);

		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required'), 
            'email.unique' => trans('user_api_language.register.email_already_taken'),
            'email.regex' => trans('user_api_language.gofereats.the_email_you_entered_is_invalid'),
        );

		$attributes = array(
			'mobile_number' => trans('user_api_language.register.email'),
			'type'=>trans('user_api_language.register.type'),			
		);

		$validator = Validator::make($request->all(), $rules, $messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}
		return response()->json([
			'status_code' => '1',
			'status_message'=> trans('user_api_language.sucess'),
		]);
	}
   
}
