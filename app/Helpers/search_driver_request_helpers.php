<?php

use App\Models\Driver;
use App\Models\DriverRequest;
use App\Models\OrderDelivery;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
	
if (!function_exists("searchDrivers")) {

	function searchDrivers($order_id)
	{
		$order = Order::where('id', $order_id)->first();
		$store = $order->store;
		list('latitude' => $pickup_latitude, 'longitude' => $pickup_longitude, 'address' => $pickup_location) = collect($order->store->user_address)->only(['latitude', 'longitude', 'address'])->toArray();
		
		list('latitude' => $drop_latitude, 'longitude' => $drop_longitude, 'address' => $drop_location) = collect($order->user->user_address)->only(['latitude', 'longitude', 'address'])->toArray();

		$group_id = $order->id . time();

		// $this->search_and_send_request_to_driver($order->id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
		// SearchRequestDriver::dispatch($order->id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
		searchRequestDriver($order->id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
	}
}

if (!function_exists("searchRequestDriver")) {

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


	function searchRequestDriver($order_id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location) {
	
		clearPending(); 
		
		$order = Order::where('id', $order_id)->first();
		$driver_request = new DriverRequest;
		$driver_search_radius = site_setting('driver_km');
		$sleep_time = 15;
		$active = true;

		if ($order->driver_id && $order->driver) {
			\Log::info("Request already accepted : " . $order->id);
			return;
		}
		$preparation_time_insec = site_setting('preperation_time_interval') ;
		$current_order_remaining_seconds = $order->remaining_seconds+($preparation_time_insec*60);
		// set current order remaining seconds as 0
		$current_order_remaining_seconds = $current_order_remaining_seconds > 0?$current_order_remaining_seconds:0;

		$current_date = Carbon::parse()->format('Y-m-d H:i:s');
		$pending_total_seconds = getSecondsFromTime(site_setting('store_new_order_expiry_time'));
		if(site_setting('multiple_delivery')=='Yes'){
			$array_con=explode(',',$order_id);		
			$radius=site_setting('delivery_radius');
			$order_triger_latitude=$order->user->user_address->latitude;
			$order_triger_longitude=$order->user->user_address->longitude;
			$compine_order = Order::select('*',DB::raw('( 6371 * acos( cos( radians('.$order_triger_latitude.') ) * cos( radians( drop_latitude ) ) * cos( radians( drop_longitude ) - radians('.$order_triger_longitude.') ) + sin( radians('.$order_triger_latitude.') ) * sin( radians( drop_latitude ) ) ) ) as distance'))->with('user_address')->where('store_id',$order->store_id)->whereNotIn('id',$array_con)->where(function($query) {	
			$query->whereNull('schedule_time')->orWhere('schedule_time','<=', date('Y-m-d H:i:s'));
			})->where('status',$order->statusArray['accepted'])->where(function($query) use($order_triger_latitude, $order_triger_longitude, $radius) {
			 $query->whereRaw('( 6371 * acos( cos( radians('.$order_triger_latitude.') ) * cos( radians( drop_latitude ) ) * cos( radians( drop_longitude ) - radians('.$order_triger_longitude.') ) + sin( radians('.$order_triger_latitude.') ) * sin( radians( drop_latitude ) ) ) ) <= '.$radius);
            })->where(function($query) use ($pending_total_seconds,$current_order_remaining_seconds) { 
            			$current_date = Carbon::parse()->format('Y-m-d H:i:s');
            	//get current order remaining_seconds and add Preparation Time Interval minute	
            	$query->whereRaw(DB::raw('UNIX_TIMESTAMP(DATE_ADD(accepted_at, INTERVAL TIME_TO_SEC(est_preparation_time) second))- UNIX_TIMESTAMP("'.$current_date.'") <= '.$current_order_remaining_seconds.''));
            })->where('delivery_type','delivery')->groupBy('id')->take(site_setting('number_of_delivery')-1)->orderBy('distance','ASC')->get();
			if($compine_order->count()){	
				$compine_order_interval = $compine_order;	
				$compine_order_interval->add($order);
			}else{
				$compine_order_interval = Order::where('id',$order->id)->get();
			}
			$orders_list=$compine_order_interval->pluck('id')->toArray();
			$current_order=$compine_order_interval;			
			$orders_list_array=$orders_list;
			$orders_list=implode(',', $orders_list);
			$drivers = Driver::search($pickup_latitude, $pickup_longitude, $driver_search_radius, $group_id)->where('status',1)->get();
		}else{
			$orders_list=$order_id;					
			$orders_list_array = explode(',',$order_id);
			$current_order = Order::where('id',$order->id)->get();
			$drivers = Driver::search($pickup_latitude, $pickup_longitude, $driver_search_radius, $group_id)->where('status',1)->get();			
		}	
        logger("All combine order list ".json_encode($orders_list));
		if ($drivers->count() == 0) {
			\Log::info("Sorry, No drivers found. : " . $order->id);
			$support_mobile = site_setting('site_support_phone');
			$store = $order->store->user;
			$push_notification_title = trans('api_messages.orders.no_drivers_found') . $order->id;
			$push_notification_data = [
				'type' => 'no_drivers_found',
				'order_id' => $order->id,
				'support_mobile' => $support_mobile,
			];
			push_notification($store->device_type, $push_notification_title, $push_notification_data, $store->type, $store->device_id);
			return false;
		}

		$firbase = resolve("App\Services\FirebaseService");
		foreach ($drivers as $key => $value) {
			# code...
			logger("list of driver_id ".$value->id."list of user_id ".$value->user_id.' Distance '.@$value->distance);
		}
		$nearest_driver = $drivers->first();
		
		$request_already = DriverRequest::where('driver_id', $nearest_driver->id)->where('group_id', $group_id)->count();
				
		if ($request_already < 1) {
			$last_second = DriverRequest::where('driver_id', $nearest_driver->id)->where('status', '0')->get()->count();
			if ($last_second<1) {
				foreach ($current_order as $key => $value) {
					$driver_request = new DriverRequest;
					$driver_request->order_id = $value->id;
					$driver_request->group_id = $group_id;
					$driver_request->driver_id = $nearest_driver->id;
					$driver_request->pickup_latitude = $pickup_latitude;
					$driver_request->pickup_longitude = $pickup_longitude;
					$driver_request->pickup_location = $pickup_location;
					$driver_request->drop_latitude 	=$value->drop_latitude;
					$driver_request->drop_longitude =$value->drop_longitude;
					$driver_request->drop_location =$value->drop_location;				
					$driver_request->orders_list =$orders_list;
					$driver_request->status = 0;
					$driver_request->distance = $value->order_delivery->est_distance;		
					$driver_request->save();	
				}
				$push_notification_title = "New order request ";
				$driving_distance = get_driving_distance($nearest_driver->latitude, $pickup_latitude, $nearest_driver->longitude, $pickup_longitude);
				if ($driving_distance['status'] == 'fail') {
					return response()->json(
						[
							'status' => '0',
							'messages' => $driving_distance['msg'],
							'status_message' => 'Some technical issue contact admin',
						]);
				}

				$get_near_time = round(floor(round($driving_distance['time'] / 60)));		
				$get_near_time	= ($get_near_time == 0)	? '1' : $get_near_time;
				$driver_request_ids= DriverRequest::whereIn('order_id',$orders_list_array)->pluck('id')->toArray();
				$driver_request_ids=implode(',', $driver_request_ids);

				$driver_request=DriverRequest::where('group_id',$group_id)->where('order_id',$order_id)->where('status',0)->first();

				$multiple_delivery = 	site_setting('multiple_delivery');	
				$push_notification_data = [
					'type' => 'order_request',
					'request_id' => $driver_request->id,
					'request_data' => [
						'request_id' => $driver_request->id,
						'order_id' => $driver_request->order_id,
						'pickup_location' => $pickup_location,
						'min_time' => $get_near_time,
						'pickup_latitude' => $pickup_latitude,
						'pickup_longitude' => $pickup_longitude,
						'store' => $order->store->name,
						'total_orders' => count($current_order),
						'group_id' => $group_id,
						'multiple_delivery' =>$multiple_delivery,
					],
				];
				$last_driver = 	$nearest_driver->user->id;
				$requestData = json_encode(["custom" => $push_notification_data]);
				$firbase->updateReference("trip_request/".$last_driver,$requestData);
				logger("Push notification sent to this Request Driver ".$last_driver );
				push_notification($nearest_driver->user->device_type, $push_notification_title, $push_notification_data, $nearest_driver->user->type, $nearest_driver->user->device_id, true);
			} else {
				searchRequestDriver($order_id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);

			}

		}

		$nexttick = time() + $sleep_time;
		
		while ($active) {
			
			if (time() >= $nexttick) {
				if ($driver_request) {
					$driver_request_count = DriverRequest::where('group_id', $group_id)->where('driver_id', $nearest_driver->id)->where('status', 0);
					if ($driver_request_count->count() > 0) {
						logger("driver request+9586".$driver_request->status_text);
						if ($driver_request->status_text == 'pending') {
							$driver_request_count->update(['status'=>2]);
							searchRequestDriver($order_id, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
						}
					}
				}

				$drivers = Driver::search($pickup_latitude, $pickup_longitude, $driver_search_radius, $group_id)->get();
				if ($drivers->count() == 0) {
					\Log::info("stop : " . $order->id);
					$active = false;
				}

			}
			$driver_accept = DriverRequest::where('group_id', $group_id)->where('status', '1')->get();
			if (count($driver_accept)) {
				foreach ($current_order as $key => $value) {
					$order = Order::find($value->id);
					$order->status = $order->statusArray['delivery'];
					$order->delivery_at = date('Y-m-d H:i:s');
					$order->save();
					$user = $order->user;
					$push_notification_title = trans('api_messages.orders.your_food_preparation_done') . $value->id;
					$push_notification_data = [
						'type' => 'order_delivery_started',
						'order_id' => $value->id,
					];
					
					push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
				}
				$active = false;
			}
		}
	}

	function clearPending()
	{
		$request = DriverRequest::where('created_at', '<', Carbon::now()->subMinutes(2)->toDateTimeString())->where('status','0')->get();

        if($request) {
			foreach($request as $request_val) {
                DriverRequest::where('id', $request_val->id)->update(['status' => '2']);
			}

	    }
	}

	function distance_km($lat1, $lon1, $lat2, $lon2) { 
		$pi80 = M_PI / 180; 
		$lat1 *= $pi80; 
		$lon1 *= $pi80; 
		$lat2 *= $pi80; 
		$lon2 *= $pi80; 
		$r = 6372.797; // mean radius of Earth in km 
		$dlat = $lat2 - $lat1; 
		$dlon = $lon2 - $lon1; 
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2); 
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
		$km = $r * $c; 		
		return $km; 
	}


}
