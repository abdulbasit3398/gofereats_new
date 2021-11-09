<?php

/**
 * UsersController
 *
 * @package     GoferEats
 * @subpackage  Controller
 * @category    User
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderCancelReason;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Store;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UsersPromoCode;
use App\Models\MenuItemModifier;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;
use Auth;
use Session;
use Google_Client;
use App\Traits\FileProcessing;
use App\Models\ServiceType;


class UsersController extends Controller
{
	use FileProcessing;
	
	/**
	 * Store detail page
	 *
	 */
	public function newDetails(Request $request)
	{

		$data['user_details'] = auth()->guard('web')->user();

		//Store and Service Status
		$data['store_id'] = $store_id = $request->store_id;
		$data['store'] = Store::where('id',$store_id)->userstatus()->firstOrFail();
		
		//Store Time
		$data['store_time_data'] = 0;

		if (isset($data['store']->store_all_time[0])) {
			$data['store_time_data'] = $data['store']->store_all_time[0]->is_available;
		
		}

		//Store Cusine
		$data['store_cuisine'] = $data['store']->store_cuisine;
	   
		//Store Menu
		$get_menu 	= Menu::GetMenu()->where('store_id', $store_id)->get();

		//Category
		$category 	= MenuCategory::whereHas('menu_item', function ($query){})
					 ->where('menu_id', $get_menu[0]->id)
					 ->select('id','name')
					 ->get();

		$data['get_menu'] 	= $get_menu->toArray();
		// dd($data['get_menu']);
	    $data['store_menu'] = $this->getMenuCategory($store_id); 
		$data['category'] 	= $category->toArray();

		// Order details
		$order_detail = '';					 
		$order_detail = get_user_order_details($store_id, $data['user_details']->id ?? null);
		$cart_store_id = $order_detail ? $order_detail['store_id'] : '';
		if ($cart_store_id) {
			$data['other_store_detail'] = Store::findOrFail($cart_store_id);
		}
		$data['other_store'] = ($cart_store_id != '' && $cart_store_id != $store_id) ? 'yes' : 'no';
		if ($data['other_store'] == 'yes') {
			$order_detail = '';
		}
		$data['order_detail_data'] = $order_detail;
		return view('home/newdetails',$data);
	
	}

	
	/**
	 * Store detail page
	 *
	 */
	public function detail(Request $request)
	{
		$data['user_details'] = auth()->guard('web')->user();

		//Store and Service Status
		$data['store_id'] = $store_id = $request->store_id;
		$data['store'] = Store::where('id',$store_id)->userstatus()->firstOrFail();
		
		//Store Time
		$data['store_time_data'] = 0;
		if (isset($data['store']->store_all_time[0])) {
			$data['store_time_data'] = $data['store']->store_all_time[0]->is_available;
		}

		//Store Cusine
		$data['store_cuisine'] = $data['store']->store_cuisine;
	   
		//Store Menu
		$get_menu 	= Menu::GetMenu()->where('store_id', $store_id)->get();

		//Category
		$category 	= MenuCategory::whereHas('menu_item', function ($query){})
					 ->where('menu_id', $get_menu[0]->id)
					 ->select('id','name')
					 ->get();

		$data['get_menu'] 	= $get_menu->toArray();
		// dd($data['get_menu']);
	    $data['store_menu'] = $this->getMenuCategory($store_id); 
		$data['category'] 	= $category->toArray();

		// Order details
		$order_detail = '';					 
		$order_detail = get_user_order_details($store_id, $data['user_details']->id ?? null);
		$cart_store_id = $order_detail ? $order_detail['store_id'] : '';
		if ($cart_store_id) {
			$data['other_store_detail'] = Store::findOrFail($cart_store_id);
		}
		$data['other_store'] = ($cart_store_id != '' && $cart_store_id != $store_id) ? 'yes' : 'no';
		if ($data['other_store'] == 'yes') {
			$order_detail = '';
		}
		$data['order_detail_data'] = $order_detail;
		
		return view('detail', $data);
	}


	//Store Menu and Menu Category and Menu Item

	public function getMenuCategory($store_id,$menu_id = '')
	{	
		 $query 	 = Menu::GetMenu()->where('store_id', $store_id);
		 $store_menu =( $menu_id == '') ? $query->limit(1)->get() : $query->where('id',$menu_id)->get();

		 $storeMenu = $store_menu[0]->menu_category->map(function($category){
		    			$menuitem  = $category->all_menu_item()->paginate(20);
				    	$menu_item = $menuitem->map(function ($item) {
				    		return [
								'id' 				=> $item->id,
								'name' 				=> $item->name,
								'description' 		=> $item->description,
								'status' 			=> $item->status,
								'price' 			=> $item->price,
								'menu_item_image' 	=> $item->menu_item_image,
								'offer_price' 		=> $item->offer_price,
							];
				    	})->toArray();
						return [
							'id' 			=> $category->id,
							'name' 			=> $category->name,
							'menu_item' 	=> $menu_item,
							'total_item' 	=> $menuitem->lastPage(),
						];
					});

		return $storeMenu->toArray();
	}

	//menu category detail
	public function menu_category_details(Request $request)
	{
		$menu_id = $request->menu_id;
		$store_id = $request->store_id;
		//Category
		$category 	= MenuCategory::whereHas('menu_item', function ($query){})
					 ->where('menu_id', $menu_id)
					 ->select('id','name')
					 ->get();
		//Menu
		$get_menu 	= Menu::GetMenu()->where('id', $menu_id )->first();			 
		$store_menu = $this->getMenuCategory($store_id,$menu_id);
		return json_encode(['menu_category' => $category,'store_menu' => $store_menu,'current_menu'=>$get_menu]);
	}


	public function get_category_item(Request $request)
	{
		$menuitem = MenuItem::where('menu_category_id',$request->category_id)->paginate(20);
		$menu_item = $menuitem->map(function ($item) {
				    	return [
							'id' 				=> $item->id,
							'name' 				=> $item->name,
							'description' 		=> $item->description,
							'status' 			=> $item->status,
							'price' 			=> $item->price,
							'menu_item_image' 	=> $item->menu_item_image,
							'offer_price' 		=> $item->offer_price,
						];
					})->toArray();
		return json_encode(['menu_item' => $menu_item]);
	}


	//session clear for menu's
	public function session_clear_data()
	{
		$result = session_clear_all_data();
		return ['status' => ($result == 'success') ? true : false];
	}

	//menu item detail
	public function menu_item_detail(Request $request)
	{
		$item_id = $request->item_id;
		$menu_item = MenuItem::with('menu_item_modifier.menu_item_modifier_item')->find($item_id);
		$menu_detail = $menu_item->toArray();
		$menu_detail['menu_item_status'] = $menu_item->menu->menu_closed;
		$menu_detail['menu_closed_status'] = $menu_item->menu->menu_closed_status;
		return json_encode(['menu_item' => $menu_detail]);
	}


	//orders store in session
	public function orders_store(Request $request)
	{
		$menu_data = $request->menu_data;
		$item_count = $request->item_count;
		$item_notes = $request->item_notes;
		$item_price = $request->item_price;
		$individual_price = $request->individual_price;
		$store_id = $request->store_id;

		$order_array = [];
		$count = $item_count;

		if (session('order_data') != null) {
			$order_array = session('order_data');
		}

		$order_data = array('menu_data' => $menu_data, 'store_id' => $store_id, 'item_notes' => $item_notes, 'item_count' => $item_count, 'item_price' => $item_price, 'individual_price' => $individual_price);

		array_push($order_array, $order_data);

		session(['order_data' => $order_array]);

		return json_encode(['last_pushed' => $order_data, 'all_order' => session('order_data')]);
	}

	//orders remove from session
	public function orders_remove(Request $request)
	{
		$order_data = $request->order_data;
		$user_details = auth()->guard('web')->user();
		if (!$user_details) {
			session()->forget('order_data');
			session(['order_data' => $order_data]);
			$order_data = get_user_order_details();
			return json_encode(['order_data' => $order_data]);
		}
		
		$order_item_id = array_column($order_data['items'], 'order_item_id');
		$order = OrderItem::where('order_id', $order_data['order_id'])->whereNotIn('id', $order_item_id)->get();

		foreach ($order as $key => $value) {
				$remove_order_item = OrderItemModifier::where('order_item_id',[$value->id])->get();
				foreach($remove_order_item as $modifier_item) {
					$remove_order_item_modifer = OrderItemModifierItem::whereIn('order_item_modifier_id',[$modifier_item->id])->delete();
				}
				OrderItemModifier::where('order_item_id',[$value->id])->delete();
			}
			OrderItem::where('order_id', $order_data['order_id'])->whereNotIn('id', $order_item_id)->delete();
		$order_data = get_user_order_details($order_data['store_id'], $user_details->id,$request->delivery_type);

		return json_encode(['order_data' => $order_data]);
	}

	public function removeItems($order_item_id)
	{
		$user_details = auth()->guard('web')->user();
		$order = Order::where('user_id',$user_details->id)->where('status','cart')->first();
		$order_items = OrderItem::where('order_id',$order->id)->whereNotIn('id',$order_item_id)
		->get()->map(function($items){
			$items->order_item_modifier->map(function($modifier){
				$modifier->order_item_modifier_item()->delete();
			});
			$items->order_item_modifier()->delete();
			$items->delete();
		});

	}

	public function orders_change(Request $request) {

		$delivery_type = $request->delivery_type ?? 'delivery';
		$order_item_id = $request->order_item_id;
		$order_data = $request->order_data;
		$is_wallet = $request->isWallet ;
		$user_details = auth()->guard('web')->user();
		if(!$user_details) {
			session()->forget('order_data');
			session(['order_data' => $order_data]);
			
			$order_data = get_user_order_details();
			return json_encode(['order_data' => $order_data]);
		}

		$OrderItemId = array_column($order_data['items'], 'order_item_id');
		$this->removeItems($OrderItemId);
		foreach ($order_data['items'] as $order_item) {	
			if ($order_item['order_item_id'] == $order_item_id) {
				$update_item = OrderItem::with('order_item_modifier.order_item_modifier_item')->find($order_item_id);
				$menu = MenuItem::find($update_item['menu_item_id']);				
				foreach($update_item->order_item_modifier as $item) {
					$update_item_modifiers = OrderItemModifier::find($item->id);
					$modifier = $item->order_item_modifier_item;
					$update_modifier_price = 0;
					foreach ($modifier as $modifier_item) {
						$modifier_item->count = $modifier_item->default_count * $order_item['item_count'];
						$update_modifier_price += number_format_change((float)$modifier_item->price * (float)$modifier_item->count);
						$modifier_item->save();

					}
					$update_item_modifiers->modifier_price = $update_modifier_price;
					$update_item_modifiers->save();
				}
				$orderitem_modifier_ids = $update_item->order_item_modifier->pluck('id')->toArray();
				$orderitem_modifiers 	= OrderItemModifier::whereIn('id',$orderitem_modifier_ids);	
				$menu_price = $update_item->price;
				$t_menu_price = $update_item->offer_price > 0 ? $update_item->offer_price : $menu_price;
				$total_amount = ($order_item['item_count'] * $t_menu_price ) + ($orderitem_modifiers->sum('modifier_price'));
				$tax = ($total_amount * $menu->tax_percentage / 100);
				$update_item->quantity 	= $order_item['item_count'];
				$update_item->total_amount = $total_amount;
				$update_item->tax 		= $tax;
				$update_item->save();
			}
		}
		
		$order_data = get_user_order_details($order_data['store_id'], $user_details->id,$delivery_type);		
		
		return json_encode(['order_data' => $order_data]);
	}

	//order history
	public function order_history()
	{
		$this->view_data['user_details'] = auth()->guard('web')->user();

		$this->view_data['order_details'] = Order::getAllRelation()->where('user_id', $this->view_data['user_details']->id)->history()->orderBy('id', 'DESC')->paginate(10);

		$this->view_data['cancel_reason'] = OrderCancelReason::where('status', 1)->where('order_cancel_reason.type',Auth::user()->type)->get();

		$this->view_data['upcoming_order_details'] = Order::getAllRelation()->where('user_id', $this->view_data['user_details']->id)->upcoming()->orderBy('id', 'DESC')->get();

		return view('orders', $this->view_data);
	}

	//order invoice

	public function order_invoice()
	{
		$order_id = request()->order_id;

		$order = Order::with(['order_item' => function ($query) {
			$query->with('menu_item','order_item_modifier.order_item_modifier_item');
		}])->find($order_id);
		
		$currency_symbol = Order::find($order_id)->currency->symbol;

		return json_encode(['order_detail' => $order, 'currency_symbol' => $currency_symbol]);
	}

	//promo code changes

	public function add_promo_code(Request $request)
	{
		$code=$request->code;
		$delivery_type = $request->delivery_type; 
		$user_details = auth()->guard('web')->user();
		$promoId = '';
		$promo_code_default = 0;
		$promo_code_date_check = PromoCode::with('promotranslation')->where(function($query)use ($code){
			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);
			})->orWhere('code',$code);
		})->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->where('status',1)->first();
		
		if ($promo_code_date_check) {
			$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code_date_check->id)->where('user_id', $user_details->id)->first();
			if ($user_promocode) {
				$user_promocode_count = UsersPromoCode::where('promo_code_id', $promo_code_date_check->id)->where('user_id', $user_details->id)->get()->count();
				if($user_promocode_count == 0)
				{
					UsersPromoCode::where('user_id', $user_details->id)->where('promo_code_id', $promo_code_date_check->id)->update(['promo_default' =>1]);	
					$data['status'] = 1;
					$data['message'] = trans('api_messages.add_promo_code.promo_applied_successfully');	
				}
				else
				{
					$data['status'] = 0;
					$data['message'] = trans('messages.profile_orders.already_applied');
				}
			} else {
				$user_initial  =  UsersPromoCode::where('user_id', $user_details->id)->first();
				logger(json_encode($user_initial));
				$promo_code_default  = 1 ;
				$users_promo_code = new UsersPromoCode;
				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;
				$users_promo_code->order_id = 0;
				if(is_null($user_initial))
					$users_promo_code->promo_default = 1;
				$users_promo_code->save();
				$data['status'] = 1;
				$data['message'] = trans('api_messages.add_promo_code.promo_applied_successfully');
			}
			$amount = promo_calculation();
			$data['order_detail_data'] = get_user_order_details($request->store_id, $user_details->id);
		} else {

			$promo_code = PromoCode::with('promotranslation')->where(function($query) use($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);

			})->orWhere('code',$code);


			})->where('end_date', '<', date('Y-m-d'))->first();

			if ($promo_code) {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.promo_code_expired');
			} else {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.invalid_code');
			}

		}
		if (isset($request->page)) {
			$class = ($data['status'] == 1) ? 'success' : 'danger';
			flash_message($class, $data['message']);
			return back();
		}
		return $data;
	}

	
	public function removePromoCode(Request $request)
	{
		if(isset($request->delivery_type))
			$delivery_type = $request->delivery_type ;	
		$is_wallet = $request->isWallet ;
		if($request->code)
		{
			$promo_id = PromoCode::whereCode($request->code)->value('id');
			$promo_code=$promo_id;
		}
		else
		{
			$promo_code=$request->id;
		}
		$user_details = auth()->guard('web')->user();
		$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code)->where('user_id', $user_details->id)->update(['promo_default' =>0]);
		$delivery_type = $delivery_type ?? '';
		$data['status'] = 1;
		$data['message'] = trans('api_messages.add_promo_code.promo_deleted_successfully');
		$data['order_detail_data'] = get_user_order_details($request->store_id, $user_details->id,$delivery_type);
		$wallet_amount = use_wallet_amount($request->order_id, $is_wallet,$request->tips);
		$order_promo = Order::where('id',$request->order_id)->first();
		$data['order_detail_data']['wallet_amount'] = $order_promo->wallet_amount ;	
		$data['order_detail_data']['total_price'] = $order_promo->total_amount;
		if (isset($request->page)) {
			$class = ($data['status'] == 1) ? 'success' : 'danger';
			flash_message($class, $data['message']);
			return back();
		}
		return $data;
	}

	//confirm address check with store address
	public function location_check(Request $request)
	{
		$order_id = $request->order_id;
		$restuarant_id = $request->restuarant_id;
		$city = $request->city;
		$address1 = $request->address1;
		$state = $request->state;
		$country = $request->country;
		$location = $request->location;
		$postal_code = $request->postal_code;
		$latitude = $request->latitude;
		$longitude = $request->longitude;

		$user_id = get_current_login_user_id();
		$user_address = UserAddress::where('user_id', $user_id)->where('type',2)->first();
		if ($user_address == '') {
			$user_address = new UserAddress;
		}

		$user_address->user_id = $user_id;
		$user_address->address = $location;
		$user_address->street = $address1;
		$user_address->first_address = $location;
		$user_address->second_address = $address1;
		$user_address->city = $city;
		$user_address->state = $state;
		$user_address->country = $country;
		$user_address->postal_code = $postal_code;
		$user_address->latitude = $latitude;
		$user_address->longitude = $longitude;
		$user_address->default = 1;
		$user_address->delivery_options = 0;
		$user_address->save();

		session()->put('city', $city);
		session()->put('address1', $address1);
		session()->put('state', $state);
		session()->put('country', $country);
		session()->put('location', $location);
		session()->put('postal_code', $postal_code);
		session()->put('latitude', $latitude);
		session()->put('longitude', $longitude);

		$result = check_location($order_id);

		if ($result == 1) {
			return json_encode(['success' => 'true']);
		}
		if(!$request->checkout_page) {
			$OrderDelivery = OrderDelivery::where('order_id', $order_id)->first();
			$OrderDelivery->delete($OrderDelivery->id);

			$OrderItem = OrderItem::where('order_id', $order_id)->get();
			foreach ($OrderItem as $key => $value) {
				$value->delete($OrderItem[$key]->id);
			}

			$order = Order::find($order_id);
			$order->delete($order_id);

			session::forget('order_data');
			session::forget('order_detail');
		}

		return json_encode(['success' => 'none','message'=>trans('admin_messages.sorry_this_place_not_delivery')]);
	}

	//location not found
	public function location_not_found()
	{
		return view('location_not_found');
	}

	public function addDriverTips(Request $request)
	{
		if($request->tips > 0)
		{
			$order_tips = Order::find($request->order_id);
			$order_tips->tips = $request->tips;
			$order_tips->save();
			$is_wallet = $request->isWallet;
			$delivery_type  = $request->delivery_type;
			$data['order_detail_data'] = get_user_order_details($order_tips->store_id, $order_tips->user_id,$delivery_type);
			$wallet_amount = use_wallet_amount($order_tips->id, $is_wallet,$request->tips);
			$order_tips_wallet = Order::find($request->order_id);
			$data['order_detail_data']['wallet_amount'] =$order_tips_wallet->wallet_amount; 
			$data['order_detail_data']['total_price'] =$order_tips_wallet->total_amount; 	
			$data['message'] = trans('messages.store_dashboard.tips_added');
			$data['status'] = 1;
			return $data;
		}
		else
		{
			$data['message'] = trans('messages.store_dashboard.tips_greater_than');
			$data['status'] = 0;
			return $data;		
		}
	}

	public function googleAuthenticate(Request $request) {
		
		try 
		{
	        $client_id = view()->shared('google_client_id');
	        $client = new Google_Client(['client_id' => $client_id]);
	        // Specify the CLIENT_ID of the app that accesses the backend
	        $payload = $client->verifyIdToken($request->idtoken);
	        if($payload) {
	            $google_id = $payload['sub'];
	        } 
	        else {
	           	$this->helper->flash_message('danger', 'invalid_token'); 
	            return redirect('login');
	        }
        }
        catch(\Exception $e) {
            $this->helper->flash_message('danger', $e->getMessage()); 
            // Call flash message function
            return redirect('login');
        }
        if($request->connect == 'yes') {
            return redirect('googleConnect/'.$google_id);
        }
		$firstName = $payload['given_name'];
        $lastName =  @$payload['family_name'];
        $email = ($payload['email'] == '') ? $google_id.'@gmail.com' : $payload['email'];
		$user = User::where('email',$email)->orWhere('google_id',$google_id)->where('type',0);
		if($user->count() > 0)
		{
			$user= User::where('email',$email)->orWhere('google_id',$google_id)->first();
			$user->google_id = $google_id;
			$user->save();
			$user_id = $user->id;
		} else {
			// If not create a new user without Password
			$user = array(
                'first_name'	=> $firstName,
                'last_name'		=> $lastName,
                'email' 		=> $email,
                'key_id'		=> $google_id,
                'source' 		=> "google",
                'user_image' 	=> $payload['picture'],
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
				 flash_message('danger', trans('messages.store_dashboard.youre_disabled_by_admin_please_contact_admin'));
				return redirect('login');
			}
		} else {
			flash_message('danger', trans('messages.store_dashboard.youre_disabled_by_admin_please_contact_admin'));
			return redirect('login');
		}
	}
	
	public function removeDriverTips(Request $request)
	{
		if($request->order_id !='')
		{
			$order_tips = Order::find($request->order_id);
			$tips = $request->tips ?? 0 ;
			$order_tips->delivery_type = $request->delivery_type;
			$order_tips->tips = ($order_tips->delivery_type == "takeaway") ? 0 : $tips ;
			$order_tips->wallet_amount = 0 ;
			$order_tips->save();			
			$is_wallet = $request->isWallet;	
			$data['order_detail_data'] = get_user_order_details($order_tips->store_id, $order_tips->user_id,$request->delivery_type,$request->promo_id);
			if($is_wallet == 1){
				$total_ammm = ($data['order_detail_data']['subtotal'] + $data['order_detail_data']['tax'] + $data['order_detail_data']['delivery_fee'] + $data['order_detail_data']['booking_fee'] + $data['order_detail_data']['penalty']) - $data['order_detail_data']['promo_amount'] ;
				$order_tips->total_amount = $total_ammm ;
				$order_tips->save();	
				$wallet_amount = use_wallet_amount($request->order_id, $is_wallet,$tips);
				$order_tip_wal = Order::find($request->order_id);		
				$data['order_detail_data']['wallet_amount'] = $order_tip_wal->wallet_amount ;	
				$data['order_detail_data']['total_price'] = $order_tip_wal->total_amount ;	
			}	
			$data['message'] = trans('messages.profile.tips_removed');
			if (isset($request->page)) {
				$class = ($data['status'] == 1) ? 'success' : 'danger';
				flash_message($class, $data['message']);
				return back();
			}
			return $data;
		}
	}

	public function setDefaultPromo(Request $request)
	{
		$user_details = auth()->guard('web')->user();
		$promo = PromoCode::where('code',$request->code)->first();
		UsersPromoCode::where('user_id', $user_details->id)->update(['promo_default' =>0]);	
		UsersPromoCode::WhereHas(
				'promo_code')->where('user_id', $user_details->id)->where('promo_code_id',$promo->id)->update(['promo_default' =>1]);
		$user_promocode = UsersPromoCode::where('promo_code_id',$promo->id )->where('user_id', $user_details->id)->first();	
		$data['order_detail_data'] = get_user_order_details($request->store_id, $user_details->id,'delivery',$promo->id);
		$data['status'] = 1;
		$data['message'] = trans('messages.store.set_as_default');
		$class = ($data['status'] == 1) ? 'success' : 'danger';
		flash_message($class, $data['message']);
		return back();
	}

	public function removeUserPromo(Request $request)
	{
		$user_details = auth()->guard('web')->user();
		$promo = PromoCode::where('code',$request->code)->first();
		$promo = UsersPromoCode::where('promo_code_id', $promo->id)->where('user_id', $user_details->id)->first();
		if(isset($promo))
		{
			$user_promocode = UsersPromoCode::where('promo_code_id', $promo->promo_code_id)->where('user_id', $user_details->id)->delete();
			$data['status'] = 1;
			$data['message'] = trans('api_messages.add_promo_code.promo_deleted_successfully');
		}
		else
		{	
			$data['status'] = 0;
			$data['message'] = trans('api_messages.store.already_deleted');
		}
		$class = ($data['status'] == 1) ? 'success' : 'danger';
		flash_message($class, $data['message']);
		return back();
	}

	public function addPromoOrder(Request $request)
	{
		$code=$request->code;
		$delivery_type = $request->delivery_type; 
		$user_details = auth()->guard('web')->user();
		$is_wallet = $request->isWallet;
		$promo_code_date_check = PromoCode::with('promotranslation')->where(function($query)use ($code){
			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);
			})->orWhere('code',$code);
		})->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->where('status',1)->first();
		
		if ($promo_code_date_check) {
			$data['status'] = 1;
			$data['message'] = trans('api_messages.add_promo_code.promo_applied_successfully');
			$user_promocode = UsersPromoCode::WhereHas(
				'promo_code')->where('user_id', $user_details->id)->where('promo_code_id',$promo_code_date_check->id)->first();
			if ($user_promocode) {
				$$user_promocode = UsersPromoCode::WhereHas(
				'promo_code')->where('user_id', $user_details->id)->where('promo_code_id',$promo_code_date_check->id)->update(['promo_default' =>1]);
				$data['status'] = 1;
			} else {
				UsersPromoCode::where('user_id', $user_details->id)->update(['promo_default' =>0]);
				$users_promo_code = new UsersPromoCode;
				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;
				$users_promo_code->order_id = 0;
				$users_promo_code->promo_default = 1;
				$users_promo_code->save();
			}
			$data['order_detail_data'] = get_user_order_details($request->store_id, $user_details->id,$delivery_type,$promo_code_date_check->id);
			$wallet_amount = use_wallet_amount($request->order_id, $is_wallet,$request->tips);
			$order_promo = Order::where('id',$request->order_id)->first();
			$walletAmount = $order_promo->wallet_amount;
			$data['order_detail_data']['wallet_amount'] = ($walletAmount > 0) ? $walletAmount : 0 ;
		} else {
			$promo_code = PromoCode::with('promotranslation')->where(function($query) use($code){
			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);
			})->orWhere('code',$code);
			})->where('end_date', '<', date('Y-m-d'))->first();
			if ($promo_code) {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.promo_code_expired');
			} else {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.invalid_code');
			}
		}
		if (isset($request->page)) {
			$class = ($data['status'] == 1) ? 'success' : 'danger';
			flash_message($class, $data['message']);
			return back();
		}
		return $data;
	}

}
