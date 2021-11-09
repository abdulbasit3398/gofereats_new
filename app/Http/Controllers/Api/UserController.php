<?php

/**
 * User Controller
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
use App\Models\Cuisine;
use App\Models\Currency;
use App\Models\IssueType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PenalityDetails;
use App\Models\PromoCode;
use App\Models\Store;
use App\Models\StoreTime;
use App\Models\Review;
use App\Models\ReviewIssue;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Models\MenuItemModifier;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;
use App\Models\Wallet;
use App\Models\Wishlist;
use App\Models\ServiceType;
use App\Traits\FileProcessing;
use App\Traits\AddOrder;
use Auth;
use Carbon\Carbon;
use DB;
use JWTAuth;
use Storage;
use Stripe;
use Validator;
use App\Models\PayoutPreference;
use App\Models\Country;
use Session;
use App\Models\MenuCategory;	



class UserController extends Controller
{
	use FileProcessing,AddOrder;

	public function __construct()
	{
		parent::__construct();
	}

	protected function mapUserAddress($user_address)
	{
		return $user_address->map(function($address) {
			return [
				'id' 			=> $address->id,
				'user_id' 		=> $address->user_id,
				'address' 		=> $address->address ?? "",
				'street'		=> $address->street,
				'first_address' => $address->first_address ?? "",
				'second_address'=> $address->second_address ?? '',
				'address1' 		=> $address->address1 ?? '',
				'city' 			=> $address->city ?? '',
				'state' 		=> $address->state ?? '',
				'postal_code' 	=> $address->postal_code ?? '',
				'country_code' 	=> $address->country_code ?? '',
				'latitude' 		=> $address->latitude ?? '',
				'longitude' 	=> $address->longitude ?? '',
				'default' 		=> $address->default ?? 0,
				'delivery_options' => $address->delivery_options ?? 0,
				'order_type' 	=> $address->order_type ?? 0,
				'delivery_time' => $address->delivery_time ?? "",
				'apartment' 	=> $address->apartment ?? "",
				'delivery_note' => $address->delivery_note ?? "",
				'type' 			=> $address->type ?? 0,
				'static_map' 	=> $address->static_map ?? "",
				'default_timezone' => $address->default_timezone,
			];
		});
	}

	/**
	 * Store Details display to User
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function home(Request $request)
	 {
		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;
		$default_currency_symbol = html_entity_decode($default_currency_symbol);
		$user_details = '';
		$already_cart = '';
		if(isset(request()->token)) {
			$user_details = JWTAuth::parseToken()->authenticate();
			$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();
		}

		$cuisine = Cuisine::where('service_type',$request->service_type)->where('status', 1)->active()->get();
		$cuisine = $cuisine->map(function ($item) {
			return [
				'id' => $item['id'],
				'name' => $item['name'],
				'dietary_icon' => $item['category_image'],
			];
		})->toArray();

		$address_details = $this->address_details();

		$perpage = 7;

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];

		
		$store_details = (object) [];

		if($already_cart) {
			if($already_cart->store_id){				
				$available = Store::find($already_cart->store_id);
				$store_details = ['image' => $available->banner, 'name' => $available->name];
			}
		}

		if (isset($request->order_type)) {
			if($user_details) {
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = $request->order_type;
				$address->save();
			}
		}

		//more Store
		$date = \Carbon\Carbon::today();

		$service_type = $request->service_type;		
		$common = User::with(
			['store' => function ($query) use ($latitude, $longitude, $date) {
				$query->with(['store_cuisine', 'review', 'store_offer', 'user_address', 'store_time']);
			}]
		)->Type('store')->whereHas('store', function ($query) use ($latitude, $longitude,$service_type) {
			$query->location($latitude, $longitude)->whereHas('store_time', function ($query) {
			})->where('service_type',$service_type);
		})->status();


		$more_store = (clone $common)->paginate($perpage);

		$count_store = $more_store->lastPage();

		$more_store = $this->common_map($more_store);

		// New store
		$date = \Carbon\Carbon::today()->subDays(10);

		$new_store = (clone $common)->where('created_at', '>=', $date)->paginate($perpage);

		$count_new_store = $new_store->lastPage();

		$new_store = $this->common_map($new_store);
		$date = \Carbon\Carbon::today()->format('Y-m-d');
		// Store Offer
		$store_offer = (clone $common)->whereHas(
			'store',
			function ($query) use ($date) {
				$query->whereHas(
					'store_offer',
					function ($query) use ($date) {
						$query->where('start_date', '<=', $date)->where('end_date', '>=', $date);
					}
				)->orderBy('id' , 'desc');
			}
		)->paginate($perpage);

		

		$count_offer = $store_offer->lastPage();

		$store_offer = $store_offer->map(
			function ($item) {
				return [
					'store_id' => $item['store']['id'],
					'name' => $item['store']['name'],
					'banner' => $item['store']['banner'],
					'title' => $item['store']['store_offer'][0]['offer_title'],
					'description' => $item['store']['store_offer'][0]['offer_description'],
					'percentage' => $item['store']['store_offer'][0]['percentage'],
					'delivery_type' => $item['store']['delivery_type'],
				];
			}
		);

		// Under prepartion time min
		$under = (clone $common)->get();
		$count_under = 0;

		$convert_mintime = 0;
		if (count($under) != 0) {

			$under = $under->sortBy(
				function ($unders) {
					return $unders->store->convert_mintime;
				}
			)->values();

			$max_time = $under[0]['store']['max_time'];

			$convert_mintime = $under[0]['store']['convert_mintime'];

			$under_minutes = (clone $common)->whereHas(
				'store',
				function ($query) use ($max_time) {
					$query->where('max_time', $max_time);
				}
			)->paginate($perpage);

			$count_under = $under_minutes->lastPage();

			$under_minutes = $this->common_map($under_minutes);
		}
		
		// Wishlist

		$wish = Wishlist::selectRaw('*,store_id as ids, (SELECT count(store_id) FROM wishlist WHERE store_id = ids) as count')->with(

			['store' => function ($query) use ($latitude, $longitude) {

				$query->with(['store_cuisine', 'review', 'user', 'store_time', 'store_offer']);
			}]
		)->whereHas('store', function ($query) use ($latitude, $longitude,$service_type) {

			$query->where('service_type',$service_type)->UserStatus()->location($latitude, $longitude)->whereHas('store_time', function ($query) {


			});

		});

		

		if($user_details) {
			$wishlist = (clone $wish)->where('user_id', $user_details->id)->paginate($perpage);
			$count_fav = $wishlist->lastPage();
			$wishlist = $this->common_map($wishlist);
		}		
		else {
			$wishlist = [];
			$count_fav = 0;
		}

		// Popular Store
		$popular = (clone $wish)->groupBy('store_id')->orderBy('count', 'desc')
		->paginate($perpage);

		$count_popular = $popular->lastPage();

		$popular = $this->common_map($popular);

		$more_store = (count($more_store) > 0) ? $more_store->toArray() : array(); // more store
		$fav = (count($wishlist) > 0) ? $wishlist->toArray() : array(); // favourite store
		$under_minutes = (count($under) > 0) ? $under_minutes->toArray() : array();
		$store_offer = (count($store_offer) > 0) ? $store_offer->toArray() : array();
		$new_store = (count($new_store) > 0) ? $new_store->toArray() : array();

		if($user_details) {
			$wallet = Wallet::where('user_id', $user_details->id)->first();
			$currency_symbol = Currency::where('code',$user_details->currency_code)->value('symbol');
			$currency_code = $user_details->currency_code;
		}

		$search = [
			0 => 'Filter',
			1 => 'Favourite',
			2 => 'Popular',
			3 => 'Under',
			4 => 'New Store',
			5 => 'More Store',
		];

		return response()->json([
			'status_code' => '1',
			'status_message' => "Success",
			'under_time' => $convert_mintime,
			'More Store' => $more_store,
			'Favourite Store' => $fav,
			'Popular Store' => $popular,
			'New Store' => $new_store,
			'Under Store' => $under_minutes,
			'Store Offer' => $store_offer,
			'more_count' => $count_store,
			'fav_count' => $count_fav,
			'popular_count' => $count_popular,
			'under_count' => $count_under,
			'offer_count' => $count_offer,
			'new_count' => $count_new_store,
			
			'fav_type' => 1, 
			'popular_type' => 2,
			'under_type' => 3,
			'new_type' => 4,
			'more_type' =>5,
			
			'wallet_amount' => isset($wallet->amount) ? $wallet->amount : 0,
			'wallet_currency' => isset($currency_code->code) ? $currency_code->code : DEFAULT_CURRENCY,
			'cart_details' => $store_details,
			'home_categories' => [trans('user_api_language.home.more_store'), trans('user_api_language.home.favourite_store'), trans('user_api_language.home.popular_store'), trans('user_api_language.home.new_store'), trans('user_api_language.home.under_store')],
			'cuisine' => $cuisine,
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
			'currency_code' => isset($currency_code) ? $currency_code:'',
			'currency_symbol' => isset($currency_symbol) ? html_entity_decode($currency_symbol):'',
		]);
	}

	/**
	 * API for Ios
	 *
	 * @return Response Json response with status
	 */
	
	public function ios(Request $request)
	{

		if(isset($_POST['token']))
			$user_details =  JWTAuth::toUser($_POST['token']);
		else
			$user_details = JWTAuth::parseToken()->authenticate();

		$request = request();

		$order = Order::getAllRelation()->where('id', $request->order_id)->first();

		$rating = str_replace('\\', '', $request->rating);

		$rating = json_decode($rating);

		$order_id = $order->id;

		$food_item = $rating->food;

		//Rating for Menu item
		if ($food_item) {

			foreach ($food_item as $key => $value) {

				$review = new Review;
				$review->order_id = $order_id;
				$review->type = $review->typeArray['user_menu_item'];
				$review->reviewer_id = $user_details->id;
				$review->reviewee_id = $value->id;
				$review->is_thumbs = $value->thumbs;
				$review->order_item_id = $value->order_item_id;
				$review->comments = $value->comment ?: "";
				$review->save();

				if ($value->reason) {
					$issues = explode(',', $value->reason);
					if ($request->thumbs == 0 && count($value->reason)) {
						foreach ($issues as $issue_id) {
							$review_issue = new ReviewIssue;
							$review_issue->review_id = $review->id;
							$review_issue->issue_id = $issue_id;
							$review_issue->save();
						}
					}

				}

			}

		}

		//Rating for driver

		if (count(get_object_vars($rating->driver)) > 0) {

			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_driver'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->driver_id;
			$review->is_thumbs = $rating->driver->thumbs;
			$review->comments = $rating->driver->comment ?: "";
			$review->save();

			if ($rating->driver->reason) {
				$issues = explode(',', $rating->driver->reason);
				if ($rating->driver->thumbs == 0 && count($issues)) {
					foreach ($issues as $issue_id) {
						$review_issue = new ReviewIssue;
						$review_issue->review_id = $review->id;
						$review_issue->issue_id = $issue_id;
						$review_issue->save();
					}
				}

			}

		}

		//Rating for Store
		if (count(get_object_vars($rating->store)) > 0) {
			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_store'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->store_id;
			$review->rating = $rating->store->thumbs;
			$review->comments = $rating->store->comment ?: "";
			$review->save();
		}
		return response()->json([
			'status_code' => '1',
			'status_message' =>  __('user_api_language.register.update_success'),
		]);
	}

	/**
	 * API for Search
	 *
	 * @return Response Json response with status
	 */
	public function categories(Request $request)
	{
		$top_cuisine = Cuisine::where('is_top', 1)->where('service_type',$request->service_type)->get();
		$more_cuisine = Cuisine::where('is_top', 0)->where('service_type',$request->service_type)->get();
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.sucess'),
			'top_category' => $top_cuisine,
			'category' => $more_cuisine,
		]);
	}

	public function search(Request $request)
	{
		$user_details ='';
		$cateogry='';
		$lang= Session::get('language');
		if(request()->token) {
			$user_details = JWTAuth::parseToken()->authenticate();
			$lang= $user_details->language;
		}
		$address_details = $this->address_details();
		return store_search ($user_details, $address_details, $request->keyword,$request->service_type,$request->delivery_type,$lang,$request->page,$cateogry);
	}

	/**
	 * API for An Store Details
	 *
	 * @return Response Json response with status
	 */
	public function get_store_details(Request $request)
	{
		$user_details = '';
		if($request->has('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
			if (isset($request->order_type)) {
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = $request->order_type;
				$address->save();
			}
		}

		$rules = array(
			'store_id' => 'required',
		);
		$messages = array (
			'required' => ':attribute '.trans('api_messages.register.field_is_required'), 
		);  

		$attributes = array(
			'store_id' => trans('api_messages.store.store_id'),
		);
		$validator = Validator::make($request->all(), $rules,$messages, $attributes);
		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}

		$address_details = $this->address_details();

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];
		$date = \Carbon\Carbon::today();

		$store_details = Store::with(
			[
				'store_cuisine', 'review',
				'store_preparation_time',
				'store_offer',
				'store_time',
				'store_menu' => function ($query) {
					$query->with(
						['menu_category' => function ($query) {
							$query->with(
								['menu_item' => function ($query) {

								}]
							)->has('menu_item');
						}]
					)->storeDetail();
				},
				'file' => function ($queryB) {
					$queryB->where('type', '4');
				},
			]
		)->where('id', $request->store_id)->UserStatus()->location($latitude, $longitude)->get();

		$store_details = $store_details->mapWithKeys(
			function ($item) use ($user_details, $delivery_time, $order_type) {
				$store_cuisine = $item['store_cuisine']->map(
					function ($item) {
						return $item['cuisine_name'];
					}
				)->toArray();
				$wishlist = 0;
				if(request()->token){
					$wishlist = $item->wishlist($user_details->id, $item['id']);
				}
				$open_time = $item['store_time']['start_time'];
				return [
					'order_type' => $order_type,
					'delivery_time' => $delivery_time,
					'delivery_type' =>trans('api_messages.store.'.$item['delivery_type']),
					'store_id' => $item['id'],
					'name' => $item['name'],
					'category' => implode(',', $store_cuisine),
					'banner' => $item['banner'],
					'min_time' => $item['convert_mintime'],
					'max_time' => $item['convert_maxtime'],
					'wished' => $wishlist ,
					'status' => $item['status'],
					'store_menu' => $item['store_menu'],
					'store_rating' => $item['review']['store_rating'],
					'price_rating' => $item['price_rating'],
					'average_rating' => $item['review']['average_rating'],
					'store_closed' => $item['store_time']['closed'],
					'store_open_time' =>$open_time,
					'store_next_time' => $item['store_next_opening'],
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
			}
		);

		$store_details = $store_details->toArray();

		//Get remaning menu values
		if (count($store_details) > 0) 
		{
			$store_menu_id = $store_details['store_menu'][0]['id'] ?? '';
			$all_details = array();
			if($store_menu_id != ''){
				$all_details = Menu::where('store_id', $request->store_id)->whereNotIn('id',[$store_menu_id])->get()->toArray();
			}
			
			//Check it has remain menu
			if(count($all_details) > 0){
				$store_details['store_menu'] = array_merge($store_details['store_menu'],$all_details);
			}
		}

		$service_type 	= Store::where('id',$request->store_id)->first();
		$service_id 	= ServiceType::where('id',$service_type->service_type)->first();
		
		if (count($store_details) > 0) {
			return response()->json([
				'status_code' => '1',
				'status_message' => trans('user_api_language.success'),
				'store_details' => $store_details,
				'service_status'=> $service_id->status,
			]);
		}

		$store = Store::find($request->store_id);

		$store_name = $store->name;
		$store_cuisine = $store->store_cuisine[0]['cuisine_name'];
		
		$check_address = check_store_location('', $latitude, $longitude, $request->store_id);

		if ($store->status == 0 || $check_address == 0) {
			return response()->json([
				'status_code' => '2',
				'status_message' => trans('store_api_language.store.unavailable'),
				'messages' => trans('store_api_language.store.it_look_like') . $store_name . trans('store_api_language.store.close_enough'),
				'cuisine' => $store_cuisine,
			]);
		}

		if($store->service_type  != $request->service_type)
		{
			return response()->json([
				'status_code' => '4',
				'status_message' => trans('store_api_language.store.service_type_mismatch'),
				'messages' => trans('store_api_language.store.it_look_like') . $store_name . trans('store_api_language.store.service_type_mismatch'),
				'cuisine' => $store_cuisine,
			]);
		}

		return response()->json([
			'status_code' => '3',
			'status_message' => trans('store_api_language.store.store_inactive'),
			'messages' =>trans('store_api_language.store.it_look_like'). $store_name . trans('store_api_language.store.currently_unavailable'),
			'cuisine' => $store_cuisine,
		]);
	}

	public function get_menu_item_addon(Request $request)
	{
		if($request->has('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
		}

		$rules = array(
			'menu_item_id'	=> 'required',
		);
		
		$attributes = array(
			'store_id' => trans('store_api_language.store.store_id'),
			'menu_item_id'	=> trans('store_api_language.store.menu_item_id'),
		);
		$messages = array(
			'required' => ':attribute '.trans('user_api_language.register.field_is_required'),
		);

		$validator = Validator::make($request->all(), $rules,$messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}
		$menu_item_name = MenuItem::where('id',$request->menu_item_id)->first();
		$menu_tem_description = $menu_item_name->description;
		//To get Menu Item Addon
		$menu_item_addon = MenuItemModifier::with('menu_item_sub_addon')->where('menu_item_id',$request->menu_item_id)->get();
		$menu_item_addon = $menu_item_addon->map(function($menu_modifier) {
			$menu_item_sub_addon = $menu_modifier->menu_item_sub_addon->map(function($menu_modifier_item) {
				return [
					'id' 		=> $menu_modifier_item->id,
					'name' 		=> $menu_modifier_item->name,
					'price' 	=> $menu_modifier_item->price,
					'is_visible'=> $menu_modifier_item->is_visible,
					'count' 	=> (int) $menu_modifier_item->count,
					'menu_item_modifier_id' 	=> $menu_modifier_item->menu_item_modifier_id,
					'is_select' => $menu_modifier_item->is_select,
				];
			})->toArray();
			return [
				'id' 			=> $menu_modifier->id,
				'menu_item_id' 	=> $menu_modifier->menu_item_id,
				'name' 			=> $menu_modifier->name,
				'count_type' 	=> $menu_modifier->count_type,
				'is_multiple'	=> $menu_modifier->is_multiple,
				'min_count' 	=> $menu_modifier->min_count,
				'max_count' 	=> $menu_modifier->max_count,
				'count' 		=> (int) collect($menu_item_sub_addon)->sum('count'),
				'is_required' 	=> $menu_modifier->is_required,
				'is_selected' 	=> $menu_modifier->is_selected,
				'menu_item_sub_addon'	=> $menu_item_sub_addon,
			];
		});

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.sucess'),
			'menu_item_description' =>$menu_tem_description,
			'menu_item_addon' => $menu_item_addon,
		]);

	}

	/**
	 * API for Add Promo details
	 *
	 * @return Response Json response with status
	 */
	public function add_promo_code(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'code' => 'required',
		);
		$messages = array(
			'required'  => ':attribute '.trans('user_api_language.register.field_is_required'),
		);
		$attributes = array(
			'code' => trans('user_api_language.add_promo_code.code')
		);
		$validator = Validator::make($request->all(), $rules,$messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}

		$code=$request->code;
		$promo_code_date_check = PromoCode::with('promotranslation')->where(function($query) use($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);
			})->orWhere('code',$code);

		})->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();

		if ($promo_code_date_check) {

			$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code_date_check->id)->where('user_id', $user_details->id)->first();

			if ($user_promocode) {
				return ['status_code' => '0', 'status_message' => trans('user_api_language.add_promo_code.promo_code_already_applied')];
			} else {
				$users_promo_code = new UsersPromoCode;
				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;
				$users_promo_code->order_id = 0;
				$users_promo_code->save();
			}
			$user_promocode = UsersPromoCode::WhereHas(
				'promo_code',
				function ($q) {
				}
			)->where('user_id', $user_details->id)->where('order_id', '0')->get();
			$final_promo_details = [];

			foreach ($user_promocode as $row) {
				if (@$row->promo_code) {
					$promo_details['id'] = $row->promo_code->id;
					$promo_details['price'] = $row->promo_code->price;
					$promo_details['type'] = $row->promo_code->promo_type;
					$promo_details['percentage'] = $row->promo_code->percentage;
					$promo_details['code'] = $row->promo_code->code;
					$promo_details['expire_date'] = $row->promo_code->end_date;
					$final_promo_details[] = $promo_details;
				}
			}

			$user = array('promo_details' => $final_promo_details, 'status_message' => trans('user_api_language.add_promo_code.promo_applied_successfully'), 'status_code' => '1');
			return response()->json($user);
		}

		$promo_code = PromoCode::with('promotranslation')->where(function($query)use ($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);

			})->orWhere('code',$code);

		})->where('end_date', '<', date('Y-m-d'))->first();

		if ($promo_code) {
			return ['status_code' => '0', 'status_message' => trans('user_api_language.add_promo_code.promo_code_expired')];
		}
		return ['status_code' => '0', 'status_message' => trans('user_api_language.add_promo_code.invalid_code')];
	}

	/**
	 * API for Promo details
	 *
	 * @return Response Json response with status
	 */
	public function get_promo_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$user_promocode = UsersPromoCode::WhereHas(
			'promo_code',
			function ($q) {
				$q->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'));
			}
		)->where('user_id', $user_details->id)->where('order_id', '0')->get();

		$final_promo_details = [];

		foreach ($user_promocode as $row) {
			if (@$row->promo_code) {
				$promo_details['id'] = $row->promo_code->id;
				$promo_details['price'] = $row->promo_code->price;
				$promo_details['type'] = $row->promo_code->promo_type;
				$promo_details['percentage'] = $row->promo_code->percentage;
				$promo_details['code'] = $row->promo_code->code;
				$promo_details['expire_date'] = $row->promo_code->end_date;
				$promo_details['default_promo'] = $row->promo_default;
				$final_promo_details[] = $promo_details;
			}
		}
		$user = array('promo_details' => $final_promo_details, 'status_message' =>trans('user_api_language.success'), 'status_code' => '1');
		return response()->json($user);
	}

	public function get_location(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$address = UserAddress::where('user_id', $user_details->id)
			->orderBy('type', 'ASC')
			->get()
			->map(function ($val) {
				$val->first_address = str_replace('', Null, $val->first_address);  
				$val->second_address = str_replace('', Null, $val->second_address);
				$val->apartment = str_replace('', Null, $val->apartment);
				$val->street = str_replace('', Null, $val->street);
				$val->postal_code = str_replace('', Null, $val->postal_code);
				$val->city = str_replace('', Null, $val->city);
				$val->address = str_replace('', Null, $val->address);
				$val->address1 = str_replace('', Null, $val->address1);
				$val->delivery_options = $val->delivery_options ?? 0;
				$val->delivery_note = $val->delivery_note ?? "";
			return $val;
		});
		$user = array(
			'status_message' => trans('user_api_language.success'),
			'status_code' => '1',
			'user_address' => $address,
		);
		return response()->json($user);
	}

	/**
	 * API for Set Save Location
	 *
	 * @return Response Json response with status
	 */
	public function saveLocation(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		logger("save location".$request->type);
		$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();

		if ($already_cart) {
			$check_address = check_store_location($already_cart['id'], $request->latitude, $request->longitude);

			if ($check_address == 0) {
				$address = UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();

				return response()->json(['status_message' => trans('store_api_language.store.store_unavailable'), 'status_code' => '2', 'user_address' => $check_address]);
			}
		}

		if($request->type == 2) {
			UserAddress::where('user_id', $user_details->id)->update(['default' => 0]);
		}

		$address = UserAddress::where('user_id', $user_details->id)->where('type', $request->type)->first();

		if ($request->type == 2 || optional($address)->default == 1) {
			$default = 1;
		}
		else {
			$add = UserAddress::where('user_id', $user_details->id)->where('default','!=',1)
			->update(['default' => 0]);

			if(!$add) {
				$default =0;
			}
		}

		if ($address == '') {
			$address = new UserAddress;
			$address->user_id = $user_details->id;
		}

		$address->street = $request->street;
		$address->city = $request->city;
		$address->state = $request->state;
		$address->first_address = $request->first_address;
		$address->second_address = $request->second_address;
		$address->postal_code = $request->postal_code;
		$address->country = $request->country;
		$address->country_code = $request->country_code;
		$address->type = $request->type;		
		$address->default = $default;
		$address->apartment = $request->apartment;
		$address->delivery_note = $request->delivery_note ?? '';
		$address->delivery_options = $request->delivery_options ?? '';
		$address->order_type = $request->order_type ?? '';
		$address->delivery_time = $request->delivery_time ?? '';
		$address->latitude = $request->latitude;
		$address->longitude = $request->longitude;
		$address->address = $request->address;
		$address->save();

		$address_details= UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();

		$user = array(
			'status_code' 	=> '1',
			'status_message'=> __('user_api_language.success'),
			'user_address' 	=> $address_details,
		);
		return response()->json($user);
	}

	/**
	 * API for Set Default Location
	 *
	 * @return Response Json response with status
	 */
	public function defaultLocation(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'default' => 'required|exists:user_address,type,user_id,' . $user_details->id,
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$add = UserAddress::where('type', $request->default)->where('user_id', $user_details->id)->first();
		$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();
		if ($already_cart) {
			$check_address = check_store_location($already_cart['id'], $add->latitude, $add->longitude);
			if ($check_address == 0) {
				$address = UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();
				return response()->json(['status_message' => 'Store unavailable', 'status_code' => '2', 'user_address' => $check_address]);
			}
		}

		UserAddress::where('default', 1)->where('user_id', $user_details->id)->update(['default' => 0]);

		$user_address = UserAddress::where('user_id', $user_details->id)->where('type', $request->default)->first();
		$user_address->default = 1;
		$user_address->order_type = $request->order_type ?? '';
		$user_address->delivery_time = $request->delivery_time ?? '';
		$user_address->save();

		$user = array(

			'status_message' =>  __('user_api_language.success'),

			'status_code' => '1',

		);

		return response()->json($user);
	}

	/**
	 * API for Remove Location
	 *
	 * @return Response Json response with status
	 */
	public function remove_location(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$remove = UserAddress::where('type', $request->type)->where('user_id', $user_details->id)->first();

		if ($remove) {

			$remove->delete();

			if ($remove->default == 1) {

				$update_default = UserAddress::where('user_id', $user_details->id)->first();
				$update_default->default = 1;
				$update_default->save();

			}

		}

		$address = UserAddress::where('user_id', $user_details->id)->get();

		$user = array(

			'status_message' =>  __('user_api_language.success'),

			'status_code' => '1',

			'user_address' => $address,

		);

		return response()->json($user);
	}

	/**
	 * API for Wishlist
	 *
	 * @return Response Json response with status
	 */
	public function add_wish_list(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$user = User::where('id', $user_details->id)->first();

		if (isset($user)) {
			$store_id = $request->store_id;

			$wishlist = Wishlist::where('user_id', $user_details->id)->where('store_id', $store_id)->first();

			if (isset($wishlist)) {

				$wishlist->delete();

				return response()->json(
					[

						'status_message' => __('user_api_language.unwishlist_sucess'),

						'status_code' => '1',

					]
				);
			} else {
				$wishlist = new Wishlist;
				$wishlist->store_id = $store_id;
				$wishlist->user_id = $user_details->id;
				$wishlist->save();

				return response()->json([
					'status_message' => __('user_api_language.wishlist_sucess'), 
					'status_code' => '1',
				]);
			}
		} else {
			return response()->json(
				[
					'status_message' => __('user_api_language.register.invalid_credentials'),
					'status_code' => '0',
				]
			);
		}
	}

	/**
	 * API for update user profile details
	 *
	 * @return Response Json response with status
	 */
	public function update_profile(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();
		// dd($user_details);
		$rules = array(

			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();
			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$user_check = User::where('id', $user_details->id)->first();
			
			$country_id 	= Country::whereCode($request->country_code)->value('id');
			$country_code= Country::whereCode($request->country_code)->value('phone_code');
			if (isset($user_check)) {
				User::where('id', $user_details->id)->update(
					['name' => html_entity_decode($request->first_name . '~' . $request->last_name),'user_first_name'=>$request->first_name , 'user_last_name' =>$request->last_name ,'email' => html_entity_decode($request->email),'country_code' =>$country_code,'country_id' =>$country_id ]
			);
				$user = User::where('id', $user_details->id)->first();
				return response()->json(
					[
						'status_message' => __('user_api_language.cart.updated_successfully'),
						'status_code' => '1',
						'name' => $user->name,
						'mobile_number' => $user->mobile_number,
						'country_code' => $user->country_code,
						'email' => $user->email,
						'profile_image' => $user->users_image,
					]
				);
			} else {
				return response()->json(
					[
						'status_message' => __('user_api_language.register.invalid_credentials'),
						'status_code' => '0',
					]
				);
			}
		}
	}

	/**
	 * API for User image
	 *
	 * @return Response Json response with status
	 */
	public function upload_image(Request $request)
	{
	   $user_details = JWTAuth::parseToken()->authenticate();
		$rules = array('image' => 'required');
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$error = $validator->messages()->toArray();
			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {	
			$user_check = User::where('id', $user_details->id)->first();
			if (isset($user_check)) {
				$file = $request->file('image');
				if($user_check->type != 2)
				{	
					$file_path = $this->fileUpload($file, 'public/images/user');
					if($user_check->auth_type  && $user_check->auth_type != 'mobile_number')
					{
						$this->fileSave('social_login', $user_details->id, $file_path['file_name'], '2');
					}
					else{
						$this->fileSave('user_image', $user_details->id, $file_path['file_name'], '1');
					}
				}
				else
				{
					$file_path = $this->fileUpload($file, 'public/images/driver');
					$this->fileSave('driver_image', $user_details->id, $file_path['file_name'], '1');
				}
				$orginal_path = Storage::url($file_path['path']);
				$user = User::where('id', $user_details->id)->first();
				return response()->json(
					[
						'status_message' => trans('user_api_language.cart.updated_successfully'),
						'status_code' => '1',
						'name' => $user->name,
						'mobile_number' => $user->mobile_number,
						'country_code' => $user->country_code,
						'email_id' => $user->email,
						'profile_image' => $user->users_image,
						'user_image_url'	=>$user->user_image_url,
					]
				);
			} else {
				return response()->json(
					[
						'status_message' =>trans('user_api_language.register.invalid_credentials'),
						'status_code' => '0',
					]
				);
			}
		}
	}

	/**
	 * API for Add to cart
	 *
	 * @return Response Json response with status
	 */
	
	public function add_to_cart(Request $request) 
	{
		logger('Params : '.json_encode($request->all()));
		$rules = array(
            'store_id'		=> 'required|exists:store,id',
            'menu_item_id'	=> 'required|exists:menu_item,id',
            'quantity'		=> 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }
        
        $find_store_by_menu = MenuItem::where('id',$request->menu_item_id)->store($request->store_id)->first();
        $delivery_type = Store::where('id',$request->store_id)->first()->delivery_type_array;
        
        if(!$find_store_by_menu) {
        	return response()->json([
                'status_code' => '0',
                'status_message' => trans('user_api_language.invalid_menu_item_id'),
            ]);
        }

		$data =  $this->add_cart_item($request,1);

		if($data['status_code'] != 1) {
			return response()->json([
				'status_code' 	 => $data['status_code'],
				'status_message' => $data['status_message'],
			]);
		}
		
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.cart.updated_successfully'),
			'subtotal'  => $data['subtotal'],
			'quantity'  => intval($data['quantity']),
			'delivery_type' => $delivery_type,
		]);
	}

	/**
	 * API for view cart
	 *
	 * @return Response Json response with status
	 */
	public function view_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$address_details = $this->address_details();
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];
		//Check Cart
		$cart_order = Order::getAllRelation()->where('user_id', $user_details->id)->status('cart')->first();
		
		if (!$cart_order) {
			return response()->json([
				'status_code' => '2',
				'status_message' => trans('user_api_language.cart.cart_empty'),
			]);
		}
		// Order menu Available or not
		$date = ($order_type == 0) ?  date('Y-m-d H:i') : $delivery_time;
		$check_menu = check_menu_available($cart_order->id, $date);
		


		if (isset($check_menu['status']) && $check_menu['status'] == false) {
			return response()->json([
				'status_code' => '4',
				'status_message' => $check_menu['status_message'],
			]);
		}
		else if (count($check_menu) > 0 && !isset($check_menu['status'])) {
			return response()->json([
				'status_code' => '3',
				'status_message' => trans('user_api_language.cart.item_not_available'),
				'unavailable' => $check_menu,
			]);
		}

		$delivery_address = UserAddress::where('user_id', $user_details->id)->default()->limit(1)->get();
		$delivery_address_data = $this->mapUserAddress($delivery_address)->first();
		$delivery_address = $delivery_address->first();
		
		//check address
		if (!$delivery_address) {
			$delivery_address = '';
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('user_api_language.cart.address_empty'),
			]);
		}

		$check_address = check_location($cart_order['id']);
		if ($check_address == 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('user_api_language.cart.store_unavailable'),
			]);
		}
		$promo_code = $request->promo_code;
		$tips = ($request->delivery_type == 'delivery') ? $request->tips : 0 ;
		if(isset($request->tips))
		{
			$cart_order->tips = $tips;
			$cart_order->save();
		}

		$order_details_data = get_user_order_details($cart_order->store_id,$cart_order->user_id,$request->delivery_type,$request->promo_code);
		$total_amount =  $order_details_data['total_price'] ;
		//wallet apply
		$is_wallet = $request->isWallet;
		$wallet_amount = use_wallet_amount($cart_order->id, $is_wallet,$tips);
		$cart_order = Order::getAllRelation()->where('user_id', $user_details->id)->status('cart')->first();
		$results = array();
		$cart_details = $cart_order->toArray();
		$promo_amount = 0.00;
		$penalty = 0.00;
		if($cart_order->user_penality)
			$penalty = $cart_order->user->penalty->remaining_amount ;
		$promo_amount = $order_details_data['promo_amount'] ;
		if($order_details_data['wallet_amount'] > 0 || $cart_order->wallet_amount > 0)
		{
			$total_amount = $cart_order->total_amount ;
		}
		$data = [
			'id' => $cart_details['id'],
			'store_id' => $cart_details['store_id'],
			'user_id' => $cart_details['user_id'],
			'address' => $delivery_address_data,
			'driver_id' => $cart_details['driver_id'] ?? 0,
			'subtotal' => $cart_details['subtotal'],
			'offer_percentage' => $cart_details['offer_percentage'],
			'offer_amount' => $cart_details['offer_amount'],
			'promo_id' => $cart_details['promo_id'],
			'promo_amount' => $order_details_data['promo_amount'],
			'delivery_fee' => $cart_details['delivery_fee'],
			'booking_fee' => $cart_details['booking_fee'],
			'store_commision_fee' => $cart_details['store_commision_fee'],
			'driver_commision_fee' => $cart_details['driver_commision_fee'] ?? '',
			'tax' => $cart_details['tax'],
			'tips'	=> $cart_details['tips'], 
			'total_amount' => number_format($total_amount , 2 ) ,
			'wallet_amount' => $cart_details['wallet_amount'],
			'payment_type' => $cart_details['payment_type'] ?? 0,
			'owe_amount' => $cart_details['owe_amount'],
			'status' => $cart_details['status'],
			'payout_status' => $cart_details['payout_status'] ?? '',
			'store_status' => $cart_details['store_status'],
			'penality' => number_format($cart_order->user_penality,2),
			'payment_methods'=>getDefautlPaymentMethod(),
		];

		$data['invoice'] = [
			array('key' =>trans('user_api_language.cart.subtotal'), 'value' => $cart_details['subtotal']),
			array('key' =>trans('user_api_language.cart.delivery_fee'), 'value' => $cart_details['delivery_fee']),
			array('key' =>trans('user_api_language.cart.booking_fee'), 'value' => $cart_details['booking_fee']),
			array('key' =>trans('user_api_language.cart.tax'), 'value' => $cart_details['tax']),
			array('key' =>trans('user_api_language.cart.promo_amount'), 'value' => $order_details_data['promo_amount']),
			array('key' =>trans('user_api_language.cart.wallet_amount'), 'value' => $cart_details['wallet_amount']),
			array('key' =>trans('user_api_language.cart.tips'), 'value' => $cart_details['tips']),
			array('key' =>trans('user_api_language.cart.penalty'),'value' => (string) number_format(($cart_order->user_penality) , 2) ),
			array('key' =>trans('user_api_language.cart.total'), 'value' => (string) number_format (($total_amount),2) ) ,
		];	

		$data['takeaway'] = [
			array('key' =>trans('user_api_language.cart.subtotal'), 'value' => $cart_details['subtotal']),
			array('key' =>trans('user_api_language.cart.delivery_fee'), 'value' => $cart_details['delivery_fee']),
			array('key' =>trans('user_api_language.cart.booking_fee'), 'value' => $cart_details['booking_fee']),
			array('key' =>trans('user_api_language.cart.tax'), 'value' => $cart_details['tax']),
			array('key' =>trans('user_api_language.cart.promo_amount'), 'value' => $order_details_data['promo_amount']),
			array('key' =>trans('user_api_language.cart.wallet_amount'), 'value' => $cart_details['wallet_amount']),
			array('key' =>trans('user_api_language.cart.tips'), 'value' => '0.00'),
			array('key' =>trans('user_api_language.cart.penalty'),'value' => (string) number_format (($cart_order->user_penality) , 2) ),
			array('key' =>trans('user_api_language.cart.total'), 'value'  => (string) number_format (($total_amount) , 2) ),
		];

		$data['store'] = $cart_details['store'];
		$order_item = $cart_details['order_item'];

		foreach ($order_item as $order_item) {
			$order_item_modifier = collect($order_item['order_item_modifier']);
			$results = array();
			$order_item_modifier->map(function($item) use (&$results) {
				$order_item_modifier_item = collect($item['order_item_modifier_item']);
				return $order_item_modifier_item->map(function($item) use (&$results) {
					$results[] = [
						'id' 	=> $item['id'],
						'count' => $item['count'],
						'price' => (string) number_format($item['price'] * $item['count'],'2'),
						'name'  => $item['modifier_item_name'],
					];
					return [];
				});
			});
			
			$data['menu_item'][] = [
				'order_item_id' => $order_item['id'],
				'order_id' => $order_item['order_id'],
				'menu_item_id' => $order_item['menu_item_id'],
				'price' => $order_item['price'],
				'quantity' => $order_item['quantity'],
				'modifier_price' => $order_item['modifier_price'],
				'total_amount' => $order_item['total_amount'],
				'offer_price' => $order_item['offer_price'],
				'tax' => $order_item['tax'],
				'notes' => $order_item['notes'],
				'id' => $order_item['menu_item']['id'],
				'is_visible' => $order_item['menu_item']['is_visible'],
				'is_offer' => $order_item['menu_item']['is_offer'],
				'menu_id' => $order_item['menu_item']['menu_id'],
				'menu_category_id' => $order_item['menu_item']['menu_category_id'],
				'name' => $order_item['menu_name'],
				'description' => $order_item['menu_item']['description'],
				'tax_percentage' => $order_item['menu_item']['tax_percentage'],
				'type' => $order_item['menu_item']['type'],
				'status' => $order_item['menu_item']['status'],
				'menu_item_image' => $order_item['menu_item']['menu_item_image'],
				'menu_item_main_addon' => $results,
			];
		}
		// Order::where('id',$order_item['order_id'])->update(['promo_amount' => $order_details_data['promo_amount']]);
		$user_promocode = UsersPromoCode::WhereHas('promo_code', function ($q) {
			})
			->where('user_id', $user_details->id)
			->where('order_id', '0')
			->get();

		$final_promo_details = $user_promocode->map(function($user_promo) {
				return [
					'id' 			=> $user_promo->promo_code->id,
					'price'			=> $user_promo->promo_code->price,
					'code' 			=> $user_promo->promo_code->code,
					'expire_date' 	=> $user_promo->promo_code->expire_date,
					'default_promo' =>$user_promo->promo_default,
				];
			});

		return response()->json([
			'status_code' => '1',
			'status_message' =>trans('api_messages.cart.updated_successfully'),
			'cart_details' => $data,
			'promo_details' => $final_promo_details,
		]);
	}

	//testing purpose
	public function unicode_decode($data)
	{
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\\\1;",urldecode($data));
		return $str;
	}

	/**
	 * API for order item
	 *
	 * @return Response Json response with status
	 */
	public function clear_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$remove_order = OrderItem::with('order_item_modifier.order_item_modifier_item')->find($request->order_item_id);
		$remove_order_item = OrderItemModifier::where('order_item_id',$request->order_item_id)->get();

		if($remove_order_item) {
			foreach ($remove_order_item as $key => $value) {
				$order = $value->id;
				$remove_order_item_modifer = OrderItemModifierItem::whereIn('order_item_modifier_id',[$order])->delete();
			}
			OrderItemModifier::where('order_item_id',$request->order_item_id)->delete();
		}
		

		if ($remove_order) {
			$remove_order->delete();
		}

		$orderitem = OrderItem::where('order_id', $request->order_id)->count();

		if ($orderitem == 0) {
			$remove_order_delivery = OrderDelivery::where('order_id', $request->order_id)->first();
			if ($remove_order_delivery) {
				$remove_order_delivery->delete();
			}
			$order = Order::find($request->order_id);
			if ($order) {
				$remove_penality = PenalityDetails::where('order_id', $order->id)->first();
				if ($remove_penality) {
					$remove_penality->delete();
				}
				$order->delete();
				//ASAP
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = 0;
				$address->save();
			}

		}

		return response()->json([
			'status_message' => __('user_api_language.cart.removed_successfully_cart'),
			'status_code' => '1',
		]);
	}

	/**
	 * API for order with order item
	 *
	 * @return Response Json response with status
	 */
	public function clear_all_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$order = Order::where('user_id', $user_details->id)->status('cart')->first();
		
		if(is_null($order)) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.already_removed'),
			]);
		}

		try {
			\DB::beginTransaction();
			$order_items = OrderItem::where('order_id', $order->id)->get();

			$order_item_modifiers = OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->get();
			
			if($order_item_modifiers->count()) {
				$order_item_modifier_items = OrderItemModifierItem::whereIn('order_item_modifier_id',$order_item_modifiers->pluck('id')->toArray())->delete();
			}

			OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->delete();
			OrderItem::where('order_id', $order->id)->delete();
			OrderDelivery::where('order_id', $order->id)->delete();
			PenalityDetails::where('order_id', $order->id)->delete();
			Order::find($order->id)->delete();
			\DB::commit();
		}
		catch (\Exception $e) {
			\DB::rollback();
			return response()->json([
				'status_code' => '0',
				// 'status_message' => trans('api_messages.unable_to_remove'),
				'status_message' => $e->getMessage(),
			]);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.removed_successfully'),
		]);
	}
	

	/**
	 * API for Order history
	 *
	 * @return Response Json response with status
	 */

	public function pending_order_list(Request $request)
	{
		try 
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$order_list = Order::getAllRelation()->where('user_id', $user_details->id)->history()->orderBy('id', 'DESC')->paginate(PAGINATION);
			$total_page = $order_list->lastPage();
			$result = [];
			
			$order_list = $order_list->map(
				function ($item) {
					$menu_item = $item['order_item']->map(
						function ($item) {
							$order_item_modifier_item = $item->order_item_modifier->map(function($menu) {
								return $menu->order_item_modifier_item->map(function ($item) {
									return [	
										'id'	=> $item->id,			
										'count' => $item->count,
										'price' => (string) number_format($item->price * $item->count,'2'),
										'name'  => @$item->modifier_item_name,
									];
								});
								
							})->toArray();
							
							foreach ($order_item_modifier_item as $key => $value){
						        if (is_array($value)){
						            foreach($value as $keys =>$val){
						            	$result[] = $val;
						            }
						        }
					    	}
							return [
								'quantity' => $item['quantity'],
								'menu_item_id' => $item['menu_item']['id'],
								'item_image' => $item['menu_item']['menu_item_image'],
								'price' => $item['total_amount'],
								'menu_name' => $item['menu_name'],
								'menu_item_main_addon' => @$result ? $result : [],
								'type' => $item['menu_item']['type'],
								'status' => $item['menu_item']['status'],
								'review' => $item['review'] ? $item['review']['is_thumbs'] : 2,
							];
						}
					)->toArray();
					$rating = "0";
					$contact = '';

					if ($item->driver_id && $item->driver) {
						if ($item->driver->review) {
							$rating = $item->driver->review->user_driver_rating;
						}
						$contact = $item->driver->driver_contact;
					}

					$user_id 		= get_store_user_id($item['store']['id']);
					$store_address 	= get_store_address($user_id);
					$user_address 	= get_user_address($item['user_id']);
					$star_rating 	= '0.0';
					$is_rating 		= 0;
					$food_status 	= [];

					if ($item->status_text == 'completed') {
						$food_status[] = [
							'time' => $item->completed_at->format('h:i').' '.trans('user_api_language.monthandtime.'.$item->completed_at->format('a')),
							'status' => trans('user_api_language.orders.ready_to_eat'),
						];
					}

					if (($item->status_text == 'delivery' || $item->status_text == 'completed') && isset($item->order_delivery->started_at)) {
						$delivery_at = (string) date('h:i', strtotime($item->delivery_at)).' '.trans('user_api_language.monthandtime.'.date('a', strtotime($item->delivery_at)));
						$food_status[] = [
							'time' => $delivery_at,
							'status' => trans('user_api_language.orders.food_on_the_way'),
						];
					}
					

					if ($item->status_text == 'accepted' || $item->status_text == 'completed') {
						$food_status[] = [
							'time' => $item->accepted_at->format('h:i').' '.trans('user_api_language.monthandtime.'.$item->accepted_at->format('a')),
							'status' => trans('user_api_language.orders.preparing_your_food'),
						];

						$food_status[] = [
							'time' => $item->accepted_at->format('h:i').' '.trans('user_api_language.monthandtime.'.$item->accepted_at->format('a')),
							'status' => trans('user_api_language.orders.order_accepted'),
						];

						if($item->review !== Null){
							$is_rating = $item->review->user_atleast == 1 ? 1 : 0;
						}
						$star_rating = $item->review !== Null ? $item->review->star_rating : '0';
					}
					$show_date = ($item->order_status == 4) ? date('d F Y h:i a', strtotime($item['cancelled_at'])) : date('d F Y h:i a', strtotime($item['updated_at']));
					$total_amount = $item['total_amount'] ;
					$getOpenTime = $item['store']['store_time']['start_time'];
					$get_show_date = date('d', strtotime($show_date)).' '.trans('user_api_language.monthandtime.'.date('M', strtotime($show_date))).' '.date('Y', strtotime($show_date)).' '.date('h:i', strtotime($show_date)).' '.trans('user_api_language.monthandtime.'.date('a', strtotime($show_date)));
					$est_time = date('h:i', strtotime($item->est_delivery_time)).' '.trans('user_api_language.monthandtime.'.date('a', strtotime($item->est_delivery_time)));
					$currency_symbol = Currency::where('code',$item['currency_code'])->value('symbol');
					// dd($item->driver);
					return [
						'order_id' => $item['id'],
						'total_amount' => $total_amount,
						'tips'         =>$item['tips'],
						'delivery_type' =>$item['delivery_type'],
						'subtotal' => $item['subtotal'],
						'delivery_fee' => $item['delivery_fee'],
						'booking_fee' => $item['booking_fee'],
						'tax' => $item['tax'],
						'wallet_amount' => $item['wallet_amount'],
						'promo_amount' => $item['promo_amount'],
						'order_status' => $item['status'],
						'name' => $item['store']['name'],
						'store_id' => $item['store']['id'],
						'store_status' => $item['store']['status'],
						'store_phone_number' => $item['store']['user']['mobile_number_phone_code']	,
						'store_open_time' => $getOpenTime,
						'status' => $item['status'],
						'store_banner' => $item['store']['banner'],
						'date' => $get_show_date ,
						'menu' => $menu_item,
						'total_seconds' => $item->user_total_seconds,
						'remaining_seconds' => $item->user_remaining_seconds,
						'user_status_text' => $item->user_status_text,
						'est_complete_time' => $est_time,
						'driver_name' => $item->driver ? $item->driver->user->name : "",
						'driver_image' => $item->driver ? $item->driver->user->user_image_url : "",
						'vehicle_type' => $item->driver ? $item->driver->vehicle_type_details->name : '',
						'vehicle_number' => $item->driver ? $item->driver->vehicle_number : '',
						'driver_rating' => $rating,
						'driver_contact' => $contact,
						'order_type' => $item['schedule_status'],
						'delivery_time' => $item['schedule_time'],
						'delivery_options' => $item->user->user_address ? $item->user->user_address->delivery_options : '',
						'apartment' => $item->user->user_address ? $item->user->user_address->apartment : '',
						'delivery_note' => $item->user->user_address ? $item->user->user_address->delivery_note : '',
						'order_delivery_status' => $item->order_delivery ? $item->order_delivery['status'] : '-1',
						'pickup_latitude' =>$store_address ? $store_address->latitude :'',
						'pickup_longitude' =>$store_address ? $store_address->longitude : '',
						'store_location' => $store_address ? $store_address->address : '',
						'drop_latitude' => $user_address ? $user_address->latitude : '', 
						'drop_longitude' => $user_address ? $user_address->longitude : '',
						'drop_address' => $user_address ? $user_address->address1 : '',
						'driver_latitude' => $item->driver ? $item->driver->latitude : "",
						'driver_longitude' => $item->driver ? $item->driver->longitude : "",
						'is_rating' => $is_rating,
						'star_rating' => $star_rating,
						'food_status' => $food_status,
						'store_closed' => $item['store']['store_time']['closed'],
						'store_next_time' => $item['store']['store_next_opening'],
						'penality' => $item['user_penality'],
						'applied_penality' => $item['user_applied_penality'],
						'notes' => (string) $item['user_notes'],
						'invoice' => [
							array('key' =>trans('user_api_language.cart.subtotal'), 'value' => $item['subtotal']),
							array('key' =>trans('user_api_language.cart.delivery_fee'), 'value' => $item['delivery_fee']),
							array('key' =>trans('user_api_language.cart.booking_fee'), 'value' => $item['booking_fee']),
							array('key' =>trans('user_api_language.cart.tax'), 'value' => $item['tax']),
							array('key' =>trans('user_api_language.cart.promo_amount'), 'value' => $item['promo_amount']),
							array('key' =>trans('user_api_language.cart.wallet_amount'), 'value' => $item['wallet_amount']),
							array('key' =>trans('user_api_language.cart.tips'), 'value' => $item['tips']),
							array('key' =>trans('user_api_language.cart.total'), 'value' => $item['total_amount']),
						],
						'currency_code' => $item['currency_code'],
						'currency_symbol' => html_entity_decode($currency_symbol),
					];
				}
			);
			// dd($total_page);	
			$order_list = $order_list->toArray();
			return response()->json(
				[
					'status_code' => '1',
					'status_message' => trans('api_messages.orders.successfully'),
					'current_page' => (int) $request->page,
					'total_page_count' => $total_page,
					'order_history' => $order_list,
				]
			);	

		} catch (\Exception $e) {
			return response()->json(
				[
					'status_code' 		=> '0',
					'status_message' 	=> $e->getMessage(),
				]);
		}
	}

	/**
	 * API for upcoming order
	 *
	 * @return Response Json response with status
	 */
	public function upcoming_order_list(Request $request)
	{
		try 
		{
			$user_details 	= JWTAuth::parseToken()->authenticate();
			$upcoming 		= Order::getAllRelation()->where('user_id', $user_details->id)->upcoming()->orderBy('id', 'DESC')->paginate(PAGINATION);
			$total_page 	= $upcoming->lastPage();

			$result = [];
			$upcoming = $upcoming->map(
				function ($item) {
					$upcoming_menu_item = $item['order_item']->map(
						function ($item) {
							$order_item_modifier_item = $item->order_item_modifier->map(function($menu) {
								  	return $menu->order_item_modifier_item->map(function ($item) {
											return[	
												'id'	=> $item->id,			
												'count' => $item->count,
												'price' => (string) number_format($item->price * $item->count,'2'),
												'name'  => @$item->modifier_item_name,
											];
										});
										
									})->toArray();

									foreach ($order_item_modifier_item as $key => $value){
									    if (is_array($value)){
									        foreach($value as $keys =>$val){
									          $result[] = $val;
									        }
									    }
								   	}

							return [

								'quantity' => $item['quantity'],
								'menu_item_id' => $item['menu_item']['id'],
								'item_image' => $item['menu_item']['menu_item_image'],
								'price' => $item['total_amount'],
								'menu_name' => $item['menu_name'],
								'menu_item_main_addon' => @$result ? $result : [],
								'type' => $item['menu_item']['type'],
								'status' => $item['menu_item']['status'],

							];
						}
					)->toArray();

					$rating = 0;
					$contact = '';

					if ($item->driver_id && $item->driver) {
						if ($item->driver->review) {
							$rating = $item->driver->review->user_driver_rating;
						}
						$contact = $item->driver->driver_contact;
					}

					$user_id 		= get_store_user_id($item['store']['id']);
					$store_address 	= get_store_address($user_id);
					$user_address 	= get_user_address($item['user_id']);
					$food_status 	= array();

					if ($item->status_text == 'completed') {
						$food_status[] = [
							'time' => $item->completed_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->completed_at->format('a')),
							'status' => trans('api_messages.orders.ready_to_eat'),
						];
					}

					if (($item->status_text == 'delivery' || $item->status_text == 'completed') && isset($item->order_delivery->started_at)) {

						$delivery_at = (string) date('h:i', strtotime($item->delivery_at)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($item->delivery_at)));

						$food_status[] = [
							'time' => $delivery_at,
							'status' => trans('api_messages.orders.food_on_the_way'),
						];
					}

					if ($item->schedule_status == '0' && ($item->status_text == 'accepted' || $item->status_text == 'completed' || $item->status_text == 'delivery')) {

						$food_status[] = [
							'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
							'status' => trans('api_messages.orders.preparing_your_food'),
						];

					}

					if ($item->status_text == 'accepted' || $item->status_text == 'completed' || $item->status_text == 'delivery') {

						$food_status[] = [
							'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
							'status' => trans('api_messages.orders.order_accepted'),
						];
					}

					$date = date('Y-m-d', strtotime($item['created_at']));
					$schedule_time = date('Y-m-d', strtotime($item['schedule_time']));

					if ($item['schedule_status'] == 0) {
						if ($date == date('Y-m-d')) {
							$date = 'Today' . ' ' . date('M d h:i a', strtotime($item['created_at']));
						} else if ($date == date('Y-m-d', strtotime("+1 days"))) {
							$date = 'Tomorrow' . ' ' . date('M d h:i a', strtotime($item['created_at']));
						} else { $date = date('l M d h:i a', strtotime($item['created_at']));}

					} else {
						$time_Stamp = strtotime($item['schedule_time']) + 1800;
						$del_time = date('h:i a', $time_Stamp);
						$common = date('M d h:i a', strtotime($item['schedule_time']));

						if ($schedule_time == date('Y-m-d')) {
							$date = 'Today' . ' ' . $common . ' - ' . $del_time;
						} else if ($schedule_time == date('Y-m-d', strtotime("+1 days"))) {
							$date = 'Tomorrow'. ' ' . $common . ' - ' . $del_time;
						} else {
							$date = date('l M d h:i a', strtotime($item['schedule_time'])) . ' - ' . $del_time;
						}
					}
					if ($item->status_text == "pending") {
						$est_completed_time = $item->est_delivery_time;
					} else if($item->status_text =='takeaway' && $item->delivery_type == 'takeaway'){
						$est_completed_time = $item->delivery_at;
					}
					else {
						$est_completed_time = $item->completed_at;
					}
					
					$date = date('Y-m-d H:i:s', strtotime($item['created_at']));
					$get_show_date = date('d', strtotime($date)).' '.trans('api_messages.monthandtime.'.date('M', strtotime($date))).' '.date('Y', strtotime($date)).' '.date('h:i', strtotime($date)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($date)));

					$est_time = date('h:i', strtotime($est_completed_time)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($est_completed_time)));

					if(is_null($item['schedule_time'])){
						$get_delivery_time = null;
					}else{
					$delivery_time = date('Y-m-d H:i:s', strtotime($item['schedule_time']));
					$get_delivery_time = date('d', strtotime($delivery_time)).' '.trans('api_messages.monthandtime.'.date('M', strtotime($delivery_time))).' '.date('Y', strtotime($delivery_time)).' '.date('h:i', strtotime($delivery_time)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));						
					}
					$currency_symbol = Currency::where('code',$item['currency_code'])->value('symbol');
					$pickup_time = date("H:i:s", strtotime($item->est_preparation_time) + strtotime($item->created_at));
					
					return [
						'order_id' => $item['id'],
						'total_amount' => $item['total_amount'],
						'tips' => $item['tips'],
						'delivery_type' =>$item['delivery_type'],
						'subtotal' => $item['subtotal'],
						'delivery_fee' => $item['delivery_fee'],
						'booking_fee' => $item['booking_fee'],
						'tax' => $item['tax'],
						'wallet_amount' => $item['wallet_amount'],
						'promo_amount' => $item['promo_amount'],
						'order_status' => $item['status'],
						'name' => $item['store']['name'],
						'store_id' => $item['store']['id'],
						'store_status' => 1,
						'store_phone_number' => $item['store']['user']['mobile_number_phone_code']	,
						'store_open_time' => $item['store']['store_time']['start_time'],
						'status' => $item['status'],
						'store_banner' => $item['store']['banner'],
						'order_type' => $item['schedule_status'],
						'delivery_time' => $get_delivery_time,
						'date' => $get_show_date,
						'menu' => $upcoming_menu_item,
						'total_seconds' => $item->user_total_seconds,
						'remaining_seconds' => $item->user_remaining_seconds,
						'user_status_text' => $item->user_status_text,
						'est_complete_time' => $est_time,
						'driver_name' => $item->driver ? $item->driver->user->name : "",
						'driver_image' => $item->driver ? $item->driver->user->user_image_url : "",
						'vehicle_type' => $item->driver ? $item->driver->vehicle_type_details->name : '',
						'vehicle_number' => $item->driver ? $item->driver->vehicle_number : '',
						'driver_rating' => $rating,
						'driver_contact' => $contact,
						'delivery_options' => $item->user->user_address ? $item->user->user_address->delivery_options : '',
						'apartment' => $item->user->user_address ? $item->user->user_address->apartment : '',
						'delivery_note' => $item->user->user_address ? $item->user->user_address->delivery_note : '',
						'order_delivery_status' => $item->order_delivery ? $item->order_delivery['status'] : '-1',

						'pickup_latitude' => $store_address->latitude,
						'pickup_longitude' => $store_address->longitude,
						'store_location' => $store_address->address,
						'drop_latitude' => $user_address->latitude,
						'drop_longitude' => $user_address->longitude,
						'drop_address' =>$user_address->address1,
						'driver_latitude' => $item->driver ? $item->driver->latitude : "",
						'driver_longitude' => $item->driver ? $item->driver->longitude : "",
						'food_status' => $food_status,
						'store_closed' => 1,
						'store_next_time' => $item['store']['store_next_opening'],
						'penality' => $item['user_penality'],
						'invoice' => [
							array('key' =>trans('api_messages.cart.subtotal'), 'value' => $item['subtotal']),
							array('key' =>trans('api_messages.cart.delivery_fee'), 'value' => $item['delivery_fee']),
							array('key' =>trans('api_messages.cart.booking_fee'), 'value' => $item['booking_fee']),
							array('key' =>trans('api_messages.cart.tax'), 'value' => $item['tax']),
							array('key' =>trans('api_messages.cart.promo_amount'), 'value' => $item['promo_amount']),
							array('key' =>trans('api_messages.cart.wallet_amount'), 'value' => $item['wallet_amount']),
							array('key' =>trans('api_messages.cart.tips'), 'value' => $item['tips']),
							array('key' =>trans('api_messages.cart.total'), 'value' => $item['total_amount']),
						],
						'currency_code' => $item['currency_code'],
						'currency_symbol' => html_entity_decode($currency_symbol),
					];
				}
			);
			
			$upcoming = $upcoming->toArray();
			return response()->json(
				[
					'status_code' 		=> '1',
					'status_message' 	=> trans('user_api_language.orders.successfully'),
					'current_page' 		=> (int) $request->page,
					'total_page_count' 	=> $total_page,
					'upcoming' 			=> $upcoming,
				]
			);

		} catch (\Exception $e) {
			return response()->json(
				[
					'status_code' 		=> '1',
					'status_message' 	=> $e->getMessage(),
				]);
		}
	}


	/**
	 * API for create a customer id  based on card details using stripe payment gateway
	 *
	 * @return Response Json response with status
	 */
	public function add_card_details(Request $request)
	{

		$rules = array(
            'intent_id'			=> 'required',
        );

        $attributes = array(
            'intent_id'     	=> 'Setup Intent Id',
        );

        $validator = Validator::make($request->all(), $rules,$attributes);
       
        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

		$user_details = JWTAuth::parseToken()->authenticate();
		
		$stripe_payment = resolve('App\Repositories\StripePayment');

		$payment_details = UserPaymentMethod::firstOrNew(['user_id' => $user_details->id]);

		$setup_intent = $stripe_payment->getSetupIntent($request->intent_id);
		
		if($setup_intent->status != 'succeeded') {
			return response()->json([
				'status_code' => '0',
				'status_message' => $setup_intent->status,
			]);
		}

		if($payment_details->stripe_payment_method != '') {
			$stripe_payment->detachPaymentToCustomer($payment_details->stripe_payment_method);
		}

		$stripe_payment->attachPaymentToCustomer($payment_details->stripe_customer_id,$setup_intent->payment_method);

		$payment_method = $stripe_payment->getPaymentMethod($setup_intent->payment_method);
		$payment_details->stripe_intent_id = $setup_intent->id;
		$payment_details->stripe_payment_method = $setup_intent->payment_method;
		$payment_details->brand = $payment_method['card']['brand'];
		$payment_details->last4 = $payment_method['card']['last4'];
		$payment_details->save();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('user_api_language.cart.added_success'),
			'brand' 			=> $payment_details->brand,
			'last4' 			=> strval($payment_details->last4),
		]);
	}

	/**
	 * API for payment card details
	 *
	 * @return Response Json response with status
	 */
	public function get_card_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$stripe_payment = resolve('App\Repositories\StripePayment');

		$payment_details = UserPaymentMethod::firstOrNew(['user_id' => $user_details->id]);

		if(!isset($payment_details->stripe_customer_id)) {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->stripe_customer_id = $stripe_customer->customer_id;
			$payment_details->save();
		}
		$customer_id = $payment_details->stripe_customer_id;

		// Check New Customer if customer not exists
		$customer_details = $stripe_payment->getCustomer($customer_id);
		if($customer_details->status == "failed" && $customer_details->status_message == "resource_missing") {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->stripe_customer_id = $stripe_customer->customer_id;
			$payment_details->save();
			$customer_id = $payment_details->stripe_customer_id;
		}

		$status_code = "1";
		if($payment_details->stripe_intent_id == '') {
			$status_code = "2";
		}

		$setup_intent = $stripe_payment->createSetupIntent($customer_id);
		if($setup_intent->status == 'failed') {
			return response()->json([
				'status_code' 		=> "0",
				'status_message' 	=> $setup_intent->status_message,
			]);
		}

		return response()->json([
			'status_code' 		=> $status_code,
			'status_message' 	=> __('user_api_language.cart.listed_sucess'),
			'intent_client_secret'=> $setup_intent->intent_client_secret,
			'brand' 			=> $payment_details->brand ?? '',
			'last4' 			=> (string)$payment_details->last4 ?? '',
		]);
	}

	/**
	 * API for filter
	 *
	 * @return Response Json response with status
	 */
	public function filter(Request $request)
	{
		$user_details ='';

		if($request->filled('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
		}

		$address_details = $this->address_details();

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];

		$perpage = 7;

		$dietary = '';
		$price = [];

		if ($request->price) {
			$price = explode(',', $request->price);
		}

		if ($request->dietary || $request->dietary != '') {
			$dietary = explode(',', $request->dietary);
		}

		// if($request->delivery_type == 'both')
		// {
		// 	$delivery_type = explode(',','takeaway,delivery');
		// }

		$delivery_type = $request->delivery_type;	

		$type = $request->type;
		$sort = $request->sort;
		$service_type = $request->service_type;
		$search = [
			0 => 'Filter',
			1 => 'Favourite',
			2 => 'Popular',
			3 => 'Under',
			4 => 'New Store',
			5 => 'More Store',
		];

		$user = User::with(['store' => function ($query) use ($price, $dietary, $type, $sort,$service_type,$delivery_type) {
				$query->with(['store_cuisine', 'store_preparation_time', 'wished' => function ($query) {
					$query->select('store_id', DB::raw('count(store_id) as count'))->groupBy('store_id');
				}, 'review']);
			}])
			->Type('store')
			->whereHas('store', function ($query) use ($latitude, $longitude,$service_type,$delivery_type) {
				if($delivery_type != 'both'){
					$query->location($latitude, $longitude)
					->whereHas('store_time', function ($query) {
					})->where('service_type',$service_type)
					->Where('delivery_type', 'like', '%' . $delivery_type . '%');
				}
				else
				{
					$query->location($latitude, $longitude)
					->whereHas('store_time', function ($query) {
					})->where('service_type',$service_type);
				}
			})->status();
			
		if ($type == 0) {
			$store = $user->whereHas('store',function ($query) use ($price, $dietary, $type, $sort) {
				if ($this->count($price) > 0) {
					$query->whereIn('price_rating', $price);
				}

				if ($sort == 0 && $sort != null) {
					$query->where('recommend', '1');
				}

				if ($sort == 1) {
					$query->whereHas('wished', function ($query) {
					});
				}

				if ($this->count($dietary) > 0 && $dietary != '') {
					$query->whereHas('store_cuisine', function ($query) use ($dietary) {
						$query->whereIn('cuisine_id', $dietary);
					});
				}
			});

			if ($sort == 2) {
				$rating = (clone $store)->get();
				$collection = collect($rating)->sortByDesc(function ($rating) {
					return $rating->store->review->store_rating;
				});

				$store = $collection->forPage($request->page, $perpage)->values();

				$page_count = round(ceil($store->count() / $perpage));
			}
			else if ($sort == 3) {
				$delivery_time = (clone $store)->get();
				$collection = collect($delivery_time)->sortBy(function ($delivery_time) {
					return $delivery_time->store->convert_mintime;
				});

				$store = $collection->forPage($request->page, $perpage)->values();
				$page_count = round(ceil($store->count() / $perpage));
			}
			else {
				$store = $store->paginate($perpage);
				$page_count = $store->lastPage();
			}
		}
		else {
			if ($type == 2) {
				$store = Wishlist::select('store_id', DB::raw('count(store_id) as count'))->with(['store' => function ($query) use ($latitude, $longitude) {
					$query->with(['store_cuisine', 'review', 'user', 'store_time', 'store_offer']);
				}])
				->whereHas('store', function ($query) use ($latitude, $longitude,$service_type) {

					$query->where('service_type',$service_type)->UserStatus()->location($latitude, $longitude)->whereHas('store_time', function ($query) {
					});
				})
				->groupBy('store_id')
				->orderBy('count', 'desc')
				->paginate($perpage);
				$page_count = $store->lastPage();
			}
			else {
				$date = \Carbon\Carbon::today()->subDays(10);
				$min_time = $request->min_time ? convert_format($request->min_time) : '00:20:00';
				$store = $user->whereHas('store',function ($query) use ($type, $min_time, $date) {
					if ($type == 3) {
						$query->where('max_time', $min_time);
					}
					else if ($type == 4) {
						$query->where('created_at', '>=', $date);
					}
					else {
						// more store
					}
				})->paginate($perpage);
				
				$page_count = $store->lastPage();
			}
		}

		$user = $this->common_map($store);

		$user = (count($user) > 0) ? $user->toArray() : array();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('user_api_language.success'),
			'store' 			=> $user,
			'page_count' 		=> $page_count,
			'current_page' 		=> (int) $request->page,
			'search_text' 		=> $search[$type],
		]);
	}

	/**
	 * API for cancel order who place the order
	 *
	 * @return Response Json response with status
	 */
	public function cancel_order(Request $request, PaymentController $PaymentController)
	{
		$order = Order::where('id', $request->order_id)->first();

		if ($order->status == '2' || $order->status == '4') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('user_api_language.user.already_cancelled'),
			]);
		}

		if ($order->schedule_status == 0) {
			$rules = [
				'order_id' => 'required|exists:order,id,status,' . $order->statusArray['pending'],
			];

			$messages = [
				'order_id.exists' => trans('user_api_language.user.your_order_progress'),

			];

			$validator = Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]);
			}
		}

		$user_details = JWTAuth::parseToken()->authenticate();

		$order->cancel_order("user", $request->cancel_reason, $request->cancel_message);
		
		$PaymentController->refund($request, 'Cancelled',$order->user_id,'user');

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.user.order_cancel'),
		]);
	}

	/**
	 * API for user review details and issue type
	 *
	 * @return Response Json response with status
	 */
	public function user_review(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$item = Order::getAllRelation()->where('user_id', $user_details->id)->where('id', $request->order_id)->first();

		if (!$item) {
			return response()->json([

				'status_message' => 'Empty',

				'status_code' => '0',

			]);
		}

		$menu_item = $item['order_item']->map(
			function ($item) {
				return [
					'quantity' => $item['quantity'],
					'order_item_id' => $item['id'],
					'menu_item_id' => $item['menu_item']['id'],
					'item_image' => $item['menu_item']['menu_item_image'],
					'price' => $item['menu_item']['price'],
					'menu_name' => $item['menu_name'],
					'type' => $item['menu_item']['type'],
					'status' => $item['menu_item']['status'],

				];
			}
		)->toArray();

		$driver_image = '';
		$driver_id = 0;
		$driver_name = '';

		if ($item->driver_id && $item->driver) {

			$driver_image = $item->driver->user->user_image_url;
			$driver_name = $item->driver->user->name;
			$driver_id = $item->driver_id;

		}

		$issue_user_menu_item = [];
		$issue_user_driver = [];

		$issue_user_menu_item = IssueType::TypeText('user_menu_item')->get();
		$issue_user_driver = IssueType::TypeText('user_driver')->get();

		$order_details = [

			'order_id' => $item['id'],
			'total_amount' => $item['total_amount'],
			'subtotal' => $item['subtotal'],
			'delivery_fee' => $item['delivery_fee'],
			'tax' => $item['tax'],
			'order_status' => $item['status'],
			'name' => $item['store']['name'],
			'store_id' => $item['store']['id'],
			'store_open_time' => $item['store']['store_time']['start_time'],
			'status' => $item['status'],
			'store_banner' => $item['store']['banner'],
			'date' => date('d F Y H:i a', strtotime($item['updated_at'])),
			'menu' => $menu_item,
			'driver_image' => $driver_image,
			'driver_name' => $driver_name,
			'driver_id' => $driver_id,
			'issue_user_menu_item' => $issue_user_menu_item,
			'issue_user_driver' => $issue_user_driver,
		];

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('user_api_language.sucess'),
			'user_review_data' 	=> $order_details,
		]);

	}

	/**
	 * API for Add rating in a order to menu item and delivery from user
	 *
	 * @return Response Json response with status
	 */
	public function add_user_review()
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$request = request();

		$order = Order::getAllRelation()->where('id', $request->order_id)->first();

		// $rating = str_replace('\\', '', $request->rating);


		$rating = str_replace('\\', '', $request->rating);

		$rating = json_decode($rating);
		
		$order_id = $order->id;

		$food_item = $rating->food;

		//Rating for Menu item
		if ($food_item) {
			foreach ($food_item as $key => $value) {
				$review = new Review;
				$review->order_id = $order_id;
				$review->type = $review->typeArray['user_menu_item'];
				$review->reviewer_id = $user_details->id;
				$review->reviewee_id = $value->id;
				$review->is_thumbs = $value->thumbs;
				$review->order_item_id = $value->order_item_id;
				$review->comments = $value->comment ?: "";
				$review->save();
				if ($value->reason) {
					$issues = explode(',', $value->reason);
					if ($request->thumbs == 0 && $this->count($issues)) {
						foreach ($issues as $issue_id) {
							$review_issue = new ReviewIssue;
							$review_issue->review_id = $review->id;
							$review_issue->issue_id = $issue_id;
							$review_issue->save();
						}
					}
				}
			}
		}
		if($order->delivery_type == 'delivery')
		{
			// Rating for driver
			if ($this->count(get_object_vars($rating->driver)) > 0) {
				$review = new Review;
				$review->order_id = $order_id;
				$review->type = $review->typeArray['user_driver'];
				$review->reviewer_id = $user_details->id;
				$review->reviewee_id = $order->driver_id;
				$review->is_thumbs = $rating->driver->thumbs;
				$review->comments = $rating->driver->comment ?: "";
				$review->save();
				if ($rating->driver->reason) {
					$issues = explode(',', $rating->driver->reason);
					if ($rating->driver->thumbs == 0 && $this->count($issues)) {
						foreach ($issues as $issue_id) {
							$review_issue = new ReviewIssue;
							$review_issue->review_id = $review->id;
							$review_issue->issue_id = $issue_id;
							$review_issue->save();
						}
					}
				}
			}
		}
		//Rating for Store
		if ($this->count(get_object_vars($rating->store)) > 0) {
			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_store'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->store_id;
			$review->rating = $rating->store->thumbs;
			$review->comments = $rating->store->comment ?: "";
			$review->save();
		}
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.cart.updated_successfully'),
		]);
	}

	/**
	 * API for wallet amount
	 *
	 * @return Response Json response with status
	 */
	public function add_wallet_amount(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$amount = $request->amount;
		$currency_code = isset($user_details->currency_code) ? $user_details->currency_code->code:DEFAULT_CURRENCY;

		$stripe_payment = resolve('App\Repositories\StripePayment');
	
		if($request->payment_type == 1){

			if($request->filled('payment_intent_id')) {
				$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
			}
			else {
				$user_payment_method = UserPaymentMethod::where('user_id', $user_details->id)->first();
				$paymentData = array(
					"amount" 		=> $amount * 100,
					'currency' 		=> $currency_code,
					'description' 	=> 'Wallet Payment by '.$user_details->first_name,
					"customer" 		=> $user_payment_method->stripe_customer_id,
					'payment_method'=> $user_payment_method->stripe_payment_method,
			      	'confirm' 		=> true,
			      	'off_session' 	=> true,
				);
				$payment_result = $stripe_payment->createPaymentIntent($paymentData);
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
					'status_code' 	=> '0',
					'status_message'=> $payment_result->status_message,
				]);
			}
		}

		if($request->payment_type == 2){

			$converted_amount = currencyConvert($user_details->currency_code->code,PAYPAL_CURRENCY_CODE,floatval($amount));
			$payment_result = $stripe_payment->PaypalPayment($converted_amount,$request->pay_key);
			if(!$payment_result->status) {
				return response()->json([
			        'status_code' => '0',
			         'status_message' => $payment_result->status_message,
			    ]);
			}
		}

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
		$payment->transaction_id = $payment_result->transaction_id;
		$payment->amount = $amount;
		$payment->status = 1;
		$payment->type = 1;
		$payment->currency_code = $currency_code;
		$payment->save();
		
		$wallet_details = Wallet::where('user_id', $user_details->id)->first();
		
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.success'),
			'wallet_amount' => $wallet_details->amount,
			'currency_code' => $wallet_details->currency_code,
		]);
	}


	/**
	 * API for Wishlist
	 *
	 * @return Response Json response with status
	 */
	public function wishlist(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();

		list('latitude' => $latitude, 'longitude' => $longitude) = collect($user->user_address)->only(['latitude', 'longitude'])->toArray();

		$wishlist = Wishlist::selectRaw('*,store_id as ids, (SELECT count(store_id) FROM wishlist WHERE store_id = ids) as count')->with(
			['store' => function ($query) use ($latitude, $longitude) {
				$query->with(['store_cuisine', 'review', 'user', 'store_time', 'store_offer']);
			}]
		)->whereHas('store', function ($query) use ($latitude, $longitude) {
			$query->UserStatus()->location($latitude, $longitude)->whereHas('store_time', function ($query) {
			});

		})->where('user_id', $user_details->id)->paginate(PAGINATION);

		$total_page = $wishlist->lastPage();
		$wishlist = $this->common_map($wishlist);

		return response()->json(
			[
				'status_code' 		=> '1',
				'status_message'	=> __('user_api_language.sucess'),
				'total_page' 		=> $total_page,
				'current_page' 		=> (int) $request->page,
				'wishlist' 			=> $wishlist ? $wishlist : [],
			]
		);
	}

	/**
	 * API for Info window
	 *
	 * @return Response Json response with status
	 */
	public function info_window(Request $request)
	{
		if(request()->token)
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$user_address = get_user_address($user_details->id);
			list('latitude' => $user_latitude, 'longitude' => $user_longitude, 'address' => $user_location) = collect($user_address)->only(['latitude', 'longitude', 'address'])->toArray();
		}
		else
		{

			$user_latitude =  request()->latitude;
			$user_longitude = request()->longitude;
			$user_location =  request()->address;

		}

		$restauant_user_id = get_store_user_id($request->id);
		
		$restauant_address = get_store_address($restauant_user_id);

		list('latitude' => $store_latitude, 'longitude' => $store_longitude, 'address' => $store_location) = collect($restauant_address)->only(['latitude', 'longitude', 'address'])->toArray();

		$store_time = StoreTime::where('store_id', $request->id)->orderBy('day', 'asc')->get();

		$store = Store::find($request->id);
		$store_name = $store->name;

		return response()->json(
			[
				'status_message' => __('user_api_language.sucess'),
				'status_code' => '1',
				'user_latitude' => $user_latitude,
				'user_longitude' => $user_longitude,
				'user_location' => $user_location,
				'store_latitude' => $store_latitude,
				'store_longitude' => $store_longitude,
				'store_location' => $store_location,
				'store_time' => $store_time,
				'store_name' => $store_name,
			]
		);

	}

	public function common_map($query) {

		if(isset(request()->token))
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$user = User::where('id', $user_details->id)->first();

			list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type, 'delivery_time' => $delivery_time) =
			collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_time'])->toArray();

		}
		else
		{
			$user_details = '';
			$latitude = request()->latitude;
			$longitude = request()->longitude;
			$order_type = request()->order_type;
			$delivery_time = request()->delivery_time;
			
		}

		

		return $query->map(
			function ($item) use ($user_details, $order_type, $delivery_time) {
				// dd($item['store']['store_cuisine']);
				$store_cuisine = $item['store']['store_cuisine']->map(
					function ($item) {
						return $item['cuisine_name'] ?? '';
					}
				)->toArray();


				if($user_details)
					$wishlist = $item['store']->wishlist($user_details->id, $item['store']['id']);
				else
					$wishlist = 0;



				return [

					'order_type' => $order_type,
					'delivery_time' => $delivery_time,
					'store_id' => $item['store']['id'],
					'name' => $item['store']['name'],
					'category' => implode(',', $store_cuisine),
					'banner' => $item['store']['banner'],
					'min_time' => $item['store']['convert_mintime'],
					'max_time' => $item['store']['convert_maxtime'],
					'store_rating' => $item['store']['review']['store_rating'],
					'price_rating' => $item['store']['price_rating'],
					'average_rating' => $item['store']['review']['average_rating'],
					'wished' => $wishlist,
					'status' => $item['store']['status'],
					'store_open_time' => $item['store']['store_time']['start_time'],
					'store_next_time' => $item['store']['store_next_opening'],
					'store_closed' => $item['store']['store_time']['closed'],
					'delivery_type' => $item['store']['delivery_type_array'],
					'store_offer' => $item['store']['store_offer']->map(

						function ($item) {

							return [

								'title' => $item->offer_title,
								'description' => $item->offer_description,
								'percentage' => $item->percentage,

							];
						}
					),

				];
			}
		);
	}

	/**
	 * Default user address
	 */
	public function address_details()
	{
		
		if(isset(request()->token)) {
			$user_details = JWTAuth::toUser(request()->token);		
			$user = User::where('id', $user_details->id)->first();
			return list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type, 'delivery_time' => $delivery_time) = collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_time'])->toArray();
		} else {
			return ['latitude' => request()->latitude, 'longitude' => request()->longitude, 'order_type' => request()->type, 'delivery_time' => request()->delivery_time ];
		}
	}

	public function count($array)
	{
 
        if(is_array($array))
        {
            $sum = 0;
            foreach($array as $ar)
            {
               $sum+= 1;
            }
            return $sum;
        }

        return 0;
	}

	/**
	 * get_payment_methods
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function getPaymentMethods(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		
		$payment_methods_avail = site_setting('payment_methods');
		$payment_methods_avail = explode(',', $payment_methods_avail);

		$payment_methods = collect(PAYMENT_METHODS);
        $payment_methods = $payment_methods->reject(function($value) use($payment_methods_avail) {
            $is_enabled = in_array($value['value'],$payment_methods_avail);
            return ($is_enabled != '1');
        });

        $is_wallet = $request->is_wallet == "1";
        $default_paymode = 'cash';

        $payment_list = array();

        $payment_methods->each(function($payment_method) use(&$payment_list, $default_paymode, $user_details, $is_wallet) {

            if($payment_method['key'] == 'cash' && $is_wallet) {
                $skip_payment = true;
            }

            $payment_method['value'] = \Lang::get('api_messages.'.$payment_method['value']);
            $payment_method['brand'] = '';
            if($payment_method['key'] == 'stripe') {
                $payment_details = UserPaymentMethod::where('user_id', $user_details->id)->where('stripe_payment_method','!=',NULL)->first();
                if($payment_details != '') {
                    $last4  = strval($payment_details->last4);
                    $payment_method['value'] = 'xxxx xxxx xxxx '.$last4;
                    $payment_method['brand'] = $payment_details->brand;
                    $stripe_card = array(
                        "key"           => "stripe_card",
                        "value"         => \Lang::get('api_messages.change_debit_card'),
                        "is_default"    => false,
                        "icon"          => asset("images/icon/stripe.png"),
                    );
                }
                else {
                    $stripe_card = array(
                        "key"           => "stripe_card",
                        "value"         => \Lang::get('api_messages.add_debit_card'),
                        "is_default"    => ($default_paymode == $payment_method['key']),
                        "icon"          => asset("images/icon/stripe.png"),
                    );
                    $skip_payment = true;
                }
            }

            if(!isset($skip_payment)) {
                $payMethodData = array(
                    "key"       => $payment_method['key'],
                    "value"     => $payment_method['value'],
                    "icon"      => $payment_method['icon'],
                    "brand"     => $payment_method['brand'],
                    "is_default"=> ($default_paymode == $payment_method['key']),
                );
                array_push($payment_list, $payMethodData);
            }
            
            if(isset($stripe_card)) {
                array_push($payment_list, $stripe_card);
            }
        });
        $wallet_check = in_array("Wallet", $payment_methods_avail) ? '1' : '0';
		$results = array('status_message' => __('user_api_language.sucess'),'status_code' => '1','payment_list' => $payment_list,'wallet'=>$wallet_check);
		return response()->json($results);
	}

	public function new_store_details(Request $request)
	{
		$page = (int) $request->page > 0 ? $request->page: 1;
		$per_page = $request->per_page ?? 10;
		if($request->has('token')) 
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			if (isset($request->order_type)) {
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = $request->order_type;
				$address->save();
			}
		}

		$rules = array('store_id' => 'required');
		$messages = array ('required' => ':attribute'.trans('api_messages.register.field_is_required'));
		$attributes = array('store_id' => trans('api_messages.store.store_id'));

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);
		if ($validator->fails()) {
		return response()->json([
					'status_code' => '0',
					'status_message' => $validator->messages()->first()
					]);
		}

		$get_store = Store::find($request->store_id);
			if(is_null($get_store)){
			return response()->json([
						'status_code' => '0',
						'status_message' => 'Invalid Store ID'
						]);
			}

			$store_name = $get_store->name;
			$store_cuisine = $get_store->store_cuisine[0]['cuisine_name'];

			$date = \Carbon\Carbon::today();
			$address_details = $this->address_details();

			$delivery_time = $address_details['delivery_time'];
			$latitude = $address_details['latitude'];
			$longitude = $address_details['longitude'];
			$order_type = $address_details['order_type'];

			$store = Store::with(['store_menu' => function ($query) {
			$query->select('id','store_id','name')->with(['menu_category' => function ($query) {
			$query->select('id','menu_id','name');
			}])->storeDetail();
			}])
			->whereHas('store_menu',function($q){ })
			->where('id', $request->store_id)
			->location($latitude, $longitude)
			->UserStatus()
			->select('id','user_id','name','delivery_type','min_time','max_time','status','price_rating')
			->first();

			if(is_null($store))
			{
				return response()->json([
				'status_code' => '3',
				'status_message' => trans('api_messages.store.store_inactive'),
				'messages' =>trans('api_messages.store.it_look_like'). $store_name . trans('api_messages.store.currently_unavailable'),
				'cuisine' => $store_cuisine,
				]);
			}

			$isCategory = $request->category_id ?? '';
			$isMenu = $request->store_menu_id ?? '';
			if($isMenu=='' && $isCategory=='')
				{
					$store_cuisine = $store->store_cuisine->map(function ($item) {
						return $item['cuisine_name'];
					})->toArray();
					$wishlist = 0;
					if(request()->token){
					$wishlist = $store->wishlist($user_details->id, $store->id);
					}	
					
					$store_details = array( 
						'store_id' => $store->id,
						'delivery_type' => $store->delivery_type,
						'name' => $store->name,
						'category' => implode(',', $store_cuisine),
						'banner' => $store->banner['medium'],
						'min_time' => $store->convert_mintime,
						'max_time' => $store->convert_maxtime,
						'store_rating' => $store->review->store_rating,
						'price_rating' => $store->price_rating,
						'average_rating'=> $store->review->average_rating,
						'wished' => $wishlist,
						'store_closed'  => ($store->store_time) ? $store->store_time->closed : '',
						'status' => $store->status,
						'store_menu' => $store->store_menu->map(function($menu)use($per_page,$page){
						$menu_category = $menu->menu_category()->select('id','menu_id','name')->paginate($per_page);
							return
							[
								'id' => $menu['id'],
								'name' => $menu['name'],
								'menu_time' => $menu['menu_time'],
								'menu_closed' => $menu['menu_closed'],
								'total_page' => $menu_category->lastPage(),
								'current_page' => $page,
								'menu_category' => $menu_category->map(function($category)use($per_page,$page){
									$menu_item = $category->menu_item()->select('id','menu_id','menu_category_id','name','description','price','currency_code','is_visible','type','status')->paginate($per_page);
										return
										[
												'id' => $category['id'],
												'name' => $category['name'],
												'total_page' => $menu_item->lastPage(),
												'current_page' => $page,
												'menu_item' => $menu_item->map(function($item){
												return
												[
													'id' => $item['id'],
													'menu_category_id' => $item['menu_category_id'],
													'name' => $item['name'],
													'description' => $item['description'],
													'price' => $item['price'],
													'is_visible' => $item['is_visible'],
													'type' => $item['type'],
													'status' => $item['status'],
													'offer_price' => $item['offer_price'],
													'is_offer' => $item['is_offer'],
													'offer_percentage' => $item['offer_percentage'],
													'menu_item_image' => $item['menu_item_image']
												];
											}),
										];
									})->toArray(),
							];
						})->toArray(),
					);
				/* Remaining Menu Details */
				$all_details = array();
				if($store->store_menu[0]->id)
				{
					$all_details = Menu::where('store_id', $request->store_id)->whereNotIn('id',[$store->store_menu[0]->id])->has('menu_category.menu_item')->get();

					$all_details = $all_details->map(function($menu){
						return[
							'id' => $menu['id'],
							'name' => $menu['name'],
							'menu_time' => $menu['menu_time'],
							'menu_closed' => $menu['menu_closed'],
						];
						})->toArray();
					}

					if(count($all_details) > 0){
						$store_details['store_menu'] = array_merge($store_details['store_menu'],$all_details);
					}
					$service_type 	= Store::where('id',$request->store_id)->first();
					$service_id 	= ServiceType::where('id',$service_type->service_type)->first();
					$store_details['menu_category'] = [];
						return response()->json([
							'status_code' => '1',
							'status_message' => trans('api_messages.success'),
							'store_details' => $store_details,
							'service_status'=> $service_id->status,
							]);
						}

						if($isMenu!='' && $isCategory=='')
						{
							$get_category = MenuCategory::where('menu_id',$request->store_menu_id)->paginate($per_page);
									$menu_category = $get_category->map(function($category) use($per_page) 
									{
										$menu_item_count = $category->menu_item()->select('id','menu_category_id','name','price','currency_code','is_visible','type','status')->count();
										$menu_item = $category->menu_item()->select('id','menu_category_id','name','description','price','currency_code','is_visible','type','status')->limit($per_page)->get();

											return
											[
												'id' => $category['id'],
												'name' => $category['name'],
												'total_page' => (int) ceil($menu_item_count / $per_page),
												'current_page' => '1',
												'menu_item' => $menu_item->map(function($item){
												return
												[
													'id' => $item['id'],
													'menu_category_id' => $item['menu_category_id'],
													'name' => $item['name'],
													'description' => $item['description'],
													'price' => $item['price'],
													'is_visible' => $item['is_visible'],
													'type' => $item['type'],
													'status' => $item['status'],
													'offer_price' => $item['offer_price'],
													'menu_item_image' => $item['menu_item_image']
												];
											}),
										];
									});

							return response()->json([
							'status_code' => '1',
							'status_message' => trans('api_messages.success'),
							'total_page' => $get_category->lastPage(),
							'current_page' => $page,
							'menu_category' => $menu_category,
							]);
						}

					if($isMenu!='' && $isCategory!='') {
						$get_item = MenuItem::where('menu_category_id',$request->category_id)->paginate($per_page);
						$menu_item = $get_item->map(function($item){
						return
							[
								'id' => $item['id'],
								'menu_category_id' => $item['menu_category_id'],
								'name' => $item['name'],
								'description' => $item['description'],
								'price' => $item['price'],
								'is_visible' => $item['is_visible'],
								'type' => $item['type'],
								'status' => $item['status'],
								'offer_price' => $item['offer_price'],
								'menu_item_image' => $item['menu_item_image']
							];
						});
						return response()->json([
						'status_code' => '1',
						'status_message' => trans('api_messages.success'),
						'total_page' => $get_item->lastPage(),
						'current_page' => $page,
						'menu_item' => $menu_item,
						]);
					}

					$check_address = check_store_location('', $latitude, $longitude, $request->store_id);

					if ($get_store->status == 0 || $check_address == 0) {
						return response()->json([
						'status_code' => '2',
						'status_message' => trans('api_messages.store.unavailable'),
						'messages' => trans('api_messages.store.it_look_like') . $store_name . trans('api_messages.store.close_enough'),
						'cuisine' => $store_cuisine,
						]);
					}

					if($get_store->service_type  != $request->service_type){
						return response()->json([
						'status_code' => '4',
						'status_message' => trans('api_messages.store.service_type_mismatch'),
						'messages' => trans('api_messages.store.it_look_like') . $store_name . trans('api_messages.store.service_type_mismatch'),
						'cuisine' => $store_cuisine,
						]);
					}

				}	
}
