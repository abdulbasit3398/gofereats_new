<?php

/**
 * UserController
 *
 * @package     GoferEats
 * @subpackage  Controller
 * @category    User
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */


namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileType;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Traits\FileProcessing;
use Auth;
use Illuminate\Http\Request;
use Session;
use Storage;
use Validator;
use App\Models\PromoCode;
use App\Models\Wallet;
use App\Models\Order;

class UserController extends Controller {
	use FileProcessing;
	/**
	 * Email users Login authentication
	 *
	 * @param array $request    Post method inputs
	 * @return redirect     to dashboard page
	 */


	public function __construct()
	{
		$this->fb = resolve('App\Http\Helper\FacebookHelper');
	}


	public function authenticate() {

		// dd(request()->all());

		$rules = array(
			'phone_number' => 'required|numeric',
			'password' => 'required',
		);

		$niceNames = array(
			'phone_number' => trans('messages.driver.phone_number'),
			'password' => trans('messages.profile.password'),
		);

		$validator = Validator::make(request()->all(), $rules);
		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {

			return back()->withErrors($validator)->withInput()->with('error_code', 5);
			// Form calling with Errors and Input values
		} else {

			$users = User::where('mobile_number',request()->phone_number)->where('type',0)->first();

	        // Check user is active or not
	        if(is_null($users)){
	        	flash_message('danger', trans('messages.store_dashboard.invalid_credentials'));
				return back();
	        } else if($users->status == 0) {     
	            flash_message('danger', trans('messages.store_dashboard.youre_disabled_by_admin_please_contact_admin'));
				return back();
	        }
			if (Auth::guard('web')->attempt(['mobile_number' => request()->phone_number, 'password' => request()->password, 'type' => '0' ,'country_code' => request()->country, 'status' => '1'])) {
				$user = auth()->guard('web')->user();
				$currency_code = $user->currency_code->code ?? session::get('currency') ;
				$currency_symbol = $user->currency_code->symbol ?? session::get('symbol');
				$original_currency_code = $user->getAttributes()['currency_code'];
				if(!$original_currency_code) {
					$user->currency_code = $user->currency_code->code;
					$user->save();
				}
				\Session::put('currency', $currency_code);
				\Session::put('symbol', $currency_symbol);
				if(session::get('order_data'))
				{	
					$intended_url = session::get('url.intended');
				}
				if (isset($intended_url)) {
					add_order_data();
					return redirect()->route($intended_url); 
				} else {
					return redirect()->route('feeds');
				}
			} else {
				flash_message('danger', trans('messages.store_dashboard.invalid_credentials'));
				return back();
			}
		}

	}

	//logout current user

	public function logout() {
		Auth::logout();
		session::forget('order_data');
		session::forget('user_data');
		session::forget('order_detail');
		session::forget('schedule_data');
		session::forget('url.intended');
		session::forget('url.intended');
		session::forget('locate');
		session::forget('location');
		session::forget('city');
		return redirect()->route('login');
	}

	//user profile details

	public function user_profile() {

		$this->view_data['user_details'] = auth()->guard('web')->user();
		$user_address = UserAddress::where('type',2)->where('user_id',$this->view_data['user_details']->id)->first();
		 ;
		$this->view_data['user_address'] =$user_address;
		if($this->view_data['user_details']->auth_type && $this->view_data['user_details']->auth_type != 'mobile_number')
		{	
			$filetype = FileType::where('name', 'social_login')->first();
			$file_image = File::where(['source_id' => $this->view_data['user_details']->id, 'type' => $filetype->id])->first();
			if($file_image){
				if($file_image->source == 2)
					$this->view_data['profile_image'] = url('/') . '/storage/images/user/' . $file_image->name;
				else
					$this->view_data['profile_image'] = $file_image->name;
			}
			else{
					$this->view_data['profile_image'] = url('/') . '/images/user.png';
				}
		}
		else
		{
			$filetype = FileType::where('name', 'user_image')->first();
			if ($this->view_data['user_details']) {
				$file_image = File::where(['source_id' => $this->view_data['user_details']->id, 'type' => $filetype->id])->first();
				if ($file_image != '') {
					$this->view_data['profile_image'] = url('/') . '/storage/images/user/' . $file_image->name;
				} else {
					$this->view_data['profile_image'] = url('/') . '/images/user.png';
				}
			} else {
				$this->view_data['profile_image'] = url('/') . '/images/user.png';
			}
		}	
		// dd($this->view_data);
		return view('user_profile', $this->view_data);
	}

	//user details changes

	public function user_details_store() {

		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'user_address' => 'required',
			// 'user_city' => 'required',
			'user_state' => 'required',
			// 'user_country' => 'required',
		);

		if (request()->profile_photo != null) {
			$rules['profile_photo'] = 'mimes:png,jpeg,jpg';
		}

		// Email login validation custom messages
		$messages = array(
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'user_address' => 'User Address',
			// 'user_city' => 'City',
			'user_state' => 'State',
			// 'user_country' => 'Country',
			'profile_photo' => 'Profile Photo',
		);

		// Email login validation custom Fields name
		$niceNames = array(
			'first_name' => trans('admin_messages.first_name'),
			'last_name' => trans('admin_messages.last_name'),
			'user_address' => trans('messages.profile.user_address'),
			// 'user_city' => 'City',
			'user_state' => 'State',
			// 'user_country' => 'Country',
			// 'profile_photo' => 'Profile Photo',
		);

		$validator = Validator::make(request()->all(), $rules, $messages);
		$validator->setAttributeNames($niceNames);
		// dd($validator->fails());
		if ($validator->fails()) {
			
			return back()->withErrors($validator)->withInput()->with('error_code', 5); // Form calling with Errors and Input values
		} else {
			
			$user_id = auth()->guard('web')->user()->id;
			$user_detail = User::find($user_id);

			if (request()->profile_photo != null) {
				$file = request()->file('profile_photo');
				$file_path = $this->fileUpload($file, 'public/images/user');
				if($user_detail->auth_type && $user_detail->auth_type != 'mobile_number')
				{
					$this->fileSave('social_login', $user_id, $file_path['file_name'], '2');
				}
				else
				{
					$this->fileSave('user_image', $user_id, $file_path['file_name'], '1');
				}
				$original_path = url(Storage::url($file_path['path']));
			}

			$user_detail->name = request()->first_name . '~' . request()->last_name;;
		
			$user_address = UserAddress::where('user_id', $user_id)->first();
			if (!$user_address) {
				$user_address = new UserAddress;
				$user_address->user_id = $user_id;
				$user_address->default = 1;
				$user_address->type = 0;
			}

			$user_detail->save();

			$user_address->address = request()->user_address;

			$user_address->address = request()->user_address;
			$user_address->street = request()->user_street;
			$user_address->city = request()->user_city;
			$user_address->state = request()->user_state;
			$user_address->postal_code = request()->user_postal_code;
			$user_address->country = request()->user_country;
			$user_address->latitude = request()->user_latitude;
			$user_address->longitude = request()->user_longitude;
			$user_address->type  = 2;
			$user_address->save();
			flash_message('success', trans('admin_messages.updated_successfully'));
			return back();

		}
	}

	//user payment details

	public function user_payment() {

		$this->view_data['user_details'] = $user_details = auth()->guard('web')->user();
		$this->view_data['payment_details'] = UserPaymentMethod::where('user_id', $this->view_data['user_details']->id)->first();
		$filetype = FileType::where('name', 'user_image')->first();
		if ($this->view_data['user_details']) {
			$file_image = File::where(['source_id' => $this->view_data['user_details']->id, 'type' => $filetype->id])->first();

			if ($file_image != '') {
				$this->view_data['profile_image'] = url('/') . '/storage/images/user/' . $file_image->name;
			} else {
				$this->view_data['profile_image'] = url('/') . '/images/user.png';
			}
		} else {
			$this->view_data['profile_image'] = url('/') . '/images/user.png';
		}
		$this->view_data['promo'] = UsersPromoCode::wherehas('promo_code')->where('order_id','')->where('user_id', $this->view_data['user_details']->id)->get();
		
		$promo_id =  $this->view_data['promo']->count() > 0 ? $this->view_data['promo'][0]->promo_code_id  :''; 
		$this->view_data['promo_code'] =  isset($promo_id) ? PromoCode::whereid($promo_id)->value('code') : '' ;
		$this->view_data['paypal'] = $this->view_data['stripe'] = 0;
		// get payment methods based on service type
		$payment_methods = site_setting('payment_methods');
		$payment_methods = explode(',', $payment_methods);
		// check availability
		if(in_array('Paypal',$payment_methods)) {
			$this->view_data['paypal'] = 1;
			$default_payment = 2;
		}
		if(in_array('Stripe',$payment_methods)) {
			$this->view_data['stripe'] = 1;
			$default_payment = 1;
		}

		if($user_details){
			$stripe_payment = resolve('App\Repositories\StripePayment');
			$this->view_data['paypal_access_token'] = $stripe_payment->PaypalClientToken($user_details);

		} else {
			$this->view_data['paypal_access_token'] = 0;
		}
		$this->view_data['default_payment'] = $default_payment;
		return view('user_payment', $this->view_data);
	}


	public function appleCallback(Request $request) 
    {
        $client_id = site_setting('apple_service_id');

        $client_secret = getAppleClientSecret();

        $params = array(
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => url('apple_callback'),
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        );
        $curl_result = curlPost("https://appleid.apple.com/auth/token",$params);
        logger('Apple Login : '.json_encode($curl_result));

        if(!isset($curl_result['id_token'])) {
           flash_message('danger', trans('messages.profile.authentication_failed'));
		return redirect('login');
            return redirect()->route('login');
        }

        $claims = explode('.', $curl_result['id_token'])[1];
        $user_data = json_decode(base64_decode($claims));
        $user_email = optional($user_data)->email ?? '';

        $user = User::where('apple_id', $user_data->sub)->orWhere('email',$user_email)->first();
        
        if(is_null($user)) {
            $user = array(
                'email' => $user_email,
                'key_id' => $user_data->sub,
                'source' => "apple",
            );
			Session::put('user_data', $user);
            return redirect('signup');
        }

		if ($user->status != 1) {
			return redirect('login');
		}

		if (Auth::guard('web')->loginUsingId($user->id)) {
			//save session cart items
			add_order_data();
			return redirect('/');
		}
		  flash_message('danger', trans('messages.profile.authentication_failed'));
		return redirect('login');
    }
	
    public function facebook_login()
	{	
		return redirect(FB_URL);
	}
	
	public function facebookAuthenticate(Request $request) {
		if ($request->error_code == 200) {
			flash_message('danger', $request->error_description);
			return redirect('login'); // Redirect to login page
		}
		$this->fb->generateSessionFromRedirect(); // Generate Access Token Session After Redirect from Facebook
		$response = $this->fb->getData(); // Get Facebook Response
		$userNode = $response->getGraphUser(); // Get Authenticated User Data
		// $email = ($userNode->getProperty('email') == '') ? $userNode->getId().'@fb.com' : $userNode->getProperty('email');
		$email = $userNode->getProperty('email');
		$fb_id = $userNode->getId();
		$user = User::user_facebook_authenticate($email, $fb_id); // Check 
		if ($user->count() > 0) // If there update Facebook Id
		{
			$user = User::user_facebook_authenticate($email, $fb_id)->first();
			$user->facebook_id = $userNode->getId();
			$user->save(); // Update a Facebook id
			$user_id = $user->id; // Get Last Updated Id
		}
		else // If not create a new user without Password
		{
			$user = User::user_facebook_authenticate($email, $fb_id);
			if ($user->count() > 0) {
				 flash_message('danger', trans('messages.store_dashboard.youre_disabled_by_admin_please_contact_admin'));
				return redirect('login');
			}
			$user = array(
					'first_name'	=> $userNode->getFirstName(),
					'last_name' 	=> $userNode->getLastName(),
					'email' 		=> $email,
					'key_id' 		=> $userNode->getId(),
					'source'		=> "facebook",
					'user_image' 	=> "https://graph.facebook.com/" . $userNode->getId() . "/picture?type=large",
				);
			Session::put('user_data', $user);
			return redirect('signup');
		}
		
		$users = User::where('id', $user_id)->first();
		if (@$users->status != "0") {
			if (Auth::guard()->loginUsingId($user_id)){
				//save session cart items
				add_order_data();
				return redirect('/');
			} else {
				flashMessage('danger', trans('messages.profile.login_failed'));
				return redirect('login');
			}
		} else {
			flash_message('danger', trans('messages.store_dashboard.youre_disabled_by_admin_please_contact_admin'));
			return redirect('login');
		}
	}

	public function addWalletAmountPaypal(Request $request)
	{
		$user_details = auth()->guard('web')->user();
		$amount = $request->amount;
		$paypal_currency = site_setting('paypal_currency_code');
		$user_currency = session::get('currency');
		$converted_amount = currencyConvert($user_currency,$paypal_currency,$amount);
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
                        'status_message' => 'Authentication failed',
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
        // $wallet = addwalletAmount($user_details,$converted_amount,$user_currency,  $bt_clientToken);
		return json_encode(['success' => 'true','amount'=> $converted_amount,'currency' =>$paypal_currency,'braintree_clientToken' =>$bt_clientToken]);
	}

	public function addWalletStripe(Request $request){
		$user_details = auth()->guard('web')->user();
		$amount = $request->amount;
		$ccode = json_decode($request->currency_code);
		$currency_code =  $ccode->code;
		$user_currency = session::get('currency');
		$stripe_payment = resolve('App\Repositories\StripePayment');
		$user_payment_method = UserPaymentMethod::where('user_id', $user_details->id)->first();
		if(is_null($user_payment_method)) {
			return response()->json([
				'status_code' => '0',
				'status_message' => __('messages.driver.something_went_wrong_try_again'),
			]);
		}
		else
		{
			if($request->filled('payment_intent_id')) {
				$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
			}
			else {
					$purchaseData = array(
						"amount" 		=> $amount * 100,
						'currency' 		=> $currency_code,
						'description' 	=> 'Add Wallet Amount for : '.$user_details->order_id,
						"customer" 		=> $user_payment_method->stripe_customer_id,
						'payment_method'=> $user_payment_method->stripe_payment_method,
				      	'confirm' 		=> true,
				      	'off_session' 	=> true,
					);
					$payment_result = $stripe_payment->createPaymentIntent($purchaseData);
			}
			if($payment_result->status == 'requires_action') {
				return response()->json([
					'status_code' 	=> '2',
					'status_message'=> $payment_result->status_message,
					'client_secret'	=> $payment_result->intent_client_secret,
				]);
			}
			else if($payment_result->status != 'success') {
				return response()->json([
					'status_code' 	=> '3',
					'status_message'=> $payment_result->status_message,
				]);
			}
			else{
				$wallet = Wallet::where('user_id', $user_details->id)->first();

				if ($wallet) {
					$amount = $wallet->amount + $amount;
				}
				$user_wallet = Wallet::firstOrNew(['user_id' => $user_details->id]);
				$user_wallet->user_id = $user_details->id;
				$user_wallet->amount = $amount;
				$user_wallet->currency_code = $user_currency;
				$user_wallet->save();
				$wallet_details = Wallet::where('user_id', $user_details->id)->first();
				return response()->json([
					'status_code' => '1',
					'status_message' => 'Successfully',
					'wallet' =>$wallet,
				]);
			}
		}
	}

	public function addWalletAmount( Request $request )
	{
		$user_details = auth()->guard('web')->user();
		$amount = $request->amount;
		$currency_code = $request->currency_code;
		$paypal_currency = site_setting('paypal_currency_code');
		$user_currency = session::get('currency');
	
		$wallet = Wallet::where('user_id', $user_details->id)->first();
		if ($wallet) {
			$amount = $wallet->amount + $amount;
		}
		$user_wallet = Wallet::firstOrNew(['user_id' => $user_details->id]);
		$user_wallet->user_id = $user_details->id;
		$user_wallet->amount = $amount;
		$user_wallet->currency_code = $user_currency;
		$user_wallet->save();
		$wallet_details = Wallet::where('user_id', $user_details->id)->first();
		return $wallet_details;
	}


	public function addWebWallet(Request $request)
	{
		$user_details = auth()->guard('web')->user();
		$is_wallet = $request->isWallet;
		$delivery_type = $request->deliverytype;
		$tips = $request->tips;
		$order = Order::where('id',$request->order_id)->first();
		$cart_order = Order::getAllRelation()->where('user_id', $user_details->id)->status('cart')->first();
		$wallet_amount = use_wallet_amount($order->id, $is_wallet,$tips);
		$data['order_detail_data'] = get_user_order_details($cart_order->store_id, $user_details->id,$delivery_type);
		$data['status'] = 1;
		return $data;	
	}

}
