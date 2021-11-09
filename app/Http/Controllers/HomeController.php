<?php

/**
 * HomeController
 * @package     GoferEats
 * @subpackage  Controller
 * @category    Datatable Base
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;
use App\Models\Country;
use App\Models\HomeSlider;
use App\Models\Pages;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Traits\FileProcessing;
use App\Models\Wallet;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;

use App\Models\Store;
use App\Models\StoreCuisine;
use App\Models\StoreDocument;
use App\Models\StorePreparationTime;
use App\Models\StoreTime;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;

use App\Models\Driver;

use App\Models\Order;
use App\Models\DriverRequest;

use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\DriverOweAmount;
use App\Models\StoreOweAmount;

use App\Models\ServiceType;

use App;
use Auth;
use Session;
use Validator;
use Artisan;
use DB;
use App\Models\Cuisine;

class HomeController extends Controller {

	use FileProcessing;
	//home page function

	public function home() {

		if (session::get('schedule_data') == null) {			
			$schedule_data = array('status' => 'ASAP', 'date' => '', 'time' => '');
			session::put('schedule_data', $schedule_data);
		}
		$data['slider'] = HomeSlider::where('status', 1)->type('user')->get();
		session::forget('order_data'); // Remove socail login signup datas
		if (session('location')) {
			return redirect()->route('feeds');
		} else {
			$service = ServiceType::active()->get();
			$data['service_type'] =  $service;
			return view('home/home', $data);
		}
	}


	// new home
	public function newHome()
	{
		if (session::get('schedule_data') == null) {			
			$schedule_data = array('status' => 'ASAP', 'date' => '', 'time' => '');
			session::put('schedule_data', $schedule_data);
		}	
		$service = ServiceType::select('id','service_name','service_description')->active()->first();
		return view('home/newhome',compact('service'));
	}

	public function feeds(Request $request)
	{
		if(is_null(session::get('service_type'))){
			$service_type =	getServiceType();
			session::put('service_type',$service_type);
			return redirect()->route('newhome');
		}
		session::put('delivery_type','delivery');
		$service_banner_image = ServiceType::where('id',session::get('service_type'))->firstOrFail();
		$data['service_banner_image'] = $service_banner_image->service_type_banner_image;
		$data['categories'] = Cuisine::select('id','name')->Active()
			->where(function($q){
				$q->where('is_top', 0)->orWhereNull('is_top');
			})->where('service_type',session::get('service_type'))
			->get()->toArray();
		if (session::get('schedule_data') == null) {
			$schedule_data = array('status' => 'ASAP', 'date' => '', 'time' => '');
			session::put('schedule_data', $schedule_data);
		}
		return view('home/newcategory',$data);
	}


	public function newDetails(Request $request)
	{
		$data['user_details'] = auth()->guard('web')->user();
		$store_id = $request->store_id;
		$store = Store::where('id',$store_id)->userstatus()->first();
		if(is_null($store)){
			flash_message('danger', 'Store Not Available');
			return redirect()->route('newhome');
		}
		//Store Time
		$data['store_time_data'] = 0;
		if (isset($store->store_all_time[0])) {
			$data['store_time_data'] = $store->store_all_time[0]->is_available;
		}
		
		/*Get Menu, Menu Category and Menu Item Start Here*/
		$data['menu_details'] = Menu::where('store_id', $store_id)->pluck('name','id');
		$menu_id = array_keys($data['menu_details']->toArray());
		$data['menu_category'] = MenuCategory::whereIn('menu_id', $menu_id)->whereHas('menu_item')->pluck('name','id');
		$data['menu_item'] = MenuItem::select('id','menu_id','menu_category_id','name','description','price','currency_code')->with(['menu_category'])
						->whereHas('menu', function ($query)  {
							$query->store(request()->store_id);
						})
						->where('status','1')
						->orderBy('menu_id', 'ASC')
    					->orderBy('menu_category_id', 'ASC')
			 			->paginate(10);
		$data['total_page'] =  $data['menu_item']->lastPage();
		$collect = collect($data['menu_item']->items());
		$data['menu_item'] = $collect->groupBy('menu_category_id');
		// dd($data['menu_item']);
		/*Get Menu, Menu Category and Menu Item End Here*/

		$data['store_time'] = StoreTime::where('store_id',$store_id)->get();
		
		/*Get Categories Only*/
		$data['currency_code'] = $data['user_details']->currency_code->code ?? session('currency');
		/*End Categories*/
		$data['store'] = $store;
		
		// Order details
		$order_detail = '';					 
		$order_detail = get_user_order_details($store_id, $data['user_details']->id ?? null);
		// dd($order_detail);
		$cart_store_id = $order_detail ? $order_detail['store_id'] : '';
		if ($cart_store_id) {
			$data['other_store_detail'] = Store::findOrFail($cart_store_id);
		}
		$data['other_store'] = ($cart_store_id != '' && $cart_store_id != $store_id) ? 'yes' : 'no';
		if ($data['other_store'] == 'yes') {
			$order_detail = '';
		}
		$data['order_detail_data'] = $order_detail;
		    // @if($store->store_all_time[0]->is_available == 1)
		return view('home/newdetails',$data);
	}

	public function get_menu_items()
	{
		$menu_item = MenuItem::select('id','menu_id','menu_category_id','name','description','price','currency_code')->with('menu_category')
						->whereHas('menu', function ($query)  {
							$query->store(request()->store_id);
						})
						->where('status','1');
				if(request()->category_id)
					$menu_item = $menu_item->where('menu_category_id',request()->category_id);
				if(request()->key)
					$menu_item = $menu_item->where('name', 'like', '%' . request()->key . '%');
		$data['menu_item'] = $menu_item->orderBy('menu_id', 'ASC')
	    					->orderBy('menu_category_id', 'ASC')
				 			->paginate(10);
			$total_page =  $data['menu_item']->lastPage();
			$collect = collect($data['menu_item']->items());
			return['data'=> $collect->groupBy('menu_category_id'),'total_page' => $total_page ];
	}


	

	//login page

	public function login() {
		session::put('service_type',1);
		$user = auth()->guard('web')->user();
		$data['country'] = Country::Active()->get();
		$data['code'] = (canDisplayCredentials()) ? '1' : '1';
		$data['phone'] = (canDisplayCredentials()) ? '7778885551' : '';
		$data['password'] = (canDisplayCredentials()) ? 'trioangle' : '';
		if ($user) {
			return redirect()->route('feeds');
		}
		session::forget('user_data');
		return view('home/login', $data);
	}

	//signup form

	public function signup() {

		$user = auth()->guard('web')->user();
		if($user) {
			return redirect()->route('feeds');
		}
		$this->view_data['country'] = Country::Active()->get();
		session()->put('code', (canDisplayCredentials()) ? '1' : '1');
		return view('home/signup', $this->view_data);
	}

	//static page

	public function static_page() {
		$this->view_data['page'] = Pages::where('url', request()->page)->where('status', 1)->firstOrFail();
		return view('home/static_page', $this->view_data);
	}

	public function store_signup_data() {
		
		$this->view_data['phone_number'] = request()->phone_number;
		$this->view_data['country_code'] = request()->country_code;
		$this->view_data['first_name'] = request()->first_name;
		$this->view_data['last_name'] = request()->last_name;
		$this->view_data['email_address'] = request()->email_address;
		$this->view_data['password'] = request()->password ; 
		$this->view_data['verification_code'] = random_num(4);
		$check_user = User::validateUser('0',$this->view_data['country_code'] ,$this->view_data['phone_number'])->count();	
		
		if ($check_user > 0) {
			$data['message'] = trans('messages.driver.this_number_already_exists');
			return json_encode(['success' => 'no', 'data' => $data]);
		}
		
		session()->put('user_name', $this->view_data['first_name'] . '~' . $this->view_data['last_name']);
		session()->put('phone_number', $this->view_data['phone_number']);
		session()->put('country_code', $this->view_data['country_code']);
		session()->put('email_address', $this->view_data['email_address']);
		session()->put('password', $this->view_data['password']);
		session()->put('verification_code', $this->view_data['verification_code']);
		session()->put('key_id', request()->key_id );
		session()->put('auth_type', request()->auth_type );
		session()->put('country_id',request()->country_id);		
		session()->save();
		$message = 'Your verification code is ' . $this->view_data['verification_code'];

		$phone_number = $this->view_data['country_code'] . $this->view_data['phone_number'];
		logger("phone number ".$phone_number);
		if(canDisplayCredentials()) {
			return json_encode(['success' => 'true', 'data' => 'message send']);
		}
		
		$message_send = sendOtp($phone_number, $message);

		if ($message_send['status'] == 'Success') {
			return json_encode(['success' => 'true', 'data' => $message_send]);
		} else {
			return json_encode(['success' => 'no', 'data' => $message_send]);
		}
	}

	//otp conformation

	public function signup_confirm(Request $request) {

		$mobile_number = session('phone_number') ? session('phone_number') : request()->phone_number;
		$country_code = session('phone_code_1') ?? request()->country_code;
		$email = request()->email_address ?? session('email_address');
		$check_user = User::validateUser('0',$country_code ,$mobile_number,$email )->count();
		
		if ($check_user > 0) {
			flash_message('danger', trans('messages.driver.this_number_already_have_account_please_login'));
			return redirect()->route('login');
		} 
		else {
			$this->view_data['first_name'] = session('first_name');
			$this->view_data['last_name'] = session('last_name');
			$this->view_data['phone_number'] = session('phone_number');
			$this->view_data['country_code'] = $country_code;
			$this->view_data['email_address'] = session('email_address');
			$this->view_data['password'] = session('password');
			$this->view_data['country_id'] = session('country_id') ;
			return view('home/signup2', $this->view_data);

		}
	}

	//store data in database

	public function store_user_data() 
	{
		$mobile_number = session('phone_number') ? session('phone_number') : request()->phone_number;
		$country_code = request()->country_code ?? session('country_code');

		$email =  request()->email_address ?? session('email_address');
		$check_user = User::validateUser('0',$country_code ,$mobile_number)->count();
		if ($check_user > 0) {
			$message_send['message'] = trans('messages.driver.this_number_already_exists');
			return json_encode(['success' => 'no', 'data' => $message_send]);
		}
		$uname = request()->first_name . '~' . request()->last_name;
		$user = new User;
		$user->mobile_number = session('phone_number') ? session('phone_number') : request()->phone_number;
		$user->name = session('user_name') ? session('user_name')  : $uname;
		$user->type = 0;
		if(request()->auth_type == 'mobile_number' || request()->auth_type == ''){
			if(site_setting('otp_verification') == 'Yes'){
				$password = bcrypt(session('password')) ?? bcrypt(request()->password) ;
			}
			else{
				$password = bcrypt(request()->password);
			}
			$user->password = $password;
			$user->currency_code = session('currency') ?? DEFAULT_CURRENCY;
		}
		$country_id = request()->country_id ?? session('country_id') ;
		$country_cod_value= Country::where('id',$country_id)->first();
		$user->country_code = $country_cod_value->phone_code ;
		$user->email = $email;
		$user->status = "1";
		$user->country_id = request()->country_id ?? session('country_id');
		if(request()->auth_type != 'mobile_number' && request()->auth_type!= ''){
			$source = request()->auth_type.'_id';
			$user->$source = request()->key_id;
			$user->auth_type = request()->auth_type;
		}
		$user->save();

		if(request()->user_image){
			$this->fileSave('social_login',$user->id,request()->user_image,'1');
		}

		if (Auth::guard()->loginUsingId($user->id)){
			if(Session::get('order_data')){	
				add_order_data();
			}
			return redirect('/');
		}
		else if (Auth::guard()->attempt(['mobile_number' => session('phone_number'), 'password' => session('password'),'type'=>0 ])) {
			$user = auth()->guard('web')->user();
			$currency_code = $user->currency_code->code ?? session::get('currency') ;
			$currency_symbol = $user->currency_code->symbol ?? session::get('symbol');
			$original_currency_code = $user->getAttributes()['currency_code'];
			if(!$original_currency_code){
				$user->currency_code = $user->currency_code->code;
				$user->save();
			}
			Session::put('currency', $currency_code);
			Session::put('symbol', $currency_symbol);
			if(Session::get('order_data')){	
				Session::put('url.intended', 'home');
			}
			$intended_url = session('url.intended');
			if ($intended_url) {
				//create new order use session values
				add_order_data();
				return redirect()->route($intended_url); // Redirect to intended url page
			} else {
				return redirect()->route('/'); // Redirect to search page
			}
		} else {
				// get firebase token
		        $firbase = resolve("App\Services\FirebaseService");
		        $firbase_token = $firbase->createCustomToken($user->email);
				return redirect()->route('login'); // Redirect to login page
			}
	}
	
	//forgot password

	public function forgot_password() {
		if (request()->method() != 'POST') {
			Session::forget('password_code');
			Session::forget('reset_user_id');
			return view('home/forgot_password');
		} else {

			$rules = array(
				'email' => 'required|email',
			);

			$niceNames = array(
				'email' => trans('messages.driver.email'),
			);

			$validator = Validator::make(request()->all(), $rules);
			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {

				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {

				$email = request()->email;

				$user_details = User::where(['type' => 0, 'email' => $email])->first();
				if (is_null($user_details)) {
					return back()->withErrors(['email' => trans('messages.profile_orders.no_account_for_this_email_you_need').' <a href="' . route('signup') . '">'. trans('messages.profile.register').'</a>'])->withInput();
				}

				if(site_setting('otp_verification') == 'Yes'){
					if (session::get('password_code') == null) {
						session::put('reset_user_id', $user_details->id);
						$user = User::find($user_details->id);
						$user->otp = random_num(4);
						$user->save();
						$check_email = otp_for_forget_user($email, $user->otp);
						if($check_email['status'] =='false'){
							flash_message("danger",$check_email['error']);
							return back();
						}
					}
					return redirect()->route('otp_confirm');
				}
				else
				{
					$this->view_data['user_details'] = $user_details;
					$this->view_data['user_id'] = $user_details->id;
					return view('home/reset_password', $this->view_data);
				}
			}
		}
	}

	//otp confirm from mail

	public function otp_confirm() {
		if (request()->method() == 'POST') {

			$rules = array(
				'code_confirm' => 'required',
			);
			// validation custom messages
			$niceNames = array(
				'code_confirm' => trans('admin_messages.code'),
			);

			$validator = Validator::make(request()->all(), $rules);
			$validator->setAttributeNames($niceNames);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {

				$code = request()->code_confirm;
				$user_id = request()->user_details;

				$user = User::find($user_id);

				if ($user->otp != $code) {
					return back()->withErrors(['code_confirm' => trans('messages.store_dashboard.code_is_incorrect')])->withInput(); // Form calling with Errors and Input values
				}
				return redirect()->route('reset_password');
			}
		} else {
			$user_id = session::get('reset_user_id');
			$user_details = User::findOrFail($user_id);
			$this->view_data['user_details'] = $user_details;

			return view('home/forgot_password2', $this->view_data);

		}
	}

	//reset password

	public function reset_password() {

		if (request()->method() == 'POST') {

			$rules = array(
				'password' => 'required|min:6|same:confirmed',
				'confirmed' => 'required',

			);

			$niceNames = array(
				'password' => trans('messages.profile.password'),
				'confirmed' => trans('messages.store.confirm_password'),
			);

			$messages = array(
				'min' => trans('validation.min.numeric', ['attribute' => trans('messages.profile.password'),'min' => 6]),
				'same' => trans('validation.confirmed', ['attribute' => trans('messages.profile.password')]),
			);

			$validator = Validator::make(request()->all(), $rules,$messages);
			$validator->setAttributeNames($niceNames);
			if ($validator->fails()) {

				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {
				$user_id = request()->user_id;
				$password = request()->password;

				$user = User::find($user_id);
				$user->password = bcrypt($password);

				$user->save();

				$user_details = User::find($user_id);
				Session::forget('reset_user_id');
				if (Auth::guard()->attempt(['mobile_number' => $user_details->mobile_number, 'password' => $password])) {
					return redirect()->route('newhome');
				} else {

					return redirect()->route('login');
				}
			}
		} else {
			$this->view_data['user_id'] = session::get('reset_user_id');
			User::findOrFail($this->view_data['user_id']);
			return view('home/reset_password', $this->view_data);
		}

	}

	//password change

	public function password_change() {

		$user_id = request()->id;
		$password = request()->password;

		$user = User::find($user_id);
		$user->password = bcrypt($password);

		$user->save();

		$user_details = User::find($user_id);

		if (Auth::guard()->attempt(['mobile_number' => $user_details->mobile_number, 'password' => $password])) {

			return json_encode(['success' => 'true']); // Response for success

		} else {

			return json_encode(['success' => 'none']); // Response for failure
		}

	}

	/**
	 * Set session for Currency & Language while choosing footer dropdowns
	 *
	 */
	public function set_session(Request $request) {
		 if ($request->language) {
			Session::put('language', $request->language);
			App::setLocale($request->language);
			} else if($request->currency) {
			$user_details = auth()->guard('web')->user();
			if(!$user_details) {
				$user_details = auth()->guard('restaurant')->user();
			}
			if($user_details) {
				
				if($user_details->type == 0)
				{
					User::whereId($user_details->id)->update(['currency_code' => $request->currency]);
					Session::put('currency', $request->currency);
					$symbol = Currency::original_symbol($request->currency);
					wallet::where('id',$user_details->id)->update(['currency_code' => $request->currency]);
					Session::put('symbol', $symbol);
					Session::save();
				}
				else
				{	
					Session::put('currency', $request->currency);
					$symbol = Currency::original_symbol($request->currency);
					Session::put('symbol', $symbol);
					Session::save();
				}
			}
			else
			{	
				Session::put('currency', $request->currency);
				$symbol = Currency::original_symbol($request->currency);
				Session::put('symbol', $symbol);
				Session::save();
			}
		}
		return json_encode([
			'success' => trans('api_messages.success'),
			'message' => trans('api_messages.register.update_success'),
		]);
	}

	public function clearLog()
    {
        file_put_contents(storage_path('logs/laravel.log'),'');
    }

    public function showLog()
    {
        $contents = \File::get(storage_path('logs/laravel.log'));
        echo '<pre>'.$contents.'</pre>';
    }

    public function updateEnv(Request $request)
    {
		$requests = $request->all();
		try{
			$valid_env = ['APP_ENV','APP_DEBUG','SHOW_CREDENTIALS','FIREBASE_PREFIX',"IP_ADDRESS"];

			foreach ($requests as $key => $value) {
				$prev_value = getenv($key);
				if(in_array($key,$valid_env)) {
					updateEnvConfig($key,$value);
				}
			}
			return "Data Update Successfully" ;
		}
		catch(\Exception $e) {
				return "Not Updated".$e;
		}
       
    }

    public function setPhoneCode($code)
    {
    	Session::put('phone_code_1', $code);
    }
   
   public function clearRoute(Request $request )
    {
    	if($request->command =='route')
    	{
       	 Artisan::call('route:clear');
    	}
    	else if($request->command == 'cache_clear')
    	{
    		Artisan::call('cache:clear');
    	}
    	else if($request->command == 'view')
    	{
    		Artisan::call('view:clear');
    	}
    }

    public function query_update(Request $request)
	{
		try{
		if($request->type=='insert'){
			if(isset($request->statement)&& $request->statement) {
			         $query = DB::statement($request->statement); 
			         echo '<h1> Statement is Execution Sucesss </h1>';
			         }else{
			          echo '<h1> Statement is Missing </h1>';
			         }         
			}elseif($request->type=='select'){
			$query = DB::select($request->sel);
				dump($query);
			echo '<h1> Statement is Execution Sucesss </h1>';
			}}catch(\Exception $e){
			echo '<h1>'.$e->getMessage().'</h1>';
		}
	}


	public function singleLoad(Request $request)
	{
		try 
		{
			DB::beginTransaction();
			$nameString = $request->key;

			for ($i=1; $i <= $request->record; $i++) {
				
				//User Details Update
				$rand = rand('1111','9999');

				//User
				$getUser = User::find('10001');
				$newUser 				= $getUser->replicate();
				$newUser->name 			= $nameString.'~User '.$rand;
				$newUser->email 		= $nameString.'user'.$rand.'@gmail.com';
				$newUser->country_code 	= '91';
				$newUser->mobile_number = '987654'.$rand;
				$newUser->save();

				//User Addresss
				$getUserAddress = UserAddress::find('3');
				$newUserAddress = $getUserAddress->replicate()->fill([
					 'user_id' 	=> $newUser->id,					
				]);
				$newUserAddress->save();

				//User Payment Method
				$getUserPayment = UserPaymentMethod::find('3');
				$newUserPayment = $getUserPayment->replicate()->fill([
					 'user_id' 	=> $newUser->id,					
				]);
				$newUserAddress->save();


				//Store Details

				//User
				$getStore = User::find('10005');
				$newStore 				 = $getStore->replicate();
				$newStore->name 		 = $nameString.'~Store '.$rand;
				$newStore->email 		 = $nameString.'store'.$rand.'@gmail.com';
				$newStore->country_code  = '91';
				$newStore->mobile_number = '987655'.$rand;
				$newStore->save();

				//User Address
				$getStoreAddress = UserAddress::find('1');
				$newStoreAddress = $getStoreAddress->replicate()->fill([
					 'user_id' 	=> $newStore->id,					
				]);
				$newStoreAddress->save();

				//Store
				$store 		 = Store::find('10001');
				$storeCreate = $store->replicate()->fill([
					 'user_id' 	=> $newStore->id,					
				]);
				$storeCreate->save();

				//Store Cusine
				$getCusine 	= StoreCuisine::find('2');
				$newCusine 	= $getCusine->replicate()->fill([
					 'store_id' => $storeCreate->id,					
				]);
				$newCusine->save();

				//Store Document
				$getDocument 	= StoreDocument::find('24');
				if($getDocument){
					$newDocument 	= $getDocument->replicate()->fill([
						 'store_id' => $storeCreate->id,					
					]);
					$newDocument->save();					
				}

				//Store Preparation Time
				$getPreparationTime = StorePreparationTime::find('1');
				if($getPreparationTime){
					$newPreparationTime = $getPreparationTime->replicate()->fill([
						 'store_id' 	=> $storeCreate->id,
						 'day'			=> '3',				
					]);
					$newPreparationTime->save();
				}

				//Store Time
				$getTime = StoreTime::find('1');
				for ($i=1; $i < 8 ; $i++) { 
					$newTime = $getTime->replicate()->fill([
						 'store_id' => $storeCreate->id,
						 'day'		=> $i,				
					]);
				}
				$newTime->save();

				// //Store Menu
				// $getMenu = Menu::find('5');
				// $newMenu = $getMenu->replicate()->fill([
				// 	 'store_id' => $storeCreate->id,
				// ]);
				// $newMenu->save();

				// //Store Menu Category
				// $getMenuCategory = MenuCategory::find('6');
				// $newMenuCategory = $getMenuCategory->replicate()->fill([
				// 	 'menu_id' => $newMenu->id,
				// ]);
				// $newMenuCategory->save();

				// //Store MenuItem
				// $getMenuItem = MenuItem::find('11');
				// $newMenuItem = $getMenuItem->replicate()->fill([
				// 	 'menu_id' => $newMenu->id,
				// 	 'menu_category_id' => $newMenuCategory->id,
				// ]);
				// $newMenuItem->save();


				//Driver Details Update

				//Driver User
				$getDriver = User::find('10003');
				$newDriver 					= $getDriver->replicate();
				$newDriver->name 			= $nameString.'~Driver '.$rand;
				$newDriver->email 			= $nameString.'driver'.$rand.'@gmail.com';
				$newDriver->country_code 	= '91';
				$newDriver->mobile_number 	= '987656'.$rand;
				$newDriver->save();

				//Driver
				$Driver = Driver::find('10001');
				$driverUpdate = $Driver->replicate()->fill([
					 'user_id' 	=> $newDriver->id,					
				]);
				$driverUpdate->save();

				$this->loadComplete($newUser->id,$storeCreate->id,$driverUpdate->id,$newDriver->id);


			}
			DB::commit();

			return array('status' => 'Completed');

		} 
		catch (\Exception $e) {
			 DB::rollback();
			 return array('error' => $e->getMessage() );
		}
	}

	public function loadComplete($user_id,$store_id,$driver_id,$user_driver_id)
	{
		//Order 
		$getOrder 	 			= Order::find('10012');
		$updateOrder 			= $getOrder->replicate();
		$updateOrder->user_id 	= $user_id;
		$updateOrder->store_id 	= $store_id;
		$updateOrder->driver_id = $driver_id;
		$updateOrder->save();

		//Request
		$getRequest 	= DriverRequest::find('12');
		$updateRequest 	= $getRequest->replicate()->fill([
				'order_id' => $updateOrder->id,
				'driver_id' => $driver_id,
		]);
		$updateRequest->save();


		//OrderDelivery
		$getOrderDelivery = OrderDelivery::find('12');
		$updateOrderDelivery 				= $getOrderDelivery->replicate();
		$updateOrderDelivery->order_id 		= $updateOrder->id;
		$updateOrderDelivery->request_id 	= $updateRequest->id;
		$updateOrderDelivery->driver_id 	= $driver_id;
		$updateOrderDelivery->save();


		//Orde Item
		$getOrderItem = OrderItem::find('8');
		$updateOrderItem 	= $getOrderItem->replicate()->fill([
				'order_id' 	   => $updateOrder->id,
//				'menu_item_id' => $menu_item_id,
		]);
		$updateOrderItem->save();

		//Payment
		$getPayment = Payment::find('4');
		$updatePayment 	= $getPayment->replicate()->fill([
				'user_id' 	=> $user_id,
				'order_id'	=> $updateOrder->id,
		]);
		$updatePayment->save();


		//Payout
		$getPayout = Payout::find('5');
		$updatePayout 	= $getPayout->replicate()->fill([
				'user_id' 	=> $user_id,
				'order_id'	=> $updateOrder->id,
		]);
		$updatePayout->save();

		//DriverOweAmount
		$getOweAmount = DriverOweAmount::find('3');
		$updateOweAmount 	= $getOweAmount->replicate()->fill([
				'user_id' 	=> $user_driver_id,
				'amount'	=> $updateOrder->owe_amount,
		]);
		$updateOweAmount->save();
	}


	//For load checking
	public function orderLoad(Request $request)
	{	
		DB::beginTransaction();
		try 
		{
			if($request->order_id && $request->record)
			{
				$getOrder = Order::find($request->order_id);
				if($getOrder)
				{
					$getOrderDelivery = OrderDelivery::where('order_id',$request->order_id)->first();
					if($getOrderDelivery)
					{
						for ($i=1; $i <= $request->record; $i++) { 
							//Order update
							$newOrder = $getOrder->replicate();
							$newOrder->save();

							//Order Delivery
							$newOrderDelivery = $getOrderDelivery->replicate()->fill([
							    'order_id' => $newOrder->id
							]);
							$newOrderDelivery->save();

							$getOrderItem = OrderItem::where('order_id',$request->order_id)->first();
							if($getOrderItem){
								//Order Item
								$newOrderItem = $getOrderItem->replicate()->fill([
								    'order_id' => $newOrder->id
								]);
								$newOrderItem->save();
							}

							$getPayment = Payment::where('order_id',$request->order_id)->first();
							if($getPayment){
								$getPayment = $getPayment->replicate()->fill([
									'order_id' => $newOrder->id
								]);
								$getPayment->save();
							}


							$getPayout = Payout::where('order_id',$request->order_id)->first();
							if($getPayout){
								$updatePayout 	= $getPayout->replicate()->fill([
										'order_id'	=> $newOrder->id,
								]);
								$updatePayout->save();								
							}

							//DriverOweAmount
							if($newOrderDelivery->driver){
								$getOweAmount = DriverOweAmount::where('user_id',$newOrderDelivery->driver->user_id)->first();
								if($getOweAmount){
									$updateOweAmount 	= $getOweAmount->replicate();
									$updateOweAmount->save();
								}								
							}

							if($newOrder->store){
								$getStoreOweAmount = StoreOweAmount::where('user_id',$newOrder->store->user_id)->first();
								if($getStoreOweAmount){
									$updateStoreOweAmount 	= $getStoreOweAmount->replicate();
									$updateStoreOweAmount->save();
								}
							}
						}
					}
				}
				DB::commit();
				return ['Order User Details' => array('user_id' => $getOrder->user_id, 'store_id' => $getOrder->store_id, 'driver_id' => $getOrderDelivery->driver_id)];
			}
			else{
				return ['error' => 'Invalid Request'];
			}

		} 
		catch (\Exception $e) 
		{
			 DB::rollback();
			 return ['error' => $e->getMessage() ];
		}
	}

	public function setServiceType(Request $request)
	{
		session::put('service_type',$request->service_type);
		session::save();
	}	
}
