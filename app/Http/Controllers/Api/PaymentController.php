<?php

/**
 * Payment Controller
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
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\PlaceOrder;
use App\Traits\FileProcessing;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use Stripe;
use Validator;


class PaymentController extends Controller
{
	use PlaceOrder,FileProcessing;

	/**
	 * User Place order and payment
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */

	public function place_order(Request $request) {
		$user_details = JWTAuth::parseToken()->authenticate();
		$rules = array(
			'delivery_type' => 'required',
		);

		$messages = array(
            'required' => ':attribute '.trans('user_api_language.register.field_is_required').'', 
        );

		$attributes = array(
			'delivery_type' => trans('user_api_language.orders.delivery_type'),
		);	

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);
		
		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}

		// check payment method exist for this service type
		$order = Order::find($request->order_id);
		$delivery_type = checkStoreDelieryType($order->store_id,$request->delivery_type);
		if($delivery_type == false)
		{
			return response()->json([
				'status_code' => '0',
				'status_message'=> trans('user_api_language.delivery_type_not_support'),
			]);
		}
		$service_type = checkActiveServiceType($order->store_id);
		if(is_null($service_type))
		{
			return response()->json(
				['status_message' =>trans('user_api_language.service_type_inactivate'),
				 'status_code' => '0'
				]);
		}
		$order->delivery_type = $request->delivery_type;
		$order->save();
		$payment_methods = site_setting('payment_methods');
		$payment_methods = explode(',', $payment_methods);
		$paypal = $stripe = $cash = 0;
		// check availability
		if(in_array('Paypal',$payment_methods))
			$paypal = 1;
		if(in_array('Stripe',$payment_methods))
			$stripe = 1;
		if(in_array('Cash',$payment_methods))
			$cash = 1;
		if(($request->payment_method==0 && !$cash) || ($request->payment_method==1 && !$stripe) || ($request->payment_method==2 && !$paypal)) {
			return response()->json([
				'status_code' => '0',
				'status_message'=> 'The payment method doesn\'t support for this store.',
			]);
		}
		$this->static_map_track($request->order_id);
		return $this->PlaceOrder($request, $user_details);
	}


	public function static_map_track($order_id)
	{
		$order = Order::findOrFail($order_id);
		$user_id = get_store_user_id($order->store_id);

		$res_address = get_store_address($user_id);

		$user_address = get_user_address($order->user_id);

		$origin = $res_address->latitude . ',' . $res_address->longitude;
		$destination = $user_address->latitude . ',' . $user_address->longitude;

		$map_url = getStaticGmapURLForDirection($origin, $destination);

		$directory = storage_path('app/public/images/map_image');

		if (!is_dir($directory = storage_path('app/public/images/map_image'))) {
			mkdir($directory, 0755, true);
		}

		$time = time();
		$imageName = 'map_' . $time . '.PNG';
		$imagePath = $directory . '/' . $imageName;
		if ($map_url) {
			file_put_contents($imagePath, file_get_contents($map_url));
			$this->fileSave('map_image', $order_id, $imageName, '1');
		}
	}


	/**
	 * Refund when the store not accept the order
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function cron_refund(Request $request)
	{
		$orders = Order::with('user.user_address')->whereIn('status', ['1'])->get();

		if ($orders->count() > 0) {
			foreach ($orders as $order) {

				date_default_timezone_set($order->user->user_address->default_timezone);

				$before_minutes = Carbon::now()->subMinutes(2)->format('Y-m-d H:i');
				$updated_at = date('Y-m-d H:i', strtotime($order->updated_at));
				if (strtotime($updated_at) <= strtotime($before_minutes)) {
					$this->refundOrder($order->id, '', $order->user_id);
				}
			}
		}
	}

	public function refundOrder($order_id,$status = '', $user_id = '',$cancel_by='')
	{
		if ($status == 'Cancelled') {
			$order = Order::find($order_id);
		}
		else {
			$order = Order::where('id', $order_id)->whereIn('status', ['1'])->first();
		}

		if ($order == '') {
			return response()->json([
				'status_message' => 'invalid order',
				'status_code' => '1',
			]);
		}
		$user = $order->user;
		$wallet_amount = $order->getRawOriginal('wallet_amount');		

		if ($wallet_amount != 0) {
			if($order->payment_type == 0) {
				$this->wallet_amount($wallet_amount, $order->user_id, $order->getRawOriginal('currency_code'));
				$push_notification_title = trans('store_api_language.refund.wallet_amount_refunded') . $order->id;
				$push_notification_data = [
					'type' => 'Amount Refund',
					'order_id' => $order->id,
				];
				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
			}			
		}

		$update_order_details = $order;

		//Revert Penality amount if exists
		$penality_Revert = revertPenality($order->id);

		$cancelled_reason = trans('api_messages.orders.your_order_id').$order->id .trans('api_messages.orders.has_been_cancelled').ucfirst($cancel_by);

		if ($order->payment_type != 0) {
			$stripe_payment = resolve('App\Repositories\StripePayment');
			try {
				$payment = Payment::where('order_id', $order->id)->first();
				$amount = $payment->amount;
				if($payment->transaction_id != '1'){
					if($order->payment_type == 1){
						$refund = $stripe_payment->refundPayment($payment->transaction_id);
					}else{
						$refund = $stripe_payment->RefundPaypal($payment->transaction_id);
					}
					if ($refund->status != 'success') {
						logger('failure-refund '.$refund->status_message);
						return response()->json([
							'status_code' => '1',
							'status_message' => $refund->status_message,
						]);
					}
				}

				$payout = new Payout;
				$payout->amount = $amount;
				$payout->transaction_id = $payment->transaction_id;
				$payout->currency_code = ($payment->payment_type == '1') ? $refund->currency : $payment->currency_code;
				$payout->order_id = $order_id;
				$payout->user_id = $order->user_id;
				$payout->status = 1;
				$payout->save();
				if ($order->status == $order->statusArray['pending']) {
					$update_order_details->status = $order->statusArray['declined'];
				}
				
				/* Refund Notification */
				if ($wallet_amount != 0) 
					$this->wallet_amount($wallet_amount, $order->user_id, $order->getRawOriginal('currency_code'));
				$push_notification_title = trans('store_api_language.refund.amount_refunded') . $order->id;
				$push_notification_data = [
					'type' => 'Amount Refund',
					'order_id' => $order->id,
				];
				
				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
				
				/* Cancel Notification */
				$user = $order->user;
				
				$push_notification_title = ($status == 'Cancelled') ? $cancelled_reason : __('store_api_language.refund.store_not_accepted');
				$push_notification_data = [
					'type' => 'order_cancelled',
					'order_id' => $update_order_details->id,
					'order_data' => [
						'id' => $update_order_details->id,
						'user_name' => $update_order_details->user->name,
						'status_text' => $update_order_details->status_text,
					],
				];

				$update_order_details->declined_at = date('Y-m-d H:i:s');
				$update_order_details->schedule_status = 0;
				$update_order_details->save();

				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
				return response()->json([
					'status_code' => '1',
					'status_message' => trans('store_api_language.refund.refund_successfully'),
					'refund' => $refund,
				]);

			}
			catch (\Exception $e) {
				return response()->json([
					'status_code' => '0',
					'status_message' => $e->getMessage(),
				]);
			}
		}
		
		if ($order->status == $order->statusArray['pending']) {
			$update_order_details->declined_at = date('Y-m-d H:i:s');
			$update_order_details->status = $order->statusArray['declined'];
			$replace_promo = replace_promo($order_id);		
		}

		$update_order_details->schedule_status = 0;
		$update_order_details->save();

		/* Cancel Notification */
		if($cancel_by != "user") {
			$user = $order->user;
			$push_notification_title = ($status == 'Cancelled') ? $cancelled_reason : __('store_api_language.refund.store_not_accept');
			
			$push_notification_data = [
				'type' 		=> 'order_cancelled',
				'order_id' 	=> $update_order_details->id,
				'order_data'=> [
					'id' 			=> $update_order_details->id,
					'user_name'		=> $update_order_details->user->name,
					'status_text'	=> $update_order_details->status_text,
				],
			];
			sleep(1);
			push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('store_api_language.refund.cash_order'),
		]);
	}

	/**
	 * Refund when the store not accept the order
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function refund(Request $request, $status = '', $user_id = '',$cancel_by='')
	{
		$order_id = $request->order_id;

		return $this->refundOrder($order_id,$status, $user_id,$cancel_by);
	}

	/**
	 * Return amount to wallet when the store not accept the order
	if using wallet amount
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */

	public function wallet_amount($amount, $user_id, $currency_code) {
		$wallet = Wallet::where('user_id', $user_id)->first();
		if ($wallet) {
			$wamount=currencyConvert($currency_code,$wallet->getRawOriginal('currency_code'),$amount);
			$wallet->amount = $wallet->getRawOriginal('amount') + $wamount;
			$wallet->save();
		}
		return;
	}
}
