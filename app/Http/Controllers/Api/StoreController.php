<?php

/**
 * StoreController Controller
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
use App\Models\User;
use App\Models\Driver;
use App\Models\Currency;
use App\Models\DriverRequest;
use App\Models\IssueType;
use App\Models\MenuItem;
use App\Models\MenuItemModifierItem;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\Payout;
use App\Models\Store;
use App\Models\MenuCategory;
use App\Models\Review;
use App\Models\ReviewIssue;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use App;
use App\Models\StoreOweAmount;
use App\Models\Payment;
use App\Models\PayoutPreference;
use App\Models\UserPaymentMethod;

class StoreController extends Controller {
	/**
	 * Construct function
	 */
	public function __construct() {
		parent::__construct();
	}


	public function Ordermap($store_id,$status,$schedule_status='',$page=1)
	{
		$order = Order::where('store_id', $store_id)->history($status);
		if($schedule_status === 0 || $schedule_status === 1)
			$order->where('schedule_status', $schedule_status);

		$order = $order->orderBy('id', 'Desc');
		if(!in_array('pending',$status)){
			$order = $order->paginate(PAGINATION);
			$total_page = $order->lastPage();
		}
		else
			$order = $order->get();

		$order = $order->map(function($order, $key)use($status) {
			if(last(request()->segments()) =='order_history' && ( in_array('completed',$status) || in_array('cancelled',$status) )) {
				if($order->status_text == "completed") {
					$order_time = $order->completed_at->format('Y-m-d h:i').trans('user_api_language.monthandtime.'.$order->completed_at->format('a'));
				} else {
					$order_time = $order->updated_at->format('Y-m-d h:i').trans('user_api_language.monthandtime.'.$order->updated_at->format('a'));
				}
				$menu = $order->order_item->first();
				return [
					'id' => $order->id,
					'order_item_count' => $order->order_item->count(),
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'status_text' => $order->status_text,
					'order_item_name' =>  $menu ? ($menu->menu_item_name ? $menu->menu_item_name->name : ''):'',
					'order_time' => $order_time,
					'order_price' => $order->subtotal,
					'currency_code' => $order->currency_code,
					'currency_symbol' => Currency::original_symbol($order->currency_code),
					'delivery_type' => $order->delivery_type,	
				];
			}
			else if(in_array('accepted',$status) || in_array('pending',$status) ){
				$date = $this->dateFormat($order->created_at, $order->schedule_time, $order->schedule_status);
				$date1 = date($order->schedule_time);
				$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));

				$pre_time = explode(':',$order->est_preparation_time);
				$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
				// form date and time
				$time = \Carbon\Carbon::parse($schedule_time_obj)->format('h:i a');

				$changeDate = $date['date'];

				return [
					'id' => $order->id,
					'order_item_count' => $order->order_item->count(),
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'driver_image' => isset($order->driver) ? $order->driver->user->user_image_url : '',
					'remaining_seconds' => $order->remaining_seconds,
					'total_seconds' => $order->total_seconds,
					'status_text' => $order->status_text,
					'order_delivery_status' => $order->order_delivery ? $order->order_delivery->status : '-1',
					'order_type' => $order->schedule_status,
					'order_date' => $changeDate,
					'order_time' => $time,
					'delivery_type' => $order->delivery_type,	
				];
			}
			else{

				return [
					'id' => $order->id,
					'status_text' => $order->status_text,
					'store_to_driver_thumbs' => $order->store_is_thumbs,
				];
			}
			
		});
		if( last(request()->segments()) =='order_history'){
			return [
				'total_page' => $total_page,
				'current_page' => (int) $page,
				request()->type => $order->toArray(),
			];
		}
		if(!isset($total_page))
			return $order;
		else
			return array('data' => $order)+array('total_page' => $total_page);
	}

	/**
	 * To get the orders for the store dashboard
	 *
	 * @return array Response for the store dashboard
	 */
	public function orders()
	{
		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
		$default_currency_symbol = html_entity_decode($default_currency_symbol);
		$store = Store::authUser()->first();

		if(isset($store->user->currency_code)) {
			$default_currency_code = $store->user->currency_code->code;
			$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
		}
		
		
		$pending_orders   = $this->Ordermap($store->id,['pending']);
		$accepted_orders  = $this->Ordermap($store->id,['accepted'],0);
		$page['current_orders'] = $accepted_orders['total_page'];
		$scheduled_orders = $this->Ordermap($store->id,['accepted'],1);
		$page['schedule_order'] = $scheduled_orders['total_page'];
		$completed_orders = $this->Ordermap($store->id,['completed']);
		$page['completed_orders'] = $completed_orders['total_page'];
		$delivery_orders  = $this->Ordermap($store->id,['delivery']);
		$page['delivery_orders'] = $delivery_orders['total_page'];
		$takeaway_orders  = $this->Ordermap($store->id,['takeaway']);
		$page['take_away'] = $takeaway_orders['total_page'];




		$owe_amount = StoreOweAmount::where('user_id', $store->user_id)->first();
		if(!is_null($owe_amount)) {
			$owe_amount->amount = $owe_amount->amount;
			$owe_amount->currency_code = $owe_amount->currency_code;
			$owe_amount->save();
		}
				
		$store_owe_amount = isset($owe_amount->amount) ? $owe_amount->amount : 0;
		
		$issue_store_delivery = IssueType::TypeText('store_delivery')->get();
		
		return response()->json([
			'status_message' => trans('user_api_language.sucess'),
			'status_code' => '1',
			'store_name' => $store->name,
			'status' => $store->status,
			'pending_orders' => $pending_orders,
			'current_orders' => $accepted_orders['data'],
			'delivery_orders' => $delivery_orders['data'],
			'take_away'	 => $takeaway_orders['data'],
			'completed_orders' => $completed_orders['data'],
			'schedule_order' => $scheduled_orders['data'],
			'store_delivery' => $issue_store_delivery ? $issue_store_delivery : [],
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
			'store_owe_amount' => $store_owe_amount,	
			'total_pages' => $page,	
		]);
	}

	/**
	 * API for getting order history
	 *
	 * @return Response Json response with status
	 */
	public function order_history(Request $request) 
	{
		try 
		{
			$default_currency_code = DEFAULT_CURRENCY;
			$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
			$default_currency_symbol = html_entity_decode($default_currency_symbol);

			$request = request();
			$store = Store::authUser()->first();

			if(isset($store->user->currency_code)) {
				$default_currency_code = $store->user->currency_code->code;
				$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
			}




			$status = ['delivery_orders'=>['delivery'],'take_away'=>['takeaway'],'completed_orders'=>['completed'],'schedule_order'=>['accepted'],'current_orders'=>['accepted'],'history'=>['completed','cancelled']  ];
			$get_status = $status[$request->type];
			
			$schedule_status = '';
			if($request->type == 'schedule_order')
				$schedule_status = 1;
			else if($request->type == 'current_orders')
				$schedule_status = 0;

			$orders = $this->Ordermap($store->id,$get_status,$schedule_status,$request->page);
			return response()->json([
				'status_code' => '1',
				'status_message' => trans('store_api_language.order_history_listed'),
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]+$orders );
			
		} catch (\Exception $e) {
			return response()->json([
				'status_code' => '1',
				'status_message' => $e->getMessage(),
			]);
		}
	}

	/**
	 * API for accepting order
	 *
	 * @return Response Json response with status
	 */
	public function accept_order(Request $request)
	{
		$store = Store::authUser()->first();

		$order = new Order;
		$status = ['cart','accepted'];
		$order = Order::where('id', $request->order_id)->where('status',1)->first();
		if(is_null($order))
		{
			return response()->json([
				'status_code' => "0",
				'status_message' => trans('user_api_language.already_accepted'),
			]);
		}

		$order->accept_order();
		$accepted_orders = Order::where('store_id', $store->id)->history(['accepted'])->get();
		$accepted_orders = $accepted_orders->map(function($order, $key) {
			return [
				'id' => $order->id,
				'order_item_count' => $order->order_item->count(),
				'user_name' => $order->user->name,
				'user_image' => $order->user->user_image_url,
				'remaining_seconds' => $order->remaining_seconds,
				'total_seconds' => $order->total_seconds,
				'status_text' => $order->status_text,
				'order_type' => $order->schedule_status,
			];
		});
		return response()->json([
			'status_code' => "1",
			'status_message' => trans('user_api_language.orders.order_accepted'),
			'accepted_orders' => $accepted_orders,
		]);
	}

	/**
	 * API for getting order details
	 *
	 * @return Response Json response with status
	 */
	public function order_details(Request $request) {

		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
		$default_currency_symbol = html_entity_decode($default_currency_symbol);
		$store = Store::authUser()->first();

		if(isset($store->user->currency_code)) {
			$default_currency_code = $store->user->currency_code->code;
			$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
		}

		$rules = ['order_id' => 'required|exists:order,id,store_id,' . $store->id];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();

		if($order->delivery_type == "takeaway")
		{	
			$is_request = 0;
		}
		else
		{
			$request = DriverRequest::where('order_id', $order->id)->get();
			$is_request = $request->count() > 0 ? 1 : 0;
		}
		


		$date = $this->dateFormat($order->updated_at, $order->schedule_time, $order->schedule_status);

		$date1 = date($order->schedule_time);
		$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));
		// dd($schedule_time_obj);	
		$pre_time = explode(':',$order->est_preparation_time);
		$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
		
		$time = \Carbon\Carbon::parse($schedule_time_obj)->format('h:i a');

		$changeDate = $date['date'];
		
		//form date and time
		$getTime = $time;
		
		$dateTrans = $date['date'];
		$delivery_at = strtotime($order->delivery_at);  
		$order_details = [
			'order_id' 		=> $order->id,
			'status' 		=>$order->status_text ,
			'order_notes' 	=> $order->notes,
			'order_delivery_status' => $order->order_delivery ? $order->order_delivery->status : '-1',
			'store_to_driver_thumbs' => $order->store_is_thumbs,
			'order_type' 	=> $order->schedule_status,
			'order_date' 	=> $dateTrans,
			'order_time' 	=> $getTime,
			'status_times' 	=> [
				'accepted' 	=> $order->accepted_at ? $order->accepted_at->format('h:i').trans('user_api_language.monthandtime.'.$order->accepted_at->format('a')) : '',
				'delivery' 	=> $order->delivery_at ? date('h:i', $delivery_at)  .trans('user_api_language.monthandtime.'.date('a', $delivery_at)) : '',
				'completed' => $order->completed_at ? $order->completed_at->format('h:i') .trans('user_api_language.monthandtime.'.$order->completed_at->format('a')) : '',
			],
			'item_details' => $order->order_item->map(function ($order_item) {
				$result = array();

				$order_item_modifier_item = $order_item->order_item_modifier->map(function ($menu) { 
					return $menu->order_item_modifier_item->map(function ($item) { 
						return [
							'id'	=> $item->id,			
							'count' => (int)$item->count,
							'price' => (string) number_format($item->price * $item->count,'2' ),
							'name'  => $item->menu_item_modifier_item->name,
							'tax' => $item->tax,

						];
					});
				})->toArray();

				foreach($order_item_modifier_item as $key => $value) {
					if(is_array($value)) {
						foreach($value as $keys =>$val) {
							$result[] = $val;
						}
					}
				}

				return [
					'name' => $order_item->menu_item->name,
					'notes' => (string) $order_item->notes,
					'price' => number_format($order_item->total_amount +  $order_item->tax - $order_item->tips,2),
					'offer_price' => $order_item->offer_price,
					'quantity' => $order_item->quantity,
					'modifiers' => @$result ? $result : [],
					'tax' =>  $order_item->t,	
				];
			})->toArray(),
			'subtotal' 	=> $order->subtotal,
			'tax' 		=> $order->tax,
			'store_fee' => $order->store_commision_fee,
			'total' => (string) numberFormat($order->store_total - $order->store_commision_fee),
			'user_image' => $order->user->user_image_url,
			'user_name' => $order->user->name,
			'user_phone' => $order->user->mobile_number_phone_code,
			'support_phone' => site_setting('site_support_phone'),
			'driver_name' => $order->driver ? $order->driver->user->name : "",
			'vechile_name' => $order->driver ? $order->driver->vehicle_name : "",
			'vechile_number' => $order->driver ? $order->driver->vehicle_number : "",
			'driver_number' => $order->driver ? $order->driver->user->mobile_number_phone_code : "",
			'driver_image' => $order->driver ? $order->driver->user->user_image_url : getEmptyUserImageUrl(),
			'pickup_location' => $order->order_delivery ? $order->order_delivery->pickup_location : "",
			'pickup_latitude' => $order->order_delivery ? $order->order_delivery->pickup_latitude : "",
			'pickup_longitude' => $order->order_delivery ? $order->order_delivery->pickup_longitude : "",
			'drop_location' => $order->order_delivery ? $order->order_delivery->drop_location : "",
			'drop_latitude' => $order->order_delivery ? $order->order_delivery->drop_latitude : "",
			'drop_longitude' => $order->order_delivery ? $order->order_delivery->drop_longitude : "",
			'driver_latitude' => $order->driver ? $order->driver->latitude : "",
			'driver_longitude' => $order->driver ? $order->driver->longitude : "",
			'is_request' => $is_request,
			'res_penality' => $order->store_penality,
			'applied_penality' => $order->res_applied_penality,
			'delivery_type'  => $order->delivery_type,
		];

		if($order->status == $order->statusArray['cancelled']) {
			$order_details['completed_date_time'] = $order->cancelled_at ? $order->cancelled_at->format('d-m-Y h:i').trans('user_api_language.monthandtime.'.$order->cancelled_at->format('a')) : '';
		} else if ($order->status == $order->statusArray['declined']) {
			$order_details['completed_date_time'] = $order->declined_at ? $order->declined_at->format('d-m-Y h:i').trans('user_api_language.monthandtime.'.$order->declined_at->format('a')) : '';
		} else {
			$order_details['completed_date_time'] = $order->completed_at ? $order->completed_at->format('d-m-Y h:i').trans('user_api_language.monthandtime.'.$order->completed_at->format('a')) : '';
		}

		$order_details['currency_code'] = $order->currency_code;
		$order_details['currency_symbol'] = html_entity_decode(Currency::original_symbol($order->currency_code));

		return response()->json([
			'status_message' => trans('driver_api_language.driver.order_details_listed') ,
			'status_code' => "1",
			'order_details' => $order_details,
			'default_currency_code'=> $default_currency_code,
			'default_currency_symbol'=> $default_currency_symbol,
		]);
	}

	/**
	 * API for delivery status
	 *
	 * @return Response Json response with status
	 */
	public function food_ready(Request $request) {

		$store = Store::authUser()->first();
		$order = new Order;
		
		$rules = [
			'order_id' => 'required|exists:order,id,store_id,' . $store->id . ',status,' . $order->statusArray['accepted'],
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();
				
		if($order->delivery_type == 'takeaway')
		{		
				$order->status = $order->statusArray['takeaway'];
				$order->delivery_at =  date('Y-m-d H:i:s');
				// $order->accepted_at = date('Y-m-d H:i:s');	
				$order->store_owe_amount =  $order->booking_fee + $order->store_commision_fee;
				$order->save();
				$order_id = $order->id;	
				$user = $order->user;
				$push_notification_title = trans('user_api_language.orders.your_food_preparation_done') . $order_id;
				$push_notification_data = [
					'type' => 'order_ready',
					'order_id' => $order_id,
				];
				push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
				$active = false;
				return response()->json([
				'status_code' => '1',
				'status_message' => trans('store_api_language.gofereats.order_is_ready'),
			]);	
		}

		$order->deliver_order();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('store_api_language.order.order_delivery'),
		]);
	}

	/**
	 * API for getting cancel reasons
	 *
	 * @return Response Json response with status
	 */
	public function get_cancel_reason(Request $request)
	{
		$store = Store::authUser()->first();
		$order = new Order;

		$rules = [
			'type' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}
		//$this->view_data['cancel_reason'] = OrderCancelReason::where('status', 1)->get();
		$cancel_reason = OrderCancelReason::type($request->type)->status()->get()
		->map(
		
			function ($reason) {
				return [
					'id' => $reason->id,
					'reason' => $reason->add_name,
				];
			}
		);

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.driver.order_cancel_reasons'),
			'cancel_reason' => $cancel_reason,
		]);
	}

	/**
	 * API for cancel order
	 *
	 * @return Response Json response with status
	 */
	public function cancel_order(Request $request)
	{
		$store = Store::authUser()->first();

		$rules = [
			'order_id' => 'required|exists:order,id,store_id,' . $store->id,
			'cancel_reason' => 'required|exists:order_cancel_reason,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();
		$order->cancel_order("store", $request->cancel_reason, $request->cancel_message);

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.driver.order_has_been_cancelled'),
		]);
	}

	/**
	 * API for delay order
	 *
	 * @return Response Json response with status
	 */
	public function delay_order(Request $request)
	{
		$store = Store::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,store_id,' . $store->id/* . ',status,' . $order->statusArray['accepted']*/,
			'delay_min' => 'required|integer',
			// 'delay_message' => 'required'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$delay_seconds = $request->delay_min * 60;

		$order = Order::where('id', $request->order_id)->first();
		$order->delay_order($delay_seconds, $request->delay_message);

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('store_api_language.order.order_has_been_delayed'),			
		]);
	}

	/**
	 * To get the menu details in array
	 *
	 * @return Response Json response
	 */
	public function menu(Request $request) 
	{
		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
		$default_currency_symbol = html_entity_decode($default_currency_symbol);

		$store = Store::authUser()->menuRelations()->first();

		if(isset($store->user->currency_code)) {
			$default_currency_code = $store->user->currency_code->code;
			$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
		}

		$store_menu = $store->store_menu->map(function($menu) {
			return [
				'id' => $menu->id,
				'name' => $menu->name,
				'menu_category' => $menu->menu_category->map(function($category) {
					return [
						'id' => $category->id,
						'name' => $category->name
					];
				})->toArray(),
			];
		})->toArray();

		return response()->json([
			'status_message' => trans('store_api_language.store_menu_details_listed'),
			'status_code' => '1',
			'store_menu' => $store_menu,
		]);
	}

	/**
	 * To get the menu item details in array
	 *
	 * @return Response Json response
	 */
	public function menu_item(Request $request) 
	{	
		try {

			$default_currency_code = DEFAULT_CURRENCY;
			$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
			$default_currency_symbol = html_entity_decode($default_currency_symbol);

			if($request->token){
				$user_details = JWTAuth::parseToken()->authenticate();
				$user = User::where('id', $user_details->id)->first();
				$default_currency_code = $user->currency_code->code;
				$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
			}
			
			$category = MenuCategory::where('id',$request->id)->first();
			if($category)
			{
				$menu_item = MenuItem::where('menu_category_id',$category->id)->paginate(PAGINATION);
				$total_count = $menu_item->lastPage();
				$menu_item = $menu_item->map(function($item) {
					return [
						'id' => $item->id,
						'name' => $item->name,
						'price' => $item->price,
						'image' => $item->menu_item_image,
						'description' => $item->description,
						'is_visible' => $item->is_visible,
						'modifier' => $item->menu_item_modifier->map(function($modifier) {
							return [
								'id' => $modifier->id,
								'name' => $modifier->name,
								'modifier_item' => $modifier->menu_item_modifier_item->map(
								function ($modifier_item) {
									return [
									'id' => $modifier_item->id,
									'name' => $modifier_item->name,
									'price' => $modifier_item->price,
									'is_visible' => $modifier_item->is_visible,
									];
								})->toArray(),
							];
						})->toArray(),
					];
				})->toArray();
			}

			return response()->json([
				'status_message'	 		=> trans('store_api_language.store_menu_details_listed'),
				'status_code' 				=> '1',
				'total_count' 				=> $total_count,
				'current_page' 				=> (int) $request->page,
				'menu_item' 				=> $menu_item,
				'default_currency_code' 	=> $default_currency_code,
				'default_currency_symbol'	=> $default_currency_symbol,
			]);
			
		} catch (\Throwable $th) {

			return response()->json([
				'status_code' 				=> '0',
				'status_message'	 		=> $th->getMessage(),
			]);
		}		
	}

	/**
	 * Toggle the visible option of the menu item, menu modifier item
	 *
	 * @return Response Json response
	 */
	public function toggle_visible(Request $request)
	{
		$store = Store::authUser()->menuRelations()->first();

		$rules = [
			'type' => 'required|in:menu_item,modifier_item',
		];

		if ($request->type == "menu_item") {
			$rules['id'] = "required|exists:menu_item,id";
		}
		elseif ($request->type == "modifier_item") {
			$rules['id'] = "required|exists:menu_item_modifier_item,id";
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_message' => $validator->messages()->first(),
				'status_code' => '0',
			]);
		}

		$data = null;
		if ($request->type == "menu_item") {
			$data = MenuItem::where('id', $request->id)->store($store->id)->first();
		}
		elseif ($request->type == "modifier_item") {
			$data = MenuItemModifierItem::where('id', $request->id)->store($store->id)->first();
		}

		if (!$data) {
			return response()->json([
				'status_message' => trans('store_api_language.you_are_not_authenticated_to_change'),
				'status_code' => '0',
			]);
		}

		$data->is_visible = $data->is_visible == 0 ? 1 : 0;
		$data->save();

		return response()->json([
			'status_message' => trans('store_api_language.visible_value_changed'),
			'status_code' => '1',
		]);
	}

	public function store_availabilty()
	{
		$request = request();
		$store = Store::authUser()->menuRelations()->first();

		$rules = [
			'status' => 'required|in:0,1',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_message' => $validator->messages()->first(),
					'status_code' => '0',
				]
			);
		}

		$store->status = $request->status;
		$store->save();

		return response()->json(
			[
				'status_message' => trans('store_api_language.store_status_updated'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * API for Review Store to driver
	 *
	 * @return Response Json response with status
	 */

	public function review_store_to_driver(Request $request)
	{
		$request = request();
		$store = Store::authUser()->first();
		$issue_type = new IssueType;

		$order = Order::where('id', $request->order_id)->first();
		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);

		$rules = [
			'order_id' => 'required|exists:order,id',
			'is_thumbs' => 'required|in:0,1',
			// 'issues' => 'required_if:is_thumbs,0',
			// 'issues_array.*' => 'required_if:is_thumbs,0|exists:issue_type,id,type_id,'.$issue_type->typeArray['store_delivery'],
		];

		// $messages = [
		//     'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
		// ];

		$validator = Validator::make($request_data, $rules/*, $messages*/);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$review = new Review;
		$review->order_id = $order->id;
		$review->type = $review->typeArray['store_delivery'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->store_id;
		$review->is_thumbs = $request->is_thumbs;
		$review->comments = $request->comments ?: "";
		$review->save();

		if ($request->issues) {
			$issues = explode(',', $request->issues);
			if ($request->is_thumbs == 0 && count($issues)) {
				foreach ($issues as $issue_id) {
					$review_issue = new ReviewIssue;
					$review_issue->review_id = $review->id;
					$review_issue->issue_id = $issue_id;
					$review_issue->save();
				}
			}

		}

		return response()->json(
			[
				'status_message' => trans('store_api_language.order_delivery_to_driver'),
				'status_code' => '1',
				'store_to_driver_thumbs' => $review->is_thumbs,
			]
		);

	}

	/**
	 * API for Dateformat converted
	 *
	 * @return Response Json response with status
	 */

	public function dateFormat($updated_at, $delivery_time, $type)
	{

		$date = date('Y-m-d', strtotime($updated_at));
		;
		if ($type == 0) {
			$common = date('h:i', strtotime($delivery_time)).trans('user_api_language.monthandtime.'.date('a', strtotime($delivery_time)));
			
			if ($date == date('Y-m-d')) {
				$date = 'Today' . ' ' . date('M d h:i', strtotime($updated_at)).trans('user_api_language.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('user_api_language.orders.Today');
				$time = $common;
			}
			else if ($date == date('Y-m-d', strtotime("+1 days"))) {
				$date = 'Tomorrow' . ' ' . date('M d h:i', strtotime($updated_at)).trans('user_api_language.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('user_api_language.orders.Today');
				$time = $common;
			}
			else {
				$date = date('l M d h:i', strtotime($updated_at)).trans('user_api_language.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('user_api_language.weekday.'.date('l', strtotime($updated_at))).' '.trans('user_api_language.monthandtime.'.date('M', strtotime($updated_at))).' '.date('d', strtotime($updated_at));
				$time = date('h:i', strtotime($updated_at)).trans('user_api_language.monthandtime.'.date('a', strtotime($updated_at)));
			}
		}
		else {

			$schedule_time = date('Y-m-d', strtotime($delivery_time));
			$time_Stamp = strtotime($delivery_time) + 1.20;
			$del_time = date('h:i a', $time_Stamp);
			$common = date('h:i', strtotime($delivery_time)).trans('user_api_language.monthandtime.'.date('a', strtotime($delivery_time)));

			if ($schedule_time == date('Y-m-d')) {
				$date = 'Today' . ' ' . $common;
				$day = trans('user_api_language.orders.Today');
				$time = $common;
			}
			else if ($schedule_time == date('Y-m-d', strtotime("+1 days"))) {
				$date = 'Tomorrow' . ' ' . $common;
				$day = trans('user_api_language.orders.Tomorrow');
				$time = $common;
			}
			else {
				$date = date('l M d h:i', strtotime($delivery_time)).trans('user_api_language.monthandtime.'.date('a', strtotime($delivery_time)));
				
				$day = trans('user_api_language.weekday.'.date('l', strtotime($delivery_time))).' '.trans('user_api_language.monthandtime.'.date('M', strtotime($delivery_time))).' '.date('d', strtotime($delivery_time));
				$time = date('h:i', strtotime($delivery_time)).trans('user_api_language.monthandtime.'.date('a', strtotime($delivery_time)));
			}

		}
		return ['date' => $day, 'time' => $time];
	}

	/**
	 * API for Review Store to driver
	 *
	 * @return Response Json response with status
	 */
	public function remainScheduleOrder()
	{
		$order = Order::where('status', '3')->where('schedule_status', '1')->get();
		if ($order) {

			foreach ($order as $value) {

				date_default_timezone_set($value->store->user_address->default_timezone);

				$data['prepartation'] = $value->est_preparation_time;
				$data['travel'] = $value->est_travel_time;

				$secs = strtotime($data['travel']) - strtotime("00:00:00");
				$data['total_time'] = date("H:i:s", strtotime($data['prepartation']) + $secs);

				$secs = strtotime($data['total_time']) - strtotime("00:00:00");
				$data['prepare'] = date("Y-m-d H:i", strtotime($value->schedule_time) - $secs);

				$changeDate;
				$date = date("H:i",strtotime($value->schedule_time));

				$date1 = date($value->schedule_time);
				$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));
				
				$pre_time = explode(':',$value->est_preparation_time);
				$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
				
				$time = \Carbon\Carbon::parse($schedule_time_obj)->format('Y-m-d H:i');
				
				//form date and time
				if ($time <= date('Y-m-d H:i',time() + 300)) {
					$order = Order::find($value->id);
					$order->schedule_status = 0;
					$order->accepted_at = date('Y-m-d H:i:s');
					$secs = strtotime($data['total_time']) - strtotime("00:00:00");
					$est_time = date("H:i:s", time() + $secs);
					$order->est_delivery_time = $est_time;
					$order->save();

					$push_notification_title = trans('user_api_language.schedule_order_timing_Start');
					$store_user = $order->store->user;
					
					$push_notification_data = [
						'type' => 'schedule_order',
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

					push_notification($store_user->device_type, $push_notification_title, $push_notification_data, 1, $store_user->device_id, true);

				}

			}

		}

	}

	/**
	 * API for Request before seven minutes to Driver
	 *
	 * @return Response Json response with status
	 */

	public function beforeSevenMin()
	{
		$order = Order::where('status', '3')->where('schedule_status', '0')->where('delivery_type','delivery')->get();
		logger('seven minutes count '.$order->count());
		if ($order) {
			$time = date('H:i', time());
			logger("seven mins cron for delivery");
			foreach ($order as $value) {
				date_default_timezone_set($value->store->user_address->default_timezone);
				$data['travel'] = $value->est_travel_time;
				$secs = strtotime($data['travel']) - strtotime("00:00:00");
				$est_delivery_time = strtotime($value->est_delivery_time)-(420+$secs);
				$request_time = date('H:i',$est_delivery_time);
				if (date('Y-m-d', strtotime($value->schedule_time)) == date('Y-m-d', time()) || $value->schedule_time==null) {
					if (strtotime($request_time) == strtotime($time)) {
						logger("sevenr mins order".$value->id);
						$order = Order::where('id', $value->id)->first();
						$order->deliver_order();
					}
				}
			}
		}

	}

	public function completeTakeawayOrder(Request $request){

		$store = Store::authUser()->first();

		$rules = [
			'order_id' => 'required|exists:order,id,store_id,' . $store->id,
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();
		$order->completeOrder();
		$completed_date_time = $order->completed_at ? $order->completed_at->format('d-m-Y h:i').trans('user_api_language.monthandtime.'.$order->completed_at->format('a')) : '';

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_language.orders.order_completed'),
			'completed_date_time' => $completed_date_time,
		]);
	}

	public function payStoreToAdmin(Request $request) {

		$store = Store::authUser()->first();
		$owe_amount = StoreOweAmount::where('user_id', $store->user_id)->first();
		if($owe_amount == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('driver_api_language.driver.not_generate_amount'),
			]);
		}
		
		$currency_code = $owe_amount->getRawOriginal('currency_code');

		$user_currency = $store->user->currency_code->code;
		
		$amount = currencyConvert($user_currency,$currency_code,floatval($request->amount));

		$total_owe_amount = $owe_amount->amount;
		$remaining_amount = $total_owe_amount - $amount;	

		$stripe_payment = resolve('App\Repositories\StripePayment');		

		if($request->payment_method == 1){
			if($request->filled('payment_intent_id')) {
				$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
			}
			else {
				$user_payment_method = UserPaymentMethod::where('user_id', $store->user_id)->first();
				$paymentData = array(
					"amount" 		=> $amount * 100,
					'currency' 		=> $currency_code,
					'description' 	=> 'Payment for Owe Amount by : '.$store->user->first_name,
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
			} else if($payment_result->status != 'success') {
				return response()->json([
					'status_code' 	=> '0',
					'status_message'=> $payment_result->status_message,
				]);
			}

		}
		if($request->payment_method == 2){
			$converted_amount = currencyConvert($currency_code,PAYPAL_CURRENCY_CODE,floatval($request->amount));
			$payment_result = $stripe_payment->PaypalPayment($converted_amount,$request->pay_key);
			if(!$payment_result->status) {
				return response()->json([
			        'status_code' => '0',
			         'status_message' => $payment_result->status_message,
			    ]);
			}
		}

		$owe_amount->amount = $remaining_amount;
		$owe_amount->save();

		$payment = new Payment;
		$payment->user_id = $store->user_id;
		$payment->order_id = $request->order_id;
		$payment->transaction_id = $payment_result->transaction_id ?? "";
		$payment->type = 0;
		$payment->amount = $amount;
		$payment->status = 1;
		$payment->currency_code = $currency_code;
		$payment->save();

		$owe_amount = StoreOweAmount::where('user_id', get_current_login_user_id())->first();
		
		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('driver_api_language.driver.payout_successfully'),
			'owe_amount' 		=> $owe_amount->amount,
			'currency_code' 	=> $owe_amount->currency_code,
		]);

	}

}
