<?php

/**
 * Place order Trait
 *
 * @package     Gofereats
 * @subpackage  Place order Trait

 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */


namespace App\Traits;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Penality;
use App\Models\PenalityDetails;
use App\Models\Store;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Models\Wallet;
use Carbon\Carbon;
use Session;
use App\Models\OrderDelivery;

trait PlaceOrder
{
	public function placeOrder($request, $user_details)
	{
		$address = UserAddress::where('user_id', $user_details->id)->default()->first();
		$address->order_type = $request->order_type;
		$address->delivery_time = $request->delivery_time;
		$address->save();
		//promo apply if user promo add for this Order
		$update_promo = promo_calculation($request->delivery_type);

		$order_details = Order::getAllRelation()->find($request->order_id);
		
		$tips = $request->tips ?? $order_details->tips;
		$tips = $order_details->delivery_type == 'delivery' ? $tips  : 0;
		$service_type  = Store::where('id',$order_details->store_id)->first()->value('service_type');
		$order_details->service_type = $service_type;
		$order_details->schedule_status = $request->order_type;
		$order_details->schedule_time = $request->delivery_time;
		$order_details->tips = $request->tips ?? $order_details->tips;
		$order_details->delivery_type = $request->delivery_type;
		$order_details->promo_amount = 	$update_promo;
		if($order_details->delivery_type == 'takeaway')
		{
			$order_details->delivery_fee = 0;
		}
		$order_details->save();
		$check_address = check_location($request->order_id);
		if ($check_address == 0) {
			return response()->json(['status_message' => trans('api_messages.orders.store_location_unavailable'), 'status_code' => '0']);
		}
		$unavailable = Store::where('id', $order_details->store_id)->status()->userstatus()->first();
		
		if (!$unavailable) {
			return response()->json(['status_message' =>trans('api_messages.orders.store_unavailable'), 'status_code' => '0']);
		}

		$check_menu = $this->checkMenuStatus($order_details);

		if ($check_menu['status'] == 'unavailable') {
			return response()->json([
				'status_code' => '0',
				'status_message'=> $check_menu['status_message'],
				'not_available'	=> [
					'menu'			=> $check_menu['menu'],
					'modifier'		=> $check_menu['modifier'],
					'modifier_item'	=> $check_menu['modifier_item'],
				],
			]);
		}

		$delivery=OrderDelivery::where('order_id',$request->order_id)->first();
		// dd($delivery);
		if($delivery){
			if (site_setting('delivery_fee_type') == 0) {
				// $order_details->delivery_fee = site_setting('delivery_fee');	
				$delivery_fee = site_setting('delivery_fee');
				$delivery->fee_type = 0;
				$delivery->total_fare = $delivery_fee ;
			}
			else {

				$store_address = $order_details->store->user_address;
				$pickup_fare = site_setting('pickup_fare');
				$drop_fare = site_setting('drop_fare');
				// $distance_fare = 
				
				$delivery->fee_type = 1;
				// $delivery->pickup_fare = $pickup_fare;
				// $delivery->drop_fare = $drop_fare;
				// $delivery->distance_fare = $distance_fare;
				// $delivery->total_fare = $ ;
			}
			$order_details->save();
			$delivery->drop_latitude = $address->latitude;
			$delivery->drop_longitude = $address->longitude;
			$delivery->drop_location = $address->address;
			$delivery->tips = $tips;
			$delivery->save();
		}
		
		//promo apply if user promo add for this Order
		$promo_codes = UsersPromoCode::whereUserId($user_details->id)->where('order_id', 0)->with('promo_code_many')->whereHas('promo_code_many')->orderBy('created_at', 'asc')->where('promo_default',1)->first();
		if ($promo_codes) {
			UsersPromoCode::whereId($promo_codes->id)->update(['order_id' => $request->order_id]);
		}

		// Wallet Amount Apply
		$owe_amount = 0;
		$store_owe_amount = 0; 
		$owe_amount_distance = 0;
		$is_wallet = $request->isWallet;
		$use_wallet_amount = use_wallet_amount($request->order_id, $is_wallet,$tips);
		$amount = $use_wallet_amount['amount'] ;
		$remaining_wallet = $use_wallet_amount['remaining_wallet_amount'];
		$currency_code = $order_details->currency_code;
		$payment_type = $request->payment_method;
		if ( $payment_type != 0 ) {
			if ($amount != 0) {
				$stripe_payment = resolve('App\Repositories\StripePayment');
				if($payment_type == 1){
					if($request->filled('payment_intent_id')) {
						$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
					}
					else {
						$user_payment_method = UserPaymentMethod::where('user_id', $user_details->id)->first();
						$purchaseData = array(
							"amount" 		=> $amount * 100,
							'currency' 		=> $currency_code,
							'description' 	=> 'Payment for Order : '.$request->order_id,
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
				}else{
					if(isApiRequest()) {
			            $user = \JWTAuth::parseToken()->authenticate();
			            $currency_code = $user->currency_code->code;
			        }else{
			        	$currency_code = session::get('currency');
			        }
			        
					$converted_amount = currencyConvert($currency_code,PAYPAL_CURRENCY_CODE,floatval($amount));
					
					$payment_result = $stripe_payment->PaypalPayment($converted_amount,$request->pay_key);
					if(!$payment_result->status) {
						return response()->json([
					        'status_code' => '0',
					        'status_message' => $payment_result->status_message,
					    ]);
					}
				}

				$payment = new Payment;
				$payment->user_id = $user_details->id;
				$payment->order_id = $request->order_id;
				$payment->transaction_id = $payment_result->transaction_id;
				$payment->type = 0;
				$payment->amount = $amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();
			}
			else {
				$payment = new Payment;
				$payment->user_id = $user_details->id;
				$payment->order_id = $request->order_id;
				$payment->transaction_id = '1';
				$payment->type = 0;
				$payment->amount = $amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();
			}
		}
		else {
			$store_to_admin = site_setting('store_commision_fee');
			$driver_to_admin = site_setting('driver_commision_fee');
			if($order_details->delivery_type == 'takeaway')
			{				
				$store_owe_amount = $order_details->booking_fee + $order_details->store_commision_fee;
				if ($store_owe_amount < 0) {
					$store_owe_amount = 0;
				}
			}	
			else
			{
				$payment = new Payment;
				$payment->user_id = $user_details->id;
				$payment->order_id = $request->order_id;
				$payment->transaction_id = '0';
				$payment->type = 0;
				$payment->amount = $amount;
				$payment->status = 0;
				$payment->currency_code = $currency_code;
				$payment->save();

				// owe amount
				$driver_to_admin = site_setting('driver_commision_fee');
				$pay_to_admin = ($order_details->delivery_fee / 100) * $driver_to_admin;
				$owe_amount = $amount - (($order_details->delivery_fee + $request->tips ) - $pay_to_admin);
				if ($owe_amount < 0) {
					$owe_amount = 0;
				}
				if(site_setting('delivery_fee_type')=='1'){
				$distance_fare=$order_details->order_delivery->est_distance * $order_details->order_delivery->distance_fare;
				$pay_to_admin_distance = ($distance_fare / 100) * $driver_to_admin;
				// owe amount =     totalamount- |     driverpayout                                         |
				$owe_amount_distance = $amount - (($distance_fare + $request->tips) - $pay_to_admin_distance);
				}else{
				$owe_amount_distance = $owe_amount;								
				}
			}
		}

		$user_address = $user_details->user_address;
		$store_address = $order_details->store->user_address;
		$driving_distance = get_driving_distance($user_address->latitude, $store_address->latitude, $user_address->longitude, $store_address->longitude);

		if ($driving_distance['status'] == 'fail') {
			return response()->json([
				'status_code' 	=> '0',
				'messages' 		=> $driving_distance['msg'],
				'status_message'=> 'Some technical issue contact admin',
			]);
		}

		$order = Order::find($request->order_id);
		$order->status = $order->statusArray['pending'];
		$order->payment_type = $payment_type;
		$order->total_amount = $amount;
		$order->owe_amount = $owe_amount;
		$order->est_preparation_time = $order->store->getStorePreparationTime(Carbon::now());
		if($order->delivery_type == 'takeaway'){
			$order->store_owe_amount = @$store_owe_amount;
		}
		$order->est_travel_time = gmdate("H:i:s", $driving_distance['time']);
		$order->owe_amount_distance = ($owe_amount_distance<0)?0:$owe_amount_distance;
		$order->drop_location = $address->address;
		$order->drop_latitude = $address->latitude;
		$order->drop_longitude = $address->longitude;
		//Estimation Delivery time
		$time = getTimeFromSeconds($driving_distance['time']);
		$secs = strtotime($order->est_preparation_time) - strtotime("00:00:00");
		$result = date("H:i:s", strtotime($time) + $secs);
		if ($order->schedule_status == 0) {
			if($order->delivery_type == 'delivery')
				$secs = strtotime($result) - strtotime("00:00:00");
			else 
				$secs = $secs;
			$est_time = date("H:i:s", time() + $secs);
		}
		else {
			$data['total_time'] = date("H:i:s", strtotime($order->est_preparation_time) + $secs);
			$est_time = date("H:i:s", strtotime($order->schedule_time));
			if($order->delivery_type == 'delivery'){
				$secs = strtotime($result) - strtotime("00:00:00");
				$est_time = date("H:i:s", strtotime($order->schedule_time) - $secs);
			}			
		}
		$order->est_delivery_time = $est_time;
		$order->notes = $request->notes;
		$order->created_at = date('Y-m-d H:i:s');
		$order->save();
		
		if ($request->isWallet == 1) {
			$wallet = Wallet::where('user_id', $user_details->id)->first();
			if ($wallet) {
				$wallet->amount = $remaining_wallet;
				$wallet->save();
			}
		}
		push_notification_for_store($order);
		$user_penality = Penality::where('user_id', $order->user_id)->first();
		if ($user_penality) {
			$penality_details = PenalityDetails::where('order_id', $order->id)->first();
			$previous = 0;
			if ($penality_details) {
				$previous = $penality_details->previous_user_penality;
			}
			$user_penality->remaining_amount = 0;
			$user_penality->paid_amount = $user_penality->paid_amount + $previous;
			$user_penality->save();
		}
		//clear_schedule data from session
		schedule_data_update('clear_schedule');
		$order = Order::find($request->order_id);
		
		return response()->json([
			'status_code' => '1',
			'status_message' => trans('user_api_message.orders.successfully'),
			'order_details' => $order,
		]);
	}

	public function wallet_amount($amount, $user_id, $currency_code) {
		$wallet = Wallet::where('user_id', $user_id)->first();
		if ($wallet) {
			$wallet->amount = $wallet->amount + currencyConvert($currency_code,$wallet->getRawOriginal('currency_code'),$amount);
			$wallet->save();
		}
		return;
	}
	
	public function checkMenuStatus($order_details)
	{
		$return_data = ['status' => 'Available','status_message' => '','menu' => [],'modifier' => [],'modifier_item' => []];
		$order_items = $order_details->order_item;
		$order_items->each(function($order_item) use(&$return_data) {
			if($order_item->menu_item == '') {
				$return_data['status'] = 'unavailable';
				array_push($return_data['menu'], ['id' => $order_item->menu_item_id,'name' => $order_item->menu_name]);
			}
			else {
				$order_item_modifiers = $order_item->order_item_modifier;
				$order_item_modifiers->each(function($order_item_modifier) use(&$return_data) {
					if($order_item_modifier->menu_item_modifier == '') {
						$return_data['status'] = 'unavailable';
						array_push($return_data['modifier'], ['id' => $order_item_modifier->modifier_id, 'name' => $order_item_modifier->modifier_name]);
					}
					else {
						$order_item_modifier_items = $order_item_modifier->order_item_modifier_item;
						$order_item_modifier_items->each(function($order_item_modifier_item) use(&$return_data) {
							if($order_item_modifier_item->menu_item_modifier_item == '') {
								$return_data['status'] = 'unavailable';
								array_push($return_data['modifier_item'], ['id' => $order_item_modifier_item->menu_item_modifier_item_id,'name' => $order_item_modifier_item->modifier_item_name]);
							}
						});
					}
				});
			}
		});

		if($return_data['status'] == 'unavailable') {
			if(count($return_data['menu']) > 0 || count($return_data['modifier']) > 0 || count($return_data['modifier_item']) > 0) {
				$return_data['status_message'] = trans('messages.modifiers.item_not_available');
			}
		}
		return $return_data;
	}
}
