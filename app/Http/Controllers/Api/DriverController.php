<?php

/**
 * DriverController Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   DriverController
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\DriverOweAmount;
use App\Models\DriverRequest;
use App\Models\IssueType;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\PayoutPreference;
use App\Models\Review;
use App\Models\ReviewIssue;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\VehicleType;
use App\Traits\FileProcessing;
use App\Models\Store;
use DateTime;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;
use stripe;
use Validator;
use App;

class DriverController extends Controller
{
	use FileProcessing;

	/**
	 * To update the vehicle details
	 *
	 * @return Response Json response
	 */
	public function vehicle_details()
	{
		$request = request();
		$user = User::auth()->first();
		$driver = Driver::authUser()->first();
		$vehicle_type = new VehicleType;

		$rules = [
			'vehicle_type' => 'required|exists:vehicle_type,id,status,' . $vehicle_type->statusArray['active'],
			'vehicle_name' => 'required',
			'vehicle_number' => 'required',
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
		$user->status = 3;
		$user->save();
		if (!$driver) {
			$driver = new Driver;
			$driver->user_id = $user->id;
		}

		$driver->vehicle_type = $request->vehicle_type;
		$driver->vehicle_name = $request->vehicle_name;
		$driver->vehicle_number = $request->vehicle_number;
		$driver->save();

		return response()->json(
			[
				'status_message' => trans('driver_api_language.gofereats.vehicle_details_updated'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * To upload the documents
	 *
	 * @return Response Json response
	 */
	public function document_upload() {
		$request = request();
		$driver = Driver::authUser()->first();

		$rules = [
			'type' => 'required|in:licence_front,licence_back,registeration_certificate,insurance,motor_certiticate',
			'document' => 'required',
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

		$file = $request->file('document');
		$file_path = $this->fileUpload($file, 'public/images/driver');
		$this->fileSave('driver_' . $request->type, $driver->user_id, $file_path['file_name'], '1');
		$original_path = url(Storage::url($file_path['path']));

		if ($driver->documents->count() == 5) {
			if(env("APP_ENV")=='live'){
				$driver->user->status  = 1;
			}else{
				$driver->user->status = $driver->user->statusArray['waiting for approval'];
			}
			$driver->user->save();
		}

		return response()->json(
			[
				'status_message' => trans('driver_api_language.gofereats.document_updated'),
				'status_code' => '1',
				'document_url' => $original_path,
			]
		);
	}

	/**
	 * To check the driver status
	 *
	 * @return Response Json response
	 */
	public function check_status() {

		$driver = Driver::authUser()->first();

		if ($driver->user->status_text == 'waiting for approval') {
			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.waiting_for_admin'),
					'status_code' => '0',
					'driver_status' => $driver->user->status_text,
				]
			);

		} else if ($driver->user->status_text == 'inactive') {

			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.inactive_contact_admin'),
					'status_code' => '0',
					'driver_status' => $driver->user->status_text,
				]
			);

		} else {
			
			return response()->json(
				[
					'status_message' => trans('driver_api_language.'.$driver->user->status_text),
					'status_code' => '1',
					'driver_status' => $driver->user->status_text,
				]
			);
		}
	}

	/**
	 * To update the driver locaiton
	 *
	 * @return Response Json response
	 */
	public function update_driver_location()
	{
		$default_currency_code = DEFAULT_CURRENCY;
		$default_currency_symbol = Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;	
		$default_currency_symbol = html_entity_decode($default_currency_symbol);

		$request = request();
		
		$driver = Driver::authUser()->first();

		if(isset($driver->user->currency_code)) {
			$default_currency_code = $driver->user->currency_code->code;
			$default_currency_symbol = html_entity_decode(Currency::original_symbol($default_currency_code));
		}

		$rules = [
			'latitude' => 'required',
			'longitude' => 'required',
			'status' => 'required|in:' . implode(',', array_values($driver->statusArray)),
		];

		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required').'', 
            
       	);

		$attributes = array(
			'latitude' => trans('driver_api_language.driver.latitude'),
			'longitude' => trans('driver_api_language.driver.longitude'),
			'status' => trans('driver_api_language.driver.status'),
		
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]);
		}

		$driver->latitude = $request->latitude;
		$driver->longitude = $request->longitude;
		if($driver->timezone == '') {
			$timezone = DB::Table('timezone')->where('value',$request->timezone)->first();
			$driver->country_code = $timezone->name ?? "IN";
		}

		$driver->status = $request->status;

		if ($request->status == 2 &&  $request->order_id ) {
			$distance = OrderDelivery::where('order_id', $request->order_id)->first();
			$pre_distance = 0;
			if (isset($distance->drop_distance)) {
				$pre_distance = $distance->drop_distance;
			} 
			$total_km = $request->total_km ? $request->total_km : 0 ;
			$drop_distance = (float) $pre_distance + (float) $total_km;
			$distance->drop_distance = $drop_distance ? $drop_distance : '0' ;
			$distance->save();
		}
		else {
			$timezone = DB::Table('timezone')->where('value',$request->timezone)->first();
			$driver->country_code = $timezone->name ?? "IN";
		}

		$driver->save();
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.driver.driver_location_updated'),
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
			'driver_status' => $driver->user->status_text,
		]);
	}

	/**
	 * To get the driver profile details
	 *
	 * @return Response Json response
	 */
	public function get_driver_profile() {

		$driver = Driver::authUser()->first();

		$user = collect($driver->user)->except(['user_image', 'users_image']);

		$user_address = collect($driver->user_address)->except(
			['id', 'latitude', 'longitude', 'default', 'delivery_options', 'apartment', 'delivery_note', 'type', 'static_map', 'country_code']
		);

		if (!$user_address->count()) {
			$user_address = collect([
				"user_id" => $driver->user_id,
				"street" => "",
				"area" => "",
				"city" => "",
				"state" => "",
				"postal_code" => "",
				"address" => "",
				"country" => "",
			]);
		}

		$driver_documents = $driver->documents->flatMap(function($document) {
			return [
				$document->fileTypeArray->search($document->type) => $document->image_name,
			];
		});

		$owe_amount = DriverOweAmount::where('user_id', $driver->user_id)->first();
		if($owe_amount) {
			$owe_amount->amount = $owe_amount->amount;
			$owe_amount->currency_code = $owe_amount->currency_code;
			$owe_amount->save();
		}
		
		$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name', 'vehicle_image', 'owe_amount']);
		$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);

		return response()->json([
			'status_message' =>trans('driver_api_language.driver.driver_profile_details'),
			'status_code' => '1',
			'driver_profile' => $driver_profile,
		]);
	}

	/**
	 * To update the driver profile
	 *
	 * @return Response Json response
	 */
	public function update_driver_profile() {


		$request = request();
		$driver = Driver::authUser()->first();
		$user = $driver->user;

		$rules = [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'country_code' => 'required|exists:country,code',
			'mobile_number' => 'required',
			// 'street' => 'required',
			// 'area' => 'required',
			// 'city' => 'required',
			// 'state' => 'required',
			// 'postal_code' => 'required',
			'dob' => 'required',
		];

		$messages = array('required' => ':attribute '.trans('api_messages.register.field_is_required'));

		$niceNames = array(
			'first_name' => trans('api_messages.driver.first_name'),
			'last_name' => trans('api_messages.driver.last_name'),
			'email' => trans('api_messages.driver.email'),
			'country_code' => trans('api_messages.driver.country_code'),
			'mobile_number' => trans('api_messages.driver.mobile_number'),
			'street' => trans('api_messages.driver.street'),
			'city' => trans('api_messages.driver.city'),
			'state' => trans('api_messages.driver.state'),
			'postal_code' => trans('api_messages.driver.postal_code'),
			'dob' => trans('api_messages.register.dob'),
		);

		$validator = Validator::make($request->all(), $rules,$messages);
		$validator->setAttributeNames($niceNames);
		if($validator->fails()) {
			return response()->json([
				'status_message' => $validator->messages()->first(),
				'status_code' => '0',
			]);
		}
		$user->name= html_entity_decode($request->first_name) .'~'. html_entity_decode($request->last_name);
		$user->user_first_name = $request->first_name;
		$user->user_last_name = $request->last_name;
		$user->email = $request->email;
		$user->country_code =  Country::whereCode($request->country_code)->value('phone_code');
		$user->country_id 	= Country::whereCode($request->country_code)->value('id');
		$user->mobile_number = $request->mobile_number;
		$user->date_of_birth =date('Y-m-d',strtotime($request->dob));
		$user->save();

		$user_address = $user->user_address;
		if (!$user_address) {
			$user_address = new UserAddress;
			$user_address->user_id = $user->id;
		}

		$user_address->street = $request->street ?? '';
		$user_address->address = $request->first_address ?? '';
		$user_address->city = $request->city ?? '';
		$user_address->state = $request->state ?? '';
		$user_address->country = $request->country ?? '';
		$user_address->postal_code = $request->postal_code ?? '';
		$user_address->default = 1;
		$user_address->save();

		$user = collect($driver->user)->except(['user_image', 'users_image']);

		$user_address = collect($driver->user_address)->except(
			['id', 'latitude', 'longitude', 'default', 'delivery_options', 'apartment', 'delivery_note', 'type', 'static_map', 'country']
		);

		if(!$user_address->count()) {
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

		$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name', 'vehicle_image']);

		$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);
		
		return response()->json([
			'status_message' => trans('driver_api_language.driver.driver_profile_details_updated'),
			'status_code' => '1',
			'driver_profile' => $driver_profile,
		]);
	}

	/**
	 * To accept the request
	 *
	 * @return Response Json response
	 */
	// public function accept_request() {

	// 	$request = request();
	// 	$driver = Driver::authUser()->first();
	// 	$order = new Order;

	// 	$rules = [
	// 		'request_id' => 'required|exists:request,id,driver_id,' . $driver->id,
	// 		'order_id' => 'required|exists:order,id', /*,status,'.$order->statusArray['accepted']*/
	// 	];

	// 	$validator = Validator::make($request->all(), $rules);
	// 	if ($validator->fails()) {
	// 		return response()->json(
	// 			[
	// 				'status_message' => $validator->messages()->first(),
	// 				'status_code' => '0',
	// 			]
	// 		);
	// 	}

	// 	$order = Order::where('id', $request->order_id)->first();
	// 	$driver_request = DriverRequest::where('id', $request->request_id)->first();

	// 	if (($driver_request->status_text != "pending") || ($order->driver_id && $order->driver)) {
	// 		return response()->json(
	// 			[
	// 				'status_message' => trans('api_messages.driver.timed_out'),
	// 				'status_code' => '0',
	// 			]
	// 		);
	// 	}

	// 	$order_list=explode(',', $driver_request->orders_list);
	// 	$driver_id=$driver_request->driver_id;
	// 	foreach($order_list as $key => $value) {		
	// 		$order_data = Order::where('id', $value)->first();
	// 		$driver_request_update=DriverRequest::where('driver_id',$driver_id)->where('order_id',$value)->where('group_id',$driver_request->group_id)->first();
	// 		$driver_request_update->status = $driver_request->statusArray['accepted'];
	// 		$driver_request_update->save();		
	// 		$order_data->driver_accepted($driver_request_update);
	// 	}


	// 	// $driver_request->status = $driver_request->statusArray['accepted'];
	// 	// $driver_request->save();

	// 	// $order->driver_accepted($driver_request);

	// 	$update_status = Driver::find($driver->id);
	// 	$update_status->status = 2;
	// 	$update_status->save();

	// 	$this->static_map($order->id);

	// 	return response()->json(
	// 		[
	// 			'status_message' => trans('api_messages.driver.request_accepted_successfully'),
	// 			'status_code' => '1',
	// 			'order_details' => [
	// 				'order_id' => $order->id,
	// 				'mobile_number' => $order->user->mobile_number,
	// 				'user_thumb_image' => $order->user->user_image_url,
	// 				'rating_value' => '0',
	// 				'vehicle_type' => $driver->vehicle_type_details->name,
	// 				'pickup_location' => $driver_request->pickup_location,
	// 				'pickup_latitude' => $driver_request->pickup_latitude,
	// 				'pickup_longitude' => $driver_request->pickup_longitude,
	// 				'drop_location' => $driver_request->drop_location,
	// 				'drop_latitude' => $driver_request->drop_latitude,
	// 				'drop_longitude' => $driver_request->drop_longitude,
	// 				'group_id' => $driver_request->group_id,
	// 				'request_id' => $driver_request->id,
	// 			],
	// 		]
	// 	);
	// }

	/**
	 * To cancel the request
	 *
	 * @return Response Json response
	 */
	public function cancel_request() {
		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'request_id' => 'required|exists:request,id,driver_id,' . $driver->id,
			'order_id' => 'required|exists:order,id', /*,status,'.$order->statusArray['accepted']*/
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

		$order = Order::where('id', $request->order_id)->first();
		$driver_request = DriverRequest::where('id', $request->request_id)->first();

		if (($driver_request->status_text != "pending") || ($order->driver_id && $order->driver)) {
			if ($driver_request->status_text == "cancelled") {
				return response()->json(
					[
						'status_message' => trans('driver_api_language.driver.timed_out'),
						'status_code' => '1',
					]
				);
			}
			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.timed_out'),
					'status_code' => '0',
				]
			);
		}

		$driver_request->status = $driver_request->statusArray['cancelled'];
		$driver_request->save();

		// searchRequestDriver(
		// 	$driver_request->order_id,
		// 	$driver_request->group_id,
		// 	$driver_request->pickup_latitude,
		// 	$driver_request->pickup_longitude,
		// 	$driver_request->pickup_location,
		// 	$driver_request->drop_latitude,
		// 	$driver_request->drop_longitude,
		// 	$driver_request->drop_location
		// );

		return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.request_cancelled_successfully'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * To search drivers for delivery
	 *
	 * @return Response Json response
	 */
	public function search_drivers() {
		$request = request();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,status,' . $order->statusArray['accepted'],
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
		searchDrivers($request->order_id);
	}

	/**
	 * To search and send request to the driver
	 *
	 * @param  integer $order            Id of the order
	 * @param  integer $group_id         Group id
	 * @param  string  $pickup_latitude  Pickup latitude
	 * @param  string  $pickup_longitude Pickup longitude
	 * @param  string  $pickup_location  Pickup location
	 * @param  string  $drop_latitude    Drop latitude
	 * @param  string  $drop_longitude   Drop longitude
	 * @param  string  $drop_location    Drop location
	 * @return null                      Empty
	 */
	public function search_and_send_request_to_driver($order, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location) {

		$driver_request = new DriverRequest;
		$driver_search_radius = 1000000000000000000;
		$sleep_time = 15;

		DriverRequest::status(['pending'])->groupId([$group_id])->update(['status' => $driver_request->statusArray['cancelled']]);

		if ($order->driver_id && $order->driver) {
			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.request_already_accepted'),
					'status_code' => '0',
				]
			)->send();
		}
		$drivers = Driver::search($drop_latitude, $drop_longitude, $driver_search_radius, $order->id)->get();

		if ($drivers->count() == 0) {
			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.no_drivers_found'),
					'status_code' => '0',
				]
			)->send();
		}

		$nearest_driver = $drivers->first();

		$driver_request->order_id = $order->id;
		$driver_request->driver_id = $nearest_driver->id;
		$driver_request->pickup_latitude = $pickup_latitude;
		$driver_request->pickup_longitude = $pickup_longitude;
		$driver_request->drop_latitude = $drop_latitude;
		$driver_request->pickup_location = $pickup_location;
		$driver_request->drop_longitude = $drop_longitude;
		$driver_request->drop_location = $drop_location;
		$driver_request->status = $driver_request->statusArray['pending'];
		$driver_request->save();

		$push_notification_title = "Your order preparation started for orderId #" . $order->id;
		$push_notification_data = [
			'type' => 'order_request',
			'request_id' => $driver_request->id,
			'pickup_location' => $pickup_location,
			'min_time' => '0',
			'pickup_latitude' => $pickup_latitude,
			'pickup_longitude' => $pickup_longitude,
		];

		push_notification($nearest_driver->user->device_type, $push_notification_title, $push_notification_data, $nearest_driver->user->type, $nearest_driver->device_id);

		$nexttick = time() + $sleep_time;
		$active = true;
		while ($active) {
			if (time() >= $nexttick) {
				$active = false;
				$this->search_and_send_request_to_driver($order, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
			}
		}
	}

	/**
	 * To get the order details
	 *
	 * @return Response Json response
	 */
	public function driver_order_details(Request $request) 
	{
		try 
		{
			$driver = Driver::authUser()->first();
			$order = new Order;
			$today_date = date("Y-m-d");

			$rules = [
				'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			];

			$validator = Validator::make($request->all(), $rules);
			if ($validator->fails()) {
				return response()->json([
						'status_message' => $validator->messages()->first(),
						'status_code' => '0',
					]);
			}

			$order 		= Order::where('id', $request->order_id)->first();
			$delivery 	= $order->order_delivery;
			$amount 	= ($order->payment_type == 0) ? (string) $order->total_amount : '';
			
			$request_data = DriverRequest::where('id', $order->order_delivery->request_id)->first();
			$array_list = explode(',',$request_data->orders_list);

			if(count($array_list)==1 && $array_list[0]==$request['order_id'] && $delivery->status != '0' && $delivery->status != '2' && $delivery->status != '6' ){
				$is_confirmed='1';
			}
			elseif (count($array_list)>1) {
			$delivery_order=OrderDelivery::driverId([$driver->id])->date($today_date)->orderBy("order_id", "desc")->whereIn('order_id',$array_list)->whereIn('status',[1,3,4,5,0])->get();
				$delivery_order=$delivery_order->where('status',0);
				$is_confirmed = count($delivery_order) ? '0' : '1';
			}
			else{
				$is_confirmed ='1';
			}

			if($delivery->priorty_order=='1'){
				$deduct_amount =0;
			}else {
				$deduct_amount = ($delivery->fee_type==0) ? 0 : ($delivery->pickup_fare+$delivery->drop_fare);
			}

			$total_fare = numberFormat(($delivery->total_fare - $deduct_amount) - $delivery->driver_earning  + $delivery->tips);

			$user_country_code = country::where('code',$order->user->country_code)->first();
			$multiple_delivery = site_setting('multiple_delivery');	

			$order_details = [
				'multiple_delivery' 	=> $multiple_delivery,
				'order_id' 				=> $order->id,
				'user_name' 			=> $order->user->name,
				'user_mobile_number' 	=> $order->user->mobile_number,
				'user_thumb_image'		=> $order->user->user_image_url,
				'store_name' 			=> $order->store->name,
				'store_mobile_number' 	=> $order->store->user->mobile_number,
				'store_thumb_image' 	=> store_images($order->store->id, '4'),
				'vehicle_type' 			=> $driver->vehicle_type_details->name,
				'pickup_location' 		=> $order->order_delivery->pickup_location,
				'pickup_latitude' 		=> $order->order_delivery->pickup_latitude,
				'pickup_longitude' 		=> $order->order_delivery->pickup_longitude,
				'drop_location' 		=> $order->order_delivery->drop_location,
				'drop_latitude' 		=> $order->order_delivery->drop_latitude,
				'drop_longitude' 		=> $order->order_delivery->drop_longitude,
				'status' 				=> $order->order_delivery->status,
				'map_image' 			=> $order->order_delivery->trip_path,
				'is_confirmed' 			=> $is_confirmed,
				'total_fare'			=> $total_fare,
				'collect_cash' 			=> $amount,
				'delivery_note' 		=> isset($order->user->user_address->delivery_note) ? $order->user->user_address->delivery_note : '',
				'flat_number' 			=> isset($order->user->user_address->apartment) ? $order->user->user_address->apartment : '',
				'user_country_code' 	=> $user_country_code->phone_code ?? '',
				'store_country_code' 	=> $order->store->user->country_code ?? '',
				'request_id' 			=> $request_data->id,
				'group_id' 				=> $request_data->group_id,		

				'order_items' => $order->order_item->map(function ($order_item) {
						$result = array();
						 $modifer_item = $order_item->order_item_modifier->map(function ($menu) {
						 	if($menu->modifer_item){
								return $menu->modifer_item->map(function ($item) { 
									return[
											'id'	=> $item->id,			
											'count' => $item->count,
											'price' => (string) number_format($item->price * $item->count,'2'),
											'name'  => $item->modifier_item_name,
									];
								});
							}
						})->toArray();
						foreach ($modifer_item as $key => $value){
							if (is_array($value)){
								 foreach($value as $keys =>$val){
							       	$result[] = $val;
							    }
							}
						}
						return [
							'name' => $order_item->menu_name,
							'quantity' => $order_item->quantity,
							'modifiers' => $result ?? [],
						];
					}
				)->toArray(),
			];
			return response()->json([
				'status_message' => trans('driver_api_language.driver.order_details_listed'),
				'status_code' => '1',
				'order_details' => $order_details,
			]);
		} 
		catch (\Exception $e) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $e->getMessage(),
			]);
		}
	}

	/**
	 * API for getting dropoff data
	 *
	 * @return Response Json response with status
	 */
	public function dropoff_data() {

		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;
		$rules = [
			'order_id' => 'required|exists:order,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$order = Order::where('id', $request->order_id)->first();

		$driver_delivery = IssueType::typeText("driver_delivery")->status()->get()->map(
			function ($issue) {
				return [
					'id' => $issue->id,
					'issue' => $issue->name,
				];
			}
		);

		$dropoff_options = $order->getDropoffOptions()->map(
			function ($value, $key) {
				return [
					'name' => $value,
					'id' => $key,
				];
			}
		);

		return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.order_cancel_reasons'),
				'status_code' => '1',
				'issues' => $driver_delivery,
				'dropoff_options' => $dropoff_options,
			]
		);
	}

	/**
	 * API for getting dropoff data
	 *
	 * @return Response Json response with status
	 */
	public function pickup_data() {
		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$order = Order::where('id', $request->order_id)->first();

		$driver_store_issues = IssueType::typeText("driver_store")->status()->get()->map(
			function ($issue) {
				return [
					'id' => $issue->id,
					'issue' => $issue->name,
				];
			}
		);

		return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.reasons_listed_successfully'),
				'status_code' => '1',
				'issues' => $driver_store_issues,
			]
		);
	}

	/**
	 * Add payout Preferences
	 *
	 * @param  Get method inputs
	 * @return Response in Json
	 */
	public function add_payout_perference(Request $request)
	{
		
		$driver = Driver::authUser()->first();
		$user = User::find($driver->user_id);

		$rules = array(
			'payout_method' => 'required|in:stripe,paypal,banktransfer',
		);

		$payout_method = ucfirst($request->payout_method);
		if ($payout_method == 'Stripe') {
			$rules['country'] = 'required|exists:country,code';
		}

		$messages = array(
            'required' 	=> ':attribute '.__('api_messages.register.field_is_required'), 
            'exists' 	=> __('api_messages.payout.field_is_selected')
        );
		$attributes = array(
			'payout_method' => __('api_messages.payout.payout_method'),
			'country' 		=> __('api_messages.payout.country'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$user_id = $user->id;
        $stripe_document 	= '';
		$account_holder_type= 'company';
		$country = $request->country;
		$request['payout_country'] = $request->country;
		$document = $request->file('document');
		$additional_document = $request->file('additional_document');
		if($document) {
			$file_path = $this->fileUpload($document, 'public/images/payout_documents/' . $user->id);
			$this->fileSave('stripe_document', $user_id, $file_path['file_name'], '1');
			$filename = $file_path['file_name'];
			$document_path = public_path(Storage::url($file_path['path']));
		}

		if($additional_document){
			$a_file_path = $this->fileUpload($additional_document, 'public/images/payout_documents/' . $user->id);
			$this->fileSave('stripe_document', $user_id, $a_file_path['file_name'], '1');
			$a_filename = $a_file_path['file_name'];
			$a_document_path = public_path(Storage::url($a_file_path['path']));
		}

		if ($payout_method == 'Stripe') {
			$stripe_payout = resolve('App\Repositories\StripePayout');
			$iban_supported_country = Country::getIbanRequiredCountries();

			$bank_data = array(
	            "country"       		=> $country,
	            "currency"   			=> $request->currency,
	            "account_holder_name"	=> $request->account_holder_name,
	            "account_holder_type" 	=> $account_holder_type,
	        );

			if (in_array($country, $iban_supported_country)) {
				$request['account_number'] = $request->iban;
				$bank_data['account_number'] = $request->iban;
			}
			else {
				if ($country == 'AU') {
					$request['routing_number'] = $request->bsb;
				}
				elseif ($country == 'HK') {
					$request['routing_number'] = $request->clearing_code . '-' . $request->branch_code;
				}
				elseif ($country == 'JP' || $country == 'SG') {
					$request['routing_number'] = $request->bank_code . $request->branch_code;
				}
				elseif ($country == 'GB') {
					$request['routing_number'] = $request->sort_code;
				}
				elseif($country == 'IN')
				{
					$request['routing_number'] = $request->ifsc_code;
				}
				$bank_data['routing_number'] = $request['routing_number'];
				$bank_data['account_number'] = $request->account_number;
			}

			$validate_data = $stripe_payout->validateRequest($request);
			if($validate_data) {
	            return $validate_data;
	        }
			$stripe_token = $stripe_payout->createStripeToken($bank_data);

			if(!$stripe_token['status']) {
				return response()->json([
				    'status_code' => '0',
				    'status_message' => $stripe_token['status_message'],
				]);
			}

			$request['stripe_token'] = $stripe_token['token'];

			$stripe_preference = $stripe_payout->createPayoutPreference($request);

			if(!$stripe_preference['status']) {
				return response()->json([
				    'status_code' => '0',
				    'status_message' => $stripe_preference['status_message'],
				]);
			}

			$recipient = $stripe_preference['recipient'];
			if(isset($document_path)) {
				$document_result = $stripe_payout->uploadDocument($document_path,$recipient->id);
				if(!$document_result['status']) {
					return response()->json([
					    'status_code' => '0',
					    'status_message' => $document_result['status_message'],
					]);
				}
				$stripe_document = $document_result['stripe_document'];
			}

			if(isset($a_document_path)) {
				$stripe_document = (isset($document_path)) ? $stripe_document : '';
				$document_result = $stripe_payout->uploadAdditonalDocument($stripe_document,$a_document_path,$recipient->id,$recipient->individual->id);
				if(!$document_result['status']) {
					return response()->json([
					    'status_code' => '0',
					    'status_message' => $document_result['status_message'],
					]);
				}
			}

			$payout_email = isset($recipient->id) ? $recipient->id : $user->email;
           	$payout_currency = $request->currency ?? '';

		}
		if ($payout_method == 'Paypal') {
            $payout_email = $request->paypal_email;
            $payout_currency = PAYPAL_CURRENCY_CODE;
        }

        if ($payout_method == 'Banktransfer') {
            $payout_email       = $request->account_number;
            $payout_currency    = "";
            $request['branch_code']= $request->swift_code;
        }

       $paypalUpdate = PayoutPreference::where('paypal_email',$request->paypal_email)->where('user_id',$user_id)->where('payout_method',$payout_method)->first();

	       if($paypalUpdate)
	       {
	       		$payout_preference = PayoutPreference::where('paypal_email',$request->paypal_email)->where('user_id',$user_id)->where('payout_method',$payout_method)->first();
	       }
	       else
	       {
	       		$payout_preference = new PayoutPreference;
	       }
		
		$payout_preference->user_id 		= $user_id;
		$payout_preference->country 		= $country ?? '';
		$payout_preference->currency_code 	= $payout_currency;
		$payout_preference->routing_number 	= $request->routing_number ?? '';
		$payout_preference->account_number 	= $request->account_number ?? '';
		$payout_preference->holder_name 	= $request->account_holder_name ?? ''?? '';
		$payout_preference->holder_type 	= $account_holder_type;
		$payout_preference->paypal_email 	= $payout_email;
		$payout_preference->address1 	= $request->address1 ?? '';
		$payout_preference->address2 	= $request->address2 ?? '';
		$payout_preference->city 		= $request->city ?? '' ;
		$payout_preference->state 		= $request->state ?? '';
		$payout_preference->postal_code = $request->postal_code ?? '';
		if (isset($document_path)) {
			$payout_preference->document_id 	= $stripe_document;
			$payout_preference->document_image 	= $filename;
		}
		if (isset($a_document_path)) {
			$payout_preference->additional_document_image 	= $a_filename;
		}
		$payout_preference->phone_number 	= $request->phone_number ?? '';
		$payout_preference->branch_code 	= $request->branch_code ?? '';
		$payout_preference->bank_name		= $request->bank_name ?? '';
		$payout_preference->branch_name 	= $request->branch_name ?? '';
		$payout_preference->bank_location     = $request->bank_location ?? '';
		$payout_preference->ssn_last_4 		= $country == 'US' ? $request->ssn_last_4 : '';
		$payout_preference->payout_method 	= $payout_method;
		$payout_preference->address_kanji 	= isset($address_kanji) ? json_encode($address_kanji) : json_encode([]);
		$payout_preference->save();
		$payout_check = PayoutPreference::where('user_id', $user_id)->where('default', 'yes')->count();

		if ($payout_check == 0) {
			$payout_preference->default = 'yes';
			$payout_preference->save();
		}
		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> trans('driver_api_language.driver.payout_details_added'),
		]);
	}

	/**
	 * Driver to confirm the order
	 *
	 * @return Response Json response
	 */
	public function confirm_order_delivery() {

		$request = request();
		$driver = Driver::authUser()->first();
		$issue_type = new IssueType;
		$order_delivery = new OrderDelivery;
		// dd($order_delivery);
		$order = Order::where('id', $request->order_id)->first();
		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'is_thumbs' => 'required|in:0,1',

			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['pending'],
		];

		$messages = [
			//'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

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
		$review->type = $review->typeArray['driver_store'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->store_id;
		$review->is_thumbs = $request->is_thumbs;
		$review->comments = $request->comments ?: "";
		$review->save();
		if ($request->issues) {
			$issues = explode(',', $request->issues);
			if ($request->is_thumbs == 0 && count($issues) > 0) {
				foreach ($issues as $issue_id) {
					$review_issue = new ReviewIssue;
					$review_issue->review_id = $review->id;
					$review_issue->issue_id = $issue_id;
					$review_issue->save();
				}
			}
		}

		$order->order_delivery->confirmed();

		return response()->json(
			[
				'status_message' =>trans('driver_api_language.driver.order_confirmed'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * Driver to start the order delivery
	 *
	 * @return Response Json response
	 */
	public function start_order_delivery(Request $request)
	{
		$driver = Driver::authUser()->first();
		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['confirmed'],
		];

		$messages = [
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order->order_delivery->started();
		
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.driver.order_started'),
		]);
	}

	/**
	 * Driver to drop off delivery
	 *
	 * @return Response Json response
	 */
	public function drop_off_delivery() {
		$request = request();
		$driver = Driver::authUser()->first();
		$issue_type = new IssueType;

		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);
		$request_data['order_delivery_id'] = $order_delivery_id;

		$dropoff_recipient = $order->getDropoffOptions()->keys()->implode(',');
		
		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'recipient' => 'required|in:' . $dropoff_recipient,
			'is_thumbs' => 'required|in:0,1',
			// 'issues' => 'required_if:is_thumbs,0',
			// 'issues_array.*' => 'required_if:is_thumbs,0|exists:issue_type,id,type_id,' . $issue_type->typeArray['driver_delivery'],
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['started'],
		];

		$messages = [
			// 'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

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
		$review->type = $review->typeArray['driver_delivery'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->user_id;
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

		$order->order_delivery->delivered();

		$order->delivery_delivered($request->recipient);

		return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.order_dropped_off'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * Driver to complete the order delivery
	 *
	 * @return Response Json response
	 */
	public function complete_order_delivery()
	{
		$request = request();
		$driver = Driver::authUser()->first();
		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();
		$pickup_drop_fare='true';
		$trip_status = $driver->status;
		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);
		$request_data['order_delivery_id'] = $order_delivery_id;
		
		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['delivered'],
		];

		$messages = [
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		// Trip Map upload //
		$file = $request->file('map_image');
		if ($file) {
			$file_path = $this->fileUpload($file, 'public/images/trip_image/' . $order->id);
			$this->fileSave('trip_image', $order->id, $file_path['file_name'], '1');
			$original_path = Storage::url($file_path['path']);
		}
		$driver_request=DriverRequest::where('id',$order->order_delivery->request_id)->first();
		if($driver_request){
			$array_list=explode(',',$driver_request->orders_list);
			$delivery=OrderDelivery::where('driver_id',$driver->id)->where('status',5)->whereIn('order_id',$array_list)->get();
			if(count($delivery)>0)
			$pickup_drop_fare='false';
			else
			$pickup_drop_fare='true';
		}else{
			$pickup_drop_fare='true';	
			$array_list=[];		
		}
		$order->order_delivery->completed($pickup_drop_fare);
		$driver_status=OrderDelivery::where('driver_id',$driver->id)->whereIn('order_id',$array_list)->get();
		$driver_status=$driver_status->whereIn('status',[0,1,3,4]);

		if(count($driver_status)==0){
			Driver::where('id',$driver->id)->update(['status'=>1]);
			$trip_status=1;
		}
		if(count($driver_status)>0){
				$near_request = OrderDelivery::where('driver_id', $driver->id)->whereIn('order_id',$array_list)->whereIn('status',[0,1,3,4])->select(DB::raw('*, ( 6371 * acos( cos( radians( drop_latitude ) ) * cos( radians( pickup_latitude ) ) * cos( radians( pickup_longitude ) - radians( drop_longitude ) ) + sin( radians( drop_latitude ) ) * sin( radians( pickup_latitude ) ) ) ) as distance'))->orderBy('distance','ASC')->get();
				$near_request=$near_request->first();
				$store_name=Order::where('id',$near_request->order_id)->first();
				$push_notification_title = "Remaining Order ".count($driver_status);
				$push_notification_data = [
					'type' => 'remaining_type',
					'order_id' => $near_request->order_id,
					'restaurant_name' => $store_name->store->name,
					
				];	
			push_notification($driver->user->device_type, $push_notification_title, $push_notification_data, $driver->user->type, $driver->user->device_id,false);
		}

		return response()->json(
			[
				'status_message' => trans('driver_api_language.orders.order_completed'),
				'status_code' => '1',
				'driver_status' => $trip_status,
			]
		);
	}

	/**
	 * Driver to cancel order
	 *
	 * @return Response Json response with status
	 */
	// public function cancel_order_delivery() {

	// 	$request = request();
	// 	$driver = Driver::authUser()->first();

	// 	$order_delivery = new OrderDelivery;
	// 	$order = Order::where('id', $request->order_id)->first();

	// 	$order_delivery_id = 0;
	// 	if ($order && $order->order_delivery) {
	// 		$order_delivery_id = $order->order_delivery->id;
	// 	}

	// 	$request_data = $request->all();
	// 	$request_data['order_delivery_id'] = $order_delivery_id;

	// 	$rules = [
	// 		'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
	// 		'order_delivery_id' => 'exists:order_delivery,id',
	// 		'cancel_reason' => 'required|exists:order_cancel_reason,id',
	// 	];

	// 	$messages = [

	// 		'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
	// 	];

	// 	$validator = Validator::make($request_data, $rules, $messages);

	// 	if ($validator->fails()) {
	// 		return response()->json(
	// 			[
	// 				'status_code' => '0',
	// 				'status_message' => $validator->messages()->first(),
	// 			]
	// 		);
	// 	}

	// 	if ($order->order_delivery->status_text == 'pending' || $order->order_delivery->status_text == 'confirmed') {

	// 		$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

	// 		$order->order_delivery->revert();

	// 	} else {

	// 		$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

	// 		$order->order_delivery->cancelled();

	// 		//Revert Penality amount if exists

	// 		$penality_Revert = revertPenality($order->id);

	// 	}

	// 	return response()->json(
	// 		[
	// 			'status_message' => trans('api_messages.driver.order_has_been_cancelled'),
	// 			'status_code' => '1',
	// 		]
	// 	);
	// }

	public function cancel_order_delivery() {

		$request = request();
		$driver = Driver::authUser()->first();
		$trip_status=2;
		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,'.$driver->id,
			'order_delivery_id' => 'exists:order_delivery,id',
			'cancel_reason' => 'required|exists:order_cancel_reason,id',
		];

		$messages = [

			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		if ($order->order_delivery->status_text == 'pending' || $order->order_delivery->status_text == 'confirmed') {

			$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

			$order->order_delivery->revert();
			$request_data=DriverRequest::where('id',$request->request_id)->first();
		
			$array_list=explode(',',$request_data->orders_list);
			$driver_status=OrderDelivery::where('driver_id',$driver->id)->whereIn('order_id',$array_list)->get();
			$driver_status=$driver_status->whereIn('status',[0,1,3,4]);
			
			if(count($driver_status)==0){
				$trip_status=1;
			}
			if(count($driver_status)>0){				
				$near_request = OrderDelivery::where('driver_id', $driver->id)->whereIn('order_id',$array_list)->whereIn('status',[0,1,3,4])->select(DB::raw('*, ( 6371 * acos( cos( radians( drop_latitude ) ) * cos( radians( pickup_latitude ) ) * cos( radians( pickup_longitude ) - radians( drop_longitude ) ) + sin( radians( drop_latitude ) ) * sin( radians( pickup_latitude ) ) ) ) as distance'))->orderBy('distance','ASC')->get();	
				$near_request=$near_request->first();
				$store_name=Order::where('id',$near_request->order_id)->first();
				$push_notification_title = "Remaining Order ".count($driver_status);
				$push_notification_data = [
					'type' => 'remaining_type',
					'order_id' => $near_request->order_id,
					'restaurant_name' => $store_name->store->name,
					
				];	
				
			push_notification($driver->user->device_type, $push_notification_title, $push_notification_data, $driver->user->type, $driver->user->device_id,false,'cancel_route');			
		}

		} else {

			$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

			$order->order_delivery->cancelled();

			//Revert Penality amount if exists

			$penality_Revert = revertPenality($order->id);
			$request_data=DriverRequest::where('id',$request->request_id)->first();
			$array_list=explode(',',$request_data->orders_list);
			$driver_status=OrderDelivery::where('driver_id',$driver->id)->whereIn('order_id',$array_list)->get();
			$driver_status=$driver_status->whereIn('status',[0,1,3,4]);
			if(count($driver_status)>0){				
				$near_request = OrderDelivery::where('driver_id', $driver->id)->whereIn('order_id',$array_list)->whereIn('status',[0,1,3,4])->select(DB::raw('*, ( 6371 * acos( cos( radians( drop_latitude ) ) * cos( radians( pickup_latitude ) ) * cos( radians( pickup_longitude ) - radians( drop_longitude ) ) + sin( radians( drop_latitude ) ) * sin( radians( pickup_latitude ) ) ) ) as distance'))->orderBy('distance','ASC')->get();	
				$near_request=$near_request->first();
				$store_name=Order::where('id',$near_request->order_id)->first();
				$push_notification_title = "Remaining Order ".count($driver_status);
				$push_notification_data = [
					'type' => 'remaining_type',
					'order_id' => $near_request->order_id,
					'restaurant_name' => $store_name->store->name,
					
				];
			
				push_notification($driver->user->device_type, $push_notification_title, $push_notification_data, $driver->user->type, $driver->user->device_id,false,'cancel_route');
				
		}
			
		}
		DriverRequest::where('id',$request->request_id)->update(['status'=>2]);
		return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.order_has_been_cancelled'),
				'status_code' => '1',
				'driver_status' => $trip_status,
			]
		);
	}
	/**
	 * get owe amount
	 *
	 * @return Response Json response with status
	 */
	public function get_owe_amount() {
		$request = request();
		$driver = Driver::authUser()->first();

		$owe_amount = DriverOweAmount::where('user_id', $driver->user_id)->first();

		if($owe_amount) {

			$owe_amount->amount = $owe_amount->amount;
			$owe_amount->currency_code = $owe_amount->currency_code;
			$owe_amount->save();

			$amount = $owe_amount->amount;
			$currency_code = $owe_amount->currency_code;

			return response()->json([
				'status_message' => trans('driver_api_language.driver.payout_successfully'),
				'status_code' => '1',
				'amount' => $amount,
				'currency_code' => $currency_code,
			]);
		} else {
			return response()->json([
				'status_message' => trans('driver_api_language.driver.not_generate_amount'),
				'status_code' => '1',
			]);
		}
	}

	/**
	 * payout to admin
	 *
	 * @return Response Json response with status
	 */
	public function pay_to_admin(Request $request) {

		$driver = Driver::authUser()->first();

		$owe_amount = DriverOweAmount::where('user_id', $driver->user_id)->first();
		if($owe_amount == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('driver_api_language.driver.not_generate_amount'),
			]);
		}

		$currency_code = $owe_amount->getRawOriginal('currency_code');
		$user_currency = $driver->user->currency_code->code;
		$amount = currencyConvert($user_currency,$currency_code,floatval($request->amount));
		$total_owe_amount = $owe_amount->amount;
		$remaining_amount = $total_owe_amount - $amount;

		if ($total_owe_amount <= 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('driver_api_language.driver.owe_amount_empty'),
			]);
		}

		$stripe_payment = resolve('App\Repositories\StripePayment');

		if($request->payment_method == 1){

			if($request->filled('payment_intent_id')) {
				$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
			} else {
				$user_payment_method = UserPaymentMethod::where('user_id', $driver->user_id)->first();

				$paymentData = array(
					"amount" 		=> $amount * 100,
					'currency' 		=> $currency_code,
					'description' 	=> 'Payment for Owe Amount by : '.$driver->user->first_name,
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
		$payment->user_id = $driver->user_id;
		$payment->order_id = $request->order_id;
		$payment->transaction_id = $payment_result->transaction_id;
		$payment->type = 0;
		$payment->amount = $amount;
		$payment->status = 1;
		$payment->currency_code = $currency_code;
		$payment->save();

		$owe_amount = DriverOweAmount::where('user_id', get_current_login_user_id())->first();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('driver_api_language.driver.payout_successfully'),
			'owe_amount' 		=> $owe_amount->amount,
			'currency_code' 	=> $owe_amount->currency_code,
		]);
	}

	/**
	 * Display payout details
	 *
	 * @param  Get method request inputs
	 * @return Response in Json
	 */
	public function payout_details() {
		$payout_details = $this->get_payout_details();

		if (count($payout_details) == 0) {
			return response()->json(['status_message' => trans('driver_api_language.driver.no_data_found'), 'status_code' => '0']);
		}

		return response()->json(
			[

				'status_message' => trans('driver_api_language.driver.payoutpreference_details'),

				'status_code' => '1',

				'payout_details' => $payout_details,

			]
		);
	}

	/**
	 * Payout Set Default and Delete
	 *
	 * @param  Get method request inputs
	 * @param  Type  Default   Set Default payout
	 * @param  Type  Delete    Delete payout Details
	 * @return Response in Json
	 */
	public function payout_changes(Request $request) {
		$request = request();
		$driver = Driver::authUser()->first();

		$rules = array(

			'payout_id' => 'required|exists:payout_preference,id',

			'type' => 'required',

		);

		$niceNames = array('payout_id' => 'Payout Id');

		$messages = array('required' => ':attribute is required.');

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return response()->json(
				[

					'status_message' => $error_msg['0']['0']['0'],

					'status_code' => '0',

				]
			);
		}

		//check valid user or not
		$check_user = PayoutPreference::where('id', $request->payout_id)

			->where('user_id', $driver->user_id)

			->first();

		if ($check_user->count()  < 1) {
			return response()->json(
				[

					'status_message' => trans('driver_api_language.payout.permission_denied'),

					'status_code' => '0',

				]
			);
		}

		//check valid type or not
		if ($request->type != 'default' && $request->type != 'delete') {
			return response()->json(
				[

					'status_message' => trans('driver_api_language.payout.the_selected_type_is_invalid'),

					'status_code' => '0',

				]
			);
		}

		//set default payout
		if ($request->type == 'default') {
			$payout = PayoutPreference::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => trans('driver_api_language.payout.the_given_payout_id'),

						'status_code' => '0']
				);
			} else {
				//Changed default option No in all Payout based on user id
				$payout_all = PayoutPreference::where('user_id', $driver->user_id)->update(['default' => 'no']);

				$payout->default = 'yes';

				$payout->save(); //save payout detils

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => trans('driver_api_language.payout.payout_preferences_successfully'),

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
		//Delete payout

		if ($request->type == 'delete') {
			$payout = PayoutPreference::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => trans('driver_api_language.payout.permission_denied_default_payout'),

						'status_code' => '0',

					]
				);
			} else {
				$payout->delete(); //Delete payout.

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => trans('driver_api_language.payout.payout_details_deleted_successfully'),

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
	}

	public function get_payout_details() {
		$request = request();
		$driver = Driver::authUser()->first();

		//get payout preferences details

		$payout_details = @PayoutPreference::where('user_id', $driver->user_id)->get();

		$data = [];

		foreach ($payout_details as $payout_result) {
			$data[] = array(

				'payout_id' => $payout_result->id,

				'user_id' => $payout_result->user_id,

				'payout_method' => $payout_result->payout_method != null

				? $payout_result->payout_method : '',

				'paypal_email' => $payout_result->paypal_email != null

				? $payout_result->paypal_email : '',

				'set_default' => ucfirst($payout_result->default),

			);
		}

		return $data;
	}

	public function earning_list() {

		$request = request();
		$driver = Driver::authUser()->first();

		$rules = [
			'type' => 'required|in:week,weekly,date',
			'start_date' => 'required|date|date_format:Y-m-d',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$start_of_the_week = 0;

		if ($request->type == 'week') {

			$start_date = strtotime($request->start_date);

			$week = date('W', $start_date);
			$year = date('Y', $start_date);

			list($week_start_date, $week_end_date) = $this->getStartAndEndDate($week, $year);

			$order_delivery_list = OrderDelivery::with('order')->driverId([$driver->id])
			->week(date('Y-m-d', strtotime($week_start_date)))
			->status(['completed'])
			->select()
			->addSelect(\DB::raw("DATE_FORMAT(confirmed_at, '%d-%m-%Y') as date"))
			->get()
			->groupBy('date');

			$order_delivery_list = $order_delivery_list->flatMap(function($date_list, $date) {
				$tips = 0;
				foreach($date_list as $order){
					$tips+= $order->order->tips;
				}	
				return [
					$date => [
						"total_fare" => numberFormat(abs($date_list->sum("total_fare") - $date_list->sum("driver_earning") + $date_list->sum("tips")) ),
						"day" => trans('api_messages.weekday.'.date('l', strtotime($date))),
						"date" => $date,
					],
				];
			});

			$date_list = array();

			$current_date = strtotime($week_start_date);

			while ($current_date <= strtotime("+6 days", strtotime($week_start_date))) {

				$date = date('d-m-Y', $current_date);
				$order_data = isset($order_delivery_list[$date]) ? $order_delivery_list[$date] : [
					"total_fare" => "0",
					"day" => trans('api_messages.weekday.'.date('l', strtotime($date))),
					"date" => $date,
				];

				$date_list[] = $order_data;
				$current_date = strtotime("+1 day", $current_date);
			}

			$last_trip_total_fare = end($date_list)['total_fare'];

			$earning_list = [
				'total_fare' => numberFormat(abs($order_delivery_list->sum('total_fare')-$order_delivery_list->sum('driver_earning')) ),
				'date_list' => $date_list,
				'last_trip_total_fare' => $last_trip_total_fare,
				'last_payout' => '0',
			];
		}

		$earning_list['currency_code'] = $driver->user->currency_code;
		$earning_list['currency_symbol'] = $driver->user->currency_symbol;

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.payout.earning_list_listed_successfully'),
			'earning_list' => $earning_list,
		]);
	}


	//Driver Past Delivery History
	public function past_delivery(Request $request) 
	{
		try 
		{
			$driver 		= Driver::authUser()->first();
			$today_date 	= date("Y-m-d");
			$past_delivery 	= OrderDelivery::driverId([$driver->id])->past($today_date)->orderBy("order_id", "desc")->paginate(PAGINATION);
			$total_page		= $past_delivery->lastPage();

			$past_delivery = $past_delivery->map(
				function ($delivery) use($driver,$today_date) {
					$request=DriverRequest::where('id',@$delivery->request_id)->first();
					$array_list=explode(',',$request->orders_list);
					if(count($array_list)==1 && $array_list[0]==$request['order_id'] && $delivery->status != '0' && $delivery->status != '2' && $delivery->status != '6' ){
						$is_confirmed='1';
					}elseif (count($array_list)>1) {
						$delivery_order=OrderDelivery::driverId([$driver->id])->past($today_date)->orderBy("order_id", "desc")->whereIn('order_id',$array_list)->whereIn('status',[1,3,4,5,0])->get();
						$delivery_order=$delivery_order->where('status',0);
						$is_confirmed = count($delivery_order) ? '0' : '1';
					}else
						$is_confirmed ='1';
						$deduct_amount =0;
						/*if($delivery->priorty_order=='1')
						$deduct_amount =0;
						else
						$deduct_amount =($delivery->fee_type==0)?0:($delivery->pickup_fare+$delivery->drop_fare);*/

					return [
						'id' => $delivery->order_id,
						'total_fare' => numberFormat(($delivery->total_fare-$deduct_amount) - $delivery->driver_earning +  $delivery->tips ),
						'vehicle_name' => $delivery->driver->vehicle_type_name,
						'status' => $delivery->status,
						'map_image' => $delivery->trip_path,
						'is_confirmed' => $is_confirmed,
						'group_id' => $request?$request->group_id:null,					
					];
				}
			);	
			return response()->json([
				'status_code' 		=> '1',
				'status_message'	=> trans('driver_api_language.payout.order_delivery_history'),
				'current_page' 		=> (int) $request->page,
				'total_page' 		=> $total_page,
				'past_delivery' 	=> $past_delivery,
			]);
			
		} catch (\Exception $e) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $e->getMessage(),
			]);
		}
	}


	//Driver Today Delivery History
	public function today_delivery(Request $request) 
	{
		try 
		{
			$driver 		= Driver::authUser()->first();
			$today_date 	= date("Y-m-d");
			$today_delivery = OrderDelivery::driverId([$driver->id])->date($today_date)->orderBy("order_id", "desc")->paginate(PAGINATION);
			$total_page		= $today_delivery->lastPage();

			$today_delivery = $today_delivery->map(
				function ($delivery) use($driver,$today_date){
					$request=DriverRequest::where('id',@$delivery->request_id)->first();
					$array_list=explode(',',$request->orders_list);
					if(count($array_list)==1 && $array_list[0]==$request['order_id'] && $delivery->status != '0' && $delivery->status != '2' && $delivery->status != '6' ){
						$is_confirmed='1';
					}elseif (count($array_list)>1) {
						$delivery_order=OrderDelivery::driverId([$driver->id])->date($today_date)->orderBy("order_id", "desc")->whereIn('order_id',$array_list)->whereIn('status',[1,3,4,5,0])->get();
						$delivery_order=$delivery_order->where('status',0);
						$is_confirmed = count($delivery_order) ? '0' : '1';
					}else
						$is_confirmed ='1';
						
						$deduct_amount =0;
						/*if($delivery->priorty_order=='1')
						$deduct_amount =0;
						else
						$deduct_amount =($delivery->fee_type==0)?0:($delivery->pickup_fare+$delivery->drop_fare);*/
					
					return [
						'id' => $delivery->order_id,
						'total_fare' => numberFormat(($delivery->total_fare-$deduct_amount) - $delivery->driver_earning  + $delivery->tips),
						'vehicle_name' => $delivery->driver->vehicle_type_name,
						'status' => $delivery->status,
						'map_image' => $delivery->trip_path,
						'is_confirmed' => $is_confirmed,
						'group_id' => $request?$request->group_id:null,							
					];
				}
			);
			return response()->json([
				'status_code' 		=> '1',
				'status_message'	=> trans('driver_api_language.payout.order_delivery_history'),
				'current_page' 		=> (int) $request->page,
				'total_page' 		=> $total_page,
				'today_delivery' 	=> $today_delivery,
			]);
			
		} catch (\Exception $e) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $e->getMessage(),
			]);
		}
	}

	public function weekly_trip(Request $request) 
	{
		try 
		{
			$driver = Driver::authUser()->first();
			$weekly_trip = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')
				->status(['completed']);

			$weekly_trip_count = $weekly_trip->get()->groupBy(function ($date) {
					return Carbon::parse($date->confirmed_at)->format('W');
				});

			$weekly_trip = $weekly_trip->paginate(PAGINATION)
				->groupBy(function ($date) {
					return Carbon::parse($date->confirmed_at)->format('W');
				});

			// get number of pagination pages
			$total_page = total_pagination($weekly_trip_count);

			foreach ($weekly_trip as $key => $value) {
				$total = 0;
				foreach ($value as $fare) {
					$total += $fare->total_fare - $fare->driver_earning + $fare->tips;
					$year = date('Y', strtotime($fare->confirmed_at));
				}
				$date = getWeekDates($year, $key);
				$format_date = date('d', strtotime($date['week_start'])).trans('user_api_language.monthandtime.'.date('M', strtotime($date['week_start']))) . '-' . date('d', strtotime($date['week_end'])).trans('user_api_language.monthandtime.'.date('M', strtotime($date['week_end'])));
				$week[] = ['week' => $format_date,
					'total_fare' => numberFormat(abs($total)),
					'year' => $year,
					'date' => $date['week_start']];
			}
			return response()->json(
				[
					'status_code' 		=> '1',
					'status_message' 	=> trans('driver_api_language.payout.successfully'),
					'total_page' 		=> $total_page,
					'current_page' 		=> (int) $request->page,
					'trip_week_details' => isset($week) ? $week : [],
				]
			);	
		} 
		catch (\Exception $e) {
			return response()->json(
				[
					'status_code' 		=> '0',
					'status_message' 	=> $e->getMessage(),
				]
			);
		}
	}


	public function weekly_statement() 
	{

		$cash_collected = 0;
		$request = request();
		$driver = Driver::authUser()->first();
		$from = $request->date;

		$date = strtotime("+6 day", strtotime($from));
		$to = date('Y-m-d', $date);
		$details = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')->select()->addSelect(\DB::raw("DATE_FORMAT(confirmed_at, '%d-%m-%Y') as date"))->whereBetween(DB::raw('Date(confirmed_at)'), [$from, $to])->status(['completed'])->get();
		$statement = $details->groupBy('date');
		$common = Order::whereDriverId($driver->id)->status('completed')->whereBetween(DB::raw('Date(updated_at)'), [$from, $to]);
		$cash = (clone $common)->where('payment_type', 0)->where('total_amount', '!=', 0)->get();
		$driver_fee = (clone $common)->where('payment_type', 0)->get();
		$card = (clone $common)->where('payment_type', '!=', 0)->get();
		$driver_commision_fee = numberFormat($driver_fee->sum('driver_commision_fee') + $card->sum('driver_commision_fee'));
		$driver_payout = 0;
		foreach($cash  as $key => $value )
		{
			if ($value['payment_type'] == 0 && $value['total_amount'] != 0) {
				$cash_collected = $cash_collected + numberFormat($value['total_amount'] );
			}
			else {
				$cash_collected = $cash_collected + '0.00';
			}
		}

		$payout = Payout::whereUserId($driver->user_id)->whereBetween(DB::raw('Date(updated_at)'), [$from, $to])->get()->sum('amount');
		$total = 0;
		$statement = $statement->flatMap(
			function ($date_list, $date) {
				return [
					[  "total_fare" => numberFormat($date_list->sum("total_fare")),
						"driver_earning" => numberFormat($date_list->sum("total_fare") - $date_list->sum("driver_earning") + $date_list->sum("tips")),
						"day" => trans('user_api_language.weekday.'.date('l', strtotime($date))),
						"format" => date('d/m', strtotime($date)),
						"date" => date('Y-m-d', strtotime($date)),
						"tips" => numberFormat($date_list->sum("tips")),
					],
				];
			}
		);
		
		$total = array_column($statement->toArray(), 'total_fare');
		$total = numberFormat(array_sum($total));
		$tips = array_column($statement->toArray(), 'tips');
		
		$tips = numberFormat(array_sum($tips));

		$total_fare = numberFormat( ($total + $tips) - $driver_commision_fee) ;
		$payout = $payout ;
		return response()->json(
			[
				'status_code' => '1',
				'status_message' => trans('driver_api_language.payout.successfully'),
				'statement' => $statement,
				'total_fare' => (string) numberFormat($total_fare),
				'base_fare' => (string) $total,
				'access_fee' => (string) $driver_commision_fee,
				'cash_collected' => (string) numberFormat($cash_collected),
				'completed_trips' => $details->count(),
				'format_date' => trans('user_api_language.monthandtime.'.date('M', strtotime($from))).date('d', strtotime($from)) . '-' .trans('user_api_language.monthandtime.'.date('M', strtotime($to))). date('d', strtotime($to)),				
				'bank_deposits' => (string) numberFormat($payout),
				'tips' => (string) numberFormat($tips),

			]
		);

	}

	public function daily_statement(Request $request) 
	{
		try 
		{
			$driver = Driver::authUser()->first();
			$from 	= $request->date;

			$daily_statement = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')->where(DB::raw('Date(confirmed_at)'), $from)->status(['completed'])->paginate(PAGINATION);

			$total_page = $daily_statement->lastPage();

			$total_fare = numberFormat(abs($daily_statement->sum('total_fare')));
			$common = Order::whereDriverId($driver->id)->status('completed')->where(DB::raw('Date(updated_at)'), $from);
			$cash = (clone $common)->where('payment_type', 0)->where('total_amount', '!=', 0)->get();
			$driver_fee = (clone $common)->where('payment_type', 0)->get();
			$card = (clone $common)->where('payment_type', '!=' , 0)->get();

			$driver_commision_fee = ($driver_fee->sum('driver_commision_fee')) + ($card->sum('driver_commision_fee'));
			$cash_collected = 0;
			$driver_payout  = 0;
			foreach($cash  as $key => $value )
			{
				if ($value['payment_type'] == 0 && $value['total_amount'] != 0) {
					if ($value['owe_amount'] != 0) {
						$driver_payout =  ($value->order_delivery->total_fare - $value->driver_commision_fee);
						$cash_collected = $cash_collected + numberFormat($value['owe_amount'] + $driver_payout + $value['tips']);
					}
					else {
						
						$cash_collected = $cash_collected + numberFormat($value['total_amount'] );
					}
				}
				else {
					$cash_collected = $cash_collected + '0.00';
				}
			}
			$daily_statement = $daily_statement->map(
				function ($daily) {
					return [
						'id' 				=> $daily->order_id,
						'total_fare' 		=> numberFormat(abs($daily->total_fare) + $daily->order->tips),
						"driver_earning" 	=> numberFormat(abs($daily->total_fare - $daily->driver_earning + $daily->order->tips)),
						'time' 				=> date('h:i', strtotime($daily->confirmed_at)).trans('user_api_language.monthandtime.'.date('a', strtotime($daily->confirmed_at))),
						"tips" 				=> $daily->order->tips,
					];
				}
			);

			$payout = Payout::whereUserId($driver->user_id)->where(DB::raw('Date(updated_at)'), $from)->get()->sum('amount');

			$tips = $daily_statement->sum('tips');
			$earning = $daily_statement->sum('driver_earning');
			return response()->json(
				[
					'status_code' 		=> '1',
					'status_message' 	=> trans('driver_api_language.payout.successfully'),
					'total_page' 		=> $total_page,
					'current_page' 		=> (int) $request->page,
					'daily_statement' 	=> $daily_statement,
					'date' 				=> $from,
					'format_date' 		=> date('d/m', strtotime($from)),
					"day" 				=> trans('user_api_language.weekday.'.date('l', strtotime($from))),
					'total_fare' 		=> (string) numberFormat(abs($earning)),
					'base_fare' 		=> (string) $total_fare ,
					'access_fee' 		=> (string) numberFormat(abs($driver_commision_fee)),
					'cash_collected' 	=> (string) numberFormat(abs($cash_collected)),
					'completed_trips' 	=> $daily_statement->count(),
					'bank_deposits' 	=> (string) numberFormat(abs($payout)),
					'time_online' 		=> '',
					'tips' 				=> (string) numberFormat(abs($tips))
				]
			);
		} 
		catch (\Exception $e) {
			return response()->json(
				[
					'status_code' 		=> '0',
					'status_message' 	=> $e->getMessage(),
				]);
		}
	}

	public function particular_order()
	{
		$request = request();
		$driver = Driver::authUser()->first();

		$trip_details = OrderDelivery::with('order.payout_table')->OrderId([$request->order_id])->first();
			
		$vehicle_type = $trip_details->driver ? $trip_details->driver->vehicle_type_name : '';
		$trip_details = replace_null_value($trip_details->toArray());
		$driver_commision_fee = numberFormat($trip_details['order']['driver_commision_fee']);
		$total_fare = $trip_details['total_fare'];
		$driver_payout = numberFormat( ($total_fare + $trip_details['tips'] )- $driver_commision_fee);
		if ($trip_details['order']['payment_type'] == 0 && $trip_details['order']['total_amount'] != 0) {
			$cash_collected = numberFormat($trip_details['order']['total_amount'] );
		}
		else {
			$cash_collected = '0.00';
		}
		
		if ($trip_details['confirmed_at']) {
			$date = $trip_details['confirmed_at'];
		}
		else {
			$date = $trip_details['updated_at'];
		}

		$can_payout = Payout::where('order_id', $trip_details['order_id'])->
			where('user_id', $driver->user_id)->first();

		$cancel_payout = 0;

		if($can_payout) {
			$cancel_payout = $can_payout->amount;
		}

		$owe_amount = '0';
		$distance_fare = '0';
		$pickup_fare = '0';
		$drop_fare = '0';

		if ($trip_details['status'] != '6') {
			$owe_amount = $trip_details['order']['owe_amount'];
			$distance_fare = $trip_details['distance_fare'];
			$pickup_fare = $trip_details['pickup_fare'] ? (string) $trip_details['pickup_fare'] : '0.00';
			$drop_fare = $trip_details['drop_fare'] ? $trip_details['drop_fare'] : '0.00';
			$total_fare = $total_fare ;
		}
		else {
			$cash_collected = '0.00';
			$total_fare = '0';
			$driver_payout = '0';
		}
		$duration_hour = '0';	
		$duration_min = '0';
		
		//hours and min translation process
		$getDurations = (string) $trip_details['duration']; 
		//first hr and min from table
		if(!empty($getDurations)) {
			
			$parts = explode(' ', $getDurations); //string convert to array

			if (count($parts) == 2) {
				//checking process hour or minutes
				if ($parts[1] == 'hr') {
			  		$duration_hour = $parts[0];
				}
				if ($parts[1] == 'min') {
			  		$duration_min = $parts[0];
				}
			}
			else{
				//checking process hour or minutes
				if ($parts[1] == 'hr') {
			  		$duration_hour= $parts[0];
				}
				if ($parts[1] == 'min') {
			  		$duration_min = $parts[0];
				}
				if ($parts[3] == 'min') {
			  		$duration_min = $parts[2];	
				}
			}
		}
		$driver_payout = $driver_payout  ;
		$driver_payout = number_format((float)$driver_payout, 2, '.', '');
		$trip = [
			'order_id' 			=> $trip_details['order_id'],
			'total_fare' 		=> (string) number_format((float) $total_fare,2),
			'status' 			=> $trip_details['status'],
			'vehicle_name' 		=> $vehicle_type,
			'map_image' 		=> $trip_details['trip_path'],
			'trip_date' 		=> trans('user_api_language.weekday.'.date('l', strtotime($date))).date('d/m/Y h:i', strtotime($date)).trans('user_api_language.clock.'.date('a', strtotime($date))),
			'pickup_latitude'	=> $trip_details['pickup_latitude'],
			'pickup_longitude'	=> $trip_details['pickup_longitude'],
			'pickup_location' 	=> $trip_details['pickup_location'],
			'drop_location' 	=> $trip_details['drop_location'],
			'drop_latitude' 	=> $trip_details['drop_latitude'],
			'drop_longitude' 	=> $trip_details['drop_longitude'],
			'duration_hour' 	=> $duration_hour,
			'duration_min' 		=> $duration_min,
			'distance' 			=> number_format($trip_details['drop_distance'], 1),
			'pickup_fare' 		=> $pickup_fare,
			'drop_fare' 		=> $drop_fare,
			'driver_payout' 	=> (string) $driver_payout,
			'trip_amount' 		=>	$trip_details['order']['total_amount'] ,
			'delivery_fee' 		=> $trip_details['order']['delivery_fee'],
			'owe_amount' 		=> $owe_amount,
			'admin_payout' 		=> (string) numberFormat($driver_commision_fee),
			'distance_fare' 	=> $distance_fare,
			'cash_collected' 	=> (string) $cash_collected,
			'driver_penality' 	=> (string) $trip_details['order']['driver_penality'],
			'applied_penality' 	=> (string) $trip_details['order']['app_driver_penality'],
			'cancel_payout' 	=> (string) numberFormat($cancel_payout),
			'applied_owe' 		=> (string) $trip_details['order']['applied_owe'],
			'notes' 			=> (string) $trip_details['order']['driver_notes'],
			'tips'				=> $trip_details['order']['tips'],
		];

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('driver_api_language.payout.successfully'),
			'trip_details' => $trip,
		]);
	}

	public function static_map($order_id = '') {

		$order = Order::find($order_id);

		$user_id = get_store_user_id($order->store_id);

		$res_address = get_store_address($user_id);

		$user_address = get_user_address($order->user_id);

		// $origin = "45.291002,-0.868131";
		// $destination = "44.683159,-0.405704";

		$origin = $res_address->latitude . ',' . $res_address->longitude;
		$destination = $user_address->latitude . ',' . $user_address->longitude;

		$map_url = getStaticGmapURLForDirection($origin, $destination);

		// Trip Map upload //

		if ($map_url) {

			$directory = storage_path('app/public/images/map_image');

			if (!is_dir($directory = storage_path('app/public/images/map_image'))) {
				mkdir($directory, 0755, true);
			}

			$time = time();
			$imageName = 'map_' . $time . '.PNG';
			$imagePath = $directory . '/' . $imageName;
			file_put_contents($imagePath, file_get_contents($map_url));

			$this->fileSave('map_image', $order_id, $imageName, '1');

		}

	}

	function getStartAndEndDate($week, $year) {

		$dateTime = new DateTime();
		$dateTime->setISODate($year, $week);
		$start_date = $dateTime->format('Y-m-d');
		$dateTime->modify('+6 days');
		$end_date = $dateTime->format('Y-m-d');
		return array($start_date, $end_date);

	}

	public function country_list(Request $request)
	{

		$country_data = Country::select(
		'id as country_id',
		'name as country_name',
		'code as country_code'	
		)->get();

		$country_list = $country_data->map(function($data){
            return [
                'country_id'    => $data->country_id,
                'country_name'  => $data->country_name,
                'country_code'  => $data->country_code,
           		 ];
	        });

		return response()->json([
			'status_code' => '1',
			'status_message' => __('user_api_language.invalid_request'),
			'country_list' => $country_list,
			]);
	}

	public function getPayoutPreference(Request $request)
	{
		$request = request();
		$driver = Driver::authUser()->first();
		$payout_type = site_setting('payout_methods');
		if($request->filled('type')) {
			if(!in_array($request->type, ['default','delete','2'])) {
                return response()->json([
                    'status_code' => '1',
                    'status_message' => __('user_api_language.invalid_request'),
                ]);
            }

            if($request->type == 'delete') {
            	$payout = PayoutPreference::find($request->payout_id);
            	if($payout->default == 'yes') {
		            return response()->json([
                    'status_code' => '1',
                    'status_message' => __('driver_api_language.payout.payout_default'),
                	]);
		        }
		        $payout->delete();
            }

            if($request->type == 'default') {
               $payout = PayoutPreference::find($request->payout_id);
               if($payout->default == 'yes') {
               		 return response()->json([
                    'status_code' => '1',
                    'status_message' => __('driver_api_language.payout.payout_already_defaulted'),
                	]);
		        }
		        PayoutPreference::where('user_id',$driver->user_id)->update(['default'=>'no']);
               $payout->default = 'yes';
               $payout->save();
            }  
		}
		$payout_data = array();
		$payout_methods_arrr = explode(',',$payout_type);
        foreach ($payout_methods_arrr as $method) {
        	$payout_credentials = PayoutPreference::where('user_id',$driver->user_id)->where('payout_method',$method)->orderBy('id', 'DESC')->first();
        	$data = array(
                'address1'      => $payout_credentials->address1 ?? '',
                'address2'      => $payout_credentials->address2 ?? '',
                'city'          => $payout_credentials->city ?? '',
                'state'         => $payout_credentials->state ?? '',
                'country'       => $payout_credentials->country ?? '',
                'postal_code'   => $payout_credentials->postal_code ?? '',
                'paypal_email'  => $payout_credentials->paypal_email ?? '',
                'currency_code' => $payout_credentials->currency_code ?? '',
                'routing_number'=> $payout_credentials->routing_number ?? '',
                'account_number'=> $payout_credentials->account_number ?? '',
                'holder_name'   => $payout_credentials->holder_name ?? '',
                'bank_name'     => $payout_credentials->bank_name ?? '',
                'branch_name'   => $payout_credentials->branch_name ?? '',
                'branch_code'   => $payout_credentials->branch_code ?? '',
                'bank_location' => $payout_credentials->bank_location ?? '',
                'phone_number'	=> $payout_credentials->phone_number ?? '',
                'document' =>  $payout_credentials->document_image ?? '',
                'additional_document' => $payout_credentials->additional_document_image ?? '',
                'ssn_last_4' =>$payout_credentials->ssn_last_4 ??  '', 
            );
            $payout_method = array(
                'id' => optional($payout_credentials)->id ?? 0,
                'key' => $method,
                'is_default' => optional($payout_credentials)->default == 'yes',
                'value' => \Lang::get('api_messages.'.strtolower($method)),
                'value_lang' => \Lang::get('driver_api_language.gofereats.add_your'.strtolower($method).'data'),
                "icon"          => asset("images/icon/".strtolower($method).".png"),
                'payout_data' => $data,
            );
            $payout_data[] = $payout_method;	
        }
        return response()->json([
            'status_code'   => '1',
            'status_message'=> __('driver_api_language.payout.payout_listed'),
            'payout_methods'=> $payout_data,
        ]);
	}
	
	public function driver_accept_order() {

		$request = request();
		$driver = Driver::authUser()->first();		
		
		$rules = [
			'group_id' => 'required',			
		];

		$validator = Validator::make(request()->all(), $rules);
		if ($validator->fails()) {
			return response()->json(
				[
					'status_message' => $validator->messages()->first(),
					'status_code' => '0',
				]
			);
		}
		// $driver_request = DriverRequest::where('driver_id', $driver->id)->where('group_id',$request->group_id)->where('status',1)->select(DB::raw('*, format( ( 6371 * acos( cos( radians( drop_latitude ) ) * cos( radians( pickup_latitude ) ) * cos( radians( pickup_longitude ) - radians( drop_longitude ) ) + sin( radians( drop_latitude ) ) * sin( radians( pickup_latitude ) ) ) ) , 2)  as distance') )->orderBy('distance','ASC')->get();	
		
		$driver_request = DriverRequest::where('driver_id', $driver->id)->where('group_id',$request->group_id)->where('status',1)->orderBy('distance','ASC')->get();	
			$driver_request_data=[];
			foreach ($driver_request as $key => $value) {					
			if($value->trip_status==0 || $value->trip_status==1 || $value->trip_status==3 || $value->trip_status==4)
				$driver_request_data[]=$value;
			}
		if($driver_request_data){
		return response()->json(
			[
				'status_message' => __('driver_api_language.accepted_order'),
				'status_code' => '1',
				'driver_accepted_orders' => $driver_request_data,				
			]
		);			
		}else{
			return response()->json(
			[
				'status_message' => __('driver_api_language.no_orders_found'),
				'status_code' => '',
				'driver_accepted_orders' =>[] ,
				
			]
		);			
		}
	}

	public function driver_received_order() {
		
		$rules = [
			'group_id' => 'required',			
		];

		$validator = Validator::make(request()->all(), $rules);
		if ($validator->fails()) {
			return response()->json(
				[
					'status_message' => $validator->messages()->first(),
					'status_code' => '0',
				]
			);
		}

		$request = request();
		$driver = Driver::authUser()->first();		
		$driver_request = DriverRequest::where('driver_id', $driver->id)->where('group_id',$request->group_id)->get();
		$remain_request = DriverRequest::where('driver_id', $driver->id)->where('group_id',$request->group_id)->where('status',1)->get();

		// $remain_request=$remain_request->reject(function($value){
		// 	if($value->trip_status==0 || $value->trip_status==1 || $value->trip_status==3 || $value->trip_status==4)
		// 	return false;
		// 	else
		// 	return true;
		// });
		
		$remain_request_data=[];
		$accepted_status='true';
			foreach ($remain_request as $key => $value) {
				if($value->is_picked=='0'){
				$accepted_status='false';
				}	
			if($value->trip_status==0 || $value->trip_status==1 || $value->trip_status==3 || $value->trip_status==4)
				$remain_request_data[]=$value;
			}

		// $remain_request = $remain_request->where('trip_status','!=',NULL)->whereIn('trip_status',[0,1,3,4]);
		if(count($remain_request_data)>0){
		$order=Order::where('id',$driver_request[0]['order_id'])->first();
		$store=Store::where('id',$order->store_id)->first();
		return response()->json(
			[
				'status_code' => '1',
				'status_message' => __('driver_api_language.request_recived_successfully'),
				'store_name'=>$store->name,
				'total_order'=>count($driver_request),
				'remaining_order_count'=>count($remain_request_data),
				'order_details' => $remain_request_data,
				'accepted_status' => $accepted_status,

			]
		);			
		}else{
			return response()->json(
			[
				'status_message' =>__('driver_api_language.no_orders_found'),
				'status_code' => '1',
				'order_details' =>[] ,
				'remaining_order' =>[] ,
				'accepted_status' =>'false' ,
			]
		);			
		}
	}

	public function accept_request() {		
		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'request_id' => 'required|exists:request,id,driver_id,' . $driver->id,
			'order_id' => 'required|exists:order,id',
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

		$order = Order::where('id', $request->order_id)->first();
		$driver_request = DriverRequest::where('id', $request->request_id)->first();
		if (($driver_request->status_text != "pending") || ($order->driver_id && $order->driver)) {
			return response()->json(
				[
					'status_message' => trans('driver_api_language.driver.timed_out'),
					'status_code' => '0',
				]
			);
		}

		$order_list=explode(',', $driver_request->orders_list);
		$driver_id=$driver_request->driver_id;
		
		foreach($order_list as $key => $value) {		
		$order_data = Order::where('id', $value)->first();
		$driver_request_update=DriverRequest::where('driver_id',$driver_id)->where('order_id',$value)->where('group_id',$driver_request->group_id)->first();
		$driver_request_update->status = $driver_request->statusArray['accepted'];
		$driver_request_update->save();		
		$order_data->driver_accepted($driver_request_update);
		}
		
		$update_status = Driver::find($driver->id);
		$update_status->status = 2;
		$update_status->save();	
		
		$this->static_map($order->id);

			return response()->json(
			[
				'status_message' => trans('driver_api_language.driver.request_accepted_successfully'),
				'status_code' => '1',
				'order_details' => [
					'order_id' => $order->id,
					'mobile_number' => $order->user->mobile_number,
					'eater_thumb_image' => $order->user->user_image_url,
					'rating_value' => '0',
					'vehicle_type' => $driver->vehicle_type_details->name,
					'pickup_location' => $driver_request->pickup_location,
					'pickup_latitude' => $driver_request->pickup_latitude,
					'pickup_longitude' => $driver_request->pickup_longitude,
					'drop_location' => $driver_request->drop_location,
					'drop_latitude' => $driver_request->drop_latitude,
					'drop_longitude' => $driver_request->drop_longitude,
					'group_id' => $driver_request->group_id,
					'request_id' => $driver_request->id,
				],
			]
		);
		
		
	}

}
