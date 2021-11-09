<?php

/**
 * order Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   order
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App;

class Order extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $guarded = [];

	use CurrencyConversion;

	protected $convert_fields = ['subtotal','offer_amount','delivery_fee','booking_fee','tax','store_commision_fee','driver_commision_fee','owe_amount','store_owe_amount','total_amount','default_amount','tips','promo_amount','wallet_amount'];

	
	protected $table = 'order';
	protected $appends = ['user_location', /*'store_opening_time',*/'user_total', 'store_is_thumbs', 'store_status', 'store_service', 'user_penality', 'store_penality', 'driver_penality', 'user_applied_penality', 'res_applied_penality', 'app_driver_penality'];
	protected $dates = ['declined_at', 'accepted_at', 'cancelled_at', 'delivery_at', 'completed_at'];

	/**
	 * Array of data for status
	 *
	 * @var array
	 */

	public $statusArray = [
		'cart' => 0,
		'pending' => 1,
		'declined' => 2,
		'accepted' => 3,
		'cancelled' => 4,
		'delivery' => 5,
		'completed' => 6,
		'expired' => 7,
		'takeaway' => 8,
	];

	public $userTypeArray = [
		'user' => 0,
		'store' => 1,
		'driver' => 2,
		'admin' => 3,
	];

	/**
	 * To check the order is in cart
	 */
	public function scopeStatus($query, $status = 'cart')
	{
		$status_value = $this->statusArray[$status];
		return $query->where('status', $status_value);
	}

	/**
	 * To check notstatus equal
	 */
	public function scopeNotstatus($query, $status = 'cart')
	{
		$status_value = $this->statusArray[$status];
		return $query->where('status', '!=', $status_value);
	}

	/**
	 * To check the order status
	 */
	public function scopeHistory($query, $status = ['cancelled', 'completed', 'declined']) {
		$array_status = array_map(
			function ($value) {
				return $this->statusArray[$value];
			},
			$status
		);
		return $query->whereIn('status', $array_status);
	}

	/**
	 * To check the upcoming order status
	 */
	public function scopeUpcoming($query, $status = ['accepted', 'delivery', 'pending','takeaway'])
	{
		$array_status = array_map(
			function ($value) {
				return $this->statusArray[$value];
			},
			$status
		);

		return $query->whereIn('status', $array_status);
	}

	public function scopeGetAllRelation($query)
	{
		return $query->with(
			[
				'store' => function ($query) {
					$query->with(['store_time']);
				},
				'order_item' => function ($query) {
					$query->with(
						['menu_item' => function ($query) {
							$query->with(
								['menu_item_main_addon' => function ($query) {
									$query->with('menu_item_sub_addon');
								},
									'review',

								]
							);
						},
						'order_item_modifier' => function($query) {
							$query->with('order_item_modifier_item');
						}]
					);
				},'review']
		);
	}

	// Join with OrderItem table
	public function order_item()
	{
		return $this->hasMany('App\Models\OrderItem', 'order_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}

	// Join with store table
	public function store()
	{
		return $this->belongsTo('App\Models\Store', 'store_id', 'id');
	}

	// Join with store table
	public function driver()
	{
		return $this->belongsTo('App\Models\Driver', 'driver_id', 'id');
	}

	// Join with driver table
	public function order_delivery()
	{
		return $this->belongsTo('App\Models\OrderDelivery', 'id', 'order_id');
	}

	// Join with request table
	public function driver_request()
	{
		return $this->belongsTo('App\Models\DriverRequest', 'id', 'order_id')->status();
	}

	// Join with payment table
	public function payment()
	{
		return $this->belongsTo('App\Models\Payment', 'id', 'order_id');
	}
	// Join with payout table
	public function payout_table() {
		return $this->hasMany('App\Models\Payout', 'order_id', 'id');
	}
	// Join with penality_details table
	public function penality_details() {
		return $this->belongsTo('App\Models\PenalityDetails', 'id', 'order_id');
	}

	// Join with review table
	public function review() {
		return $this->belongsTo('App\Models\Review', 'id', 'order_id');
	}
	// get cancelled_reason_text
	public function getCancelledReasonTextAttribute() {
		$value = $this->belongsTo('App\Models\OrderCancelReason', 'cancelled_reason', 'id')->first();
		if ($value) {
			return $value->name;
		}

	}

	public function getStoreIsThumbsAttribute() {

		$review = Review::where('order_id', $this->attributes['id'])->where('type', 3)->first();

		if ($review) {
			return strval($review->is_thumbs);
		}
		return '';

	}

	public function getTotalSecondsAttribute() {
		if ($this->status_text == "pending") {
			$total_seconds = getSecondsFromTime(site_setting('store_new_order_expiry_time'));
		} elseif ($this->status_text == "accepted") {
			$preparation_time = $this->est_preparation_time;
			$total_seconds = getSecondsFromTime($preparation_time);
		} else {
			return 100;
		}

		return $total_seconds;
	}

	public function getRemainingSecondsAttribute()
	{
		if ($this->status_text != "pending" && $this->status_text != "accepted") {
			return 100;
		}
		$current_date = Carbon::parse();

		if (isset($this->accepted_at)) {
			$updated_time = $this->accepted_at;
		}
		else {
			$updated_time = $this->updated_at;
		}

		$expiry_seconds = $this->total_seconds;

		$expiry_date = (clone $updated_time)->addSeconds($expiry_seconds);
		$remaining_seconds = $current_date->diffInRealSeconds($expiry_date, false);
		if ($remaining_seconds <= 0) {
			if ($this->status_text == "pending") {
				$this->decline_order();
			}
			elseif ($this->status_text == "accepted") {
				// $this->deliver_order();
			}
		}
		return $remaining_seconds;
	}

	public function getUserTotalSecondsAttribute() {

		if ($this->status_text != "pending" && $this->status_text != "accepted" && $this->status_text != "delivery") {
			return 100;
		}

		$total_seconds = getSecondsFromTime(site_setting('store_new_order_expiry_time'));

		$preparation_time = $this->est_preparation_time;
		$total_seconds += getSecondsFromTime($preparation_time);

		$est_travel_time = $this->est_travel_time;
		$total_seconds += getSecondsFromTime($est_travel_time);

		return $total_seconds;
	}

	public function getUserRemainingSecondsAttribute() {

		if ($this->status_text != "pending" && $this->status_text != "accepted" && $this->status_text != "delivery") {
			return 100;
		}
		$current_date = Carbon::parse();
		
		$updated_time = $this->payment->created_at ?? '';

		$expiry_seconds = $this->user_total_seconds;

		$expiry_date =  $updated_time ? (clone $updated_time)->addSeconds($expiry_seconds) : '';
		$remaining_seconds = $current_date->diffInRealSeconds($expiry_date, false);
		
		return $remaining_seconds;
	}

	//estimate_preparation_time
	public function getEstimatePreparationTimeAttribute()
	{
		$total_seconds = getSecondsFromTime($this->est_preparation_time);
		$preparation_time = $this->accepted_at->addSeconds($total_seconds);
		return $preparation_time;
	}

	public function getUserLocationAttribute()
	{
		if (isset($this->attributes['user_id'])) {
			$user_id = $this->attributes['user_id'];
				
			$store_user_id = Store::where('id', $this->attributes['store_id'])->first()->user_id;

			$userAddress = UserAddress::where('user_id', $user_id)->default()->first();
			$StoreAddress = UserAddress::where('user_id', $store_user_id)->first();

			return [$StoreAddress,$userAddress];
		}
		return ['', ''];
	}

	public function getUserLanguage()
	{
		if (isset($this->attributes['user_id'])) {
			$user_id = $this->attributes['user_id'];

			$user_language = User::where('id', $user_id)->first()->language;

			return $user_language;
		}
		return en;
	}

	//get_user_payout values
	public function get_user_payout($value)
	{
		$user = $this->payout_table()->where('user_id', $this->attributes['user_id'])->first();

		if ($user) {
			return $user->$value;
		}
	}

	//get_store_payout values
	public function get_store_payout($value)
	{
		$store = $this->store()->first();
		$store_id = '';
		if ($store) {
			$store_id = $store->user_id;
		}
		$payout = $this->payout_table()->where('user_id', $store_id)->first();
		if ($payout) {
			return $payout->$value;
		}
	}

	//get_driver_payout values
	public function get_driver_payout($value)
	{
		$driver = $this->driver()->first();
		$driver_id = '';
		if ($driver) {
			$driver_id = $driver->user_id;
		}

		$driver = $this->payout_table()->where('user_id', $driver_id)->first();

		if ($driver) {
			return $driver->$value;
		}

	}

	public function getStatusTextAttribute()
	{
		return array_search($this->status, $this->statusArray);
	}

	public function getPaymentTypeTextAttribute()
	{
		$wallet = $this->wallet_amount > 0 ? ' & Wallet' : '';
		$pay_mode = $this->payment_type != 0 ? ($this->payment_type == 1 ? 'Credit Card' : 'Paypal') : 'Cash';
		if ($this->total_amount > 0) {
			return $pay_mode . $wallet;
		} else if ($this->wallet_amount > 0) {
			return 'Wallet';
		} else {
			return $pay_mode;
		}
	}

	public function getCanceledByTextAttribute()
	{
		return array_search($this->cancelled_by, $this->userTypeArray);
	}

	//store_service
	public function getStoreServiceAttribute()
	{
		if (isset($this->store_id)) {
			$service_type 	= Store::whereId($this->store_id)->first()->service_type;
			$service 	= ServiceType::whereId($service_type)->first();
			return $service->service_name ?? '';
		}
		return '';
	}

	public function getStoreStatusAttribute()
	{
		if (isset($this->store_id)) {
			return Store::whereId($this->store_id)->first()->status;
		}

		return '';
	}

	public function change_time_format($date,$day='')
	{
		if (date('Ymd') == date('Ymd', strtotime($date)) && isset($day))
		{
			return trans('messages.profile_orders.today').' '.date('h:i', strtotime($date)).'  '.trans('messages.driver.'.date('a', strtotime($date))) ;
		}
		else if (date('Ymd') == date('Ymd', strtotime($date))) {
			return trans('messages.profile_orders.today').' '. trans('messages.driver.'.date('M', strtotime($date))).' '.date('h:i', strtotime($date)).' '.trans('messages.driver.'.date('a', strtotime($date))) ;
		}
		else {
			return date('d', strtotime($date)).' '.trans('messages.driver.'.date('M', strtotime($date))).' '.date('h:i', strtotime($date)).' '.trans('messages.driver.'.date('a', strtotime($date)));
		}

	}

	//started_at_time
	public function getStartedAtTimeAttribute()
	{
		
			if (isset($this->order_delivery->started_at)) {
				return $this->change_time_format($this->order_delivery->started_at);
			}
	}

	//accepted_at_time
	public function getAcceptedAtTimeAttribute()
	{	
		if (isset($this->accepted_at)) {
		  $day='d';
		  return $this->change_time_format($this->accepted_at,$day);
		}
	}

	//delivery_at_time
	public function getDeliveryAtTimeAttribute() {
		
		if ($this->attributes['delivery_at']) {
			$day = 'd';
			return $this->change_time_format($this->delivery_at,$day);
		}

	}
	//completed_at_time
	public function getCompletedAtTimeAttribute() {
		if ($this->attributes['completed_at']) {
			return $this->change_time_format($this->completed_at);
		}

	}
	//cancelled_at_time
	public function getCancelledAtTimeAttribute() {
		if ($this->attributes['cancelled_at']) {
			return $this->change_time_format($this->cancelled_at);
		}

	}
	//declined_at_time
	public function getDeclinedAtTimeAttribute() {
		if ($this->attributes['declined_at']) {
			return $this->change_time_format($this->declined_at);
		}

	}

	public function getUserStatusTextAttribute() {
		$getLocale = App::getLocale();
		
		$status_text = $this->status_text;

		if ($this->schedule_status == 0) {

			switch ($status_text) {

			case 'pending':
				$user_status_text = trans('api_messages.store.confirming_order');
				break;

			case ($this->status_text == 'accepted' && !$this->order_delivery->started_at):
				$user_status_text = trans('api_messages.store.preparing_your_order');
				break;

			case ($this->status_text == 'delivery' && !$this->order_delivery->started_at):
				$user_status_text = trans('api_messages.store.preparing_your_order');
				break;

			case ($status_text == 'delivery' && isset($this->order_delivery->started_at)):
				$user_status_text = trans('api_messages.store.food_on_the_way');
				break;
			case 'declined':
				$user_status_text = trans('api_messages.store.order_declined');
				break;

			case 'cancelled':
				$user_status_text = trans('api_messages.store.order_cancelled');
				break;

			case 'completed':
				$user_status_text = trans('api_messages.store.order_delivered');
				break;

			case 'takeaway':			

				$user_status_text = trans('api_messages.store.takeaway');
				break;

			default:
				$user_status_text = "";
				break;
			}
		} else {

			$user_status_text = trans('api_messages.store.order_scheduled');

		}

		return $user_status_text;
	}

	public function getDeliveryAtAttribute() {
		if($this->delivery_type =='takeaway')
			$delivery_at = isset($this->attributes['est_delivery_time']) ? $this->attributes['est_delivery_time'] : '';
		else 
			$delivery_at = isset($this->attributes['delivery_at']) ? $this->attributes['delivery_at'] : '';
		if($delivery_at)
			$delivery_at = Carbon::parse($delivery_at);
		$order_delivery = OrderDelivery::whereNotIn('status', ['0,1'])->where('order_id', $this->id)->first();
		if ($delivery_at && $order_delivery && $this->delivery_type != "takeaway") {
			$delivery_at = Carbon::parse($order_delivery->started_at);
		} else if ($this->status_text != "completed" && $this->accepted_at && $this->status_text != "takeaway") {
			$preparation_time = $this->est_preparation_time;
			$total_seconds = getSecondsFromTime($preparation_time);
			$delivery_at = $this->accepted_at->addSeconds($total_seconds);
		}	
		else if($this->delivery_type == "takeaway" && !$this->attributes['delivery_at'])
		{
			$delivery_at = $this->attributes['delivery_at'];
		}
		return $delivery_at;
	}

	public function getCompletedAtAttribute() {

		$completed_at = isset($this->attributes['completed_at']) ? $this->attributes['completed_at'] : '';

		if ($completed_at) {

			$completed_at = Carbon::parse($completed_at);
		}

		if ($this->status_text != "completed" && $this->getDeliveryAtAttribute()) {

			$delivery_time = $this->attributes['est_travel_time'];
			$total_seconds = getSecondsFromTime($delivery_time);
			if($this->status_text == "takeaway")
			{
				$delivery_at = Carbon::parse($this->getDeliveryAtAttribute());
				$completed_at =  $delivery_at->addSeconds($total_seconds);
			}
			else
				$completed_at = $this->getDeliveryAtAttribute()->addSeconds($total_seconds);
		}

		return $completed_at;
	}

	public function getStoreTotalAttribute() {

		if ($this->store_penality >= $this->subtotal + $this->tax) {
			return 0;
		}

		return $this->subtotal + $this->tax - $this->store_penality;
	}

	//is_refund_to_user
	public function getIsRefundToUserAttribute() {
		return (($this->attributes['wallet_amount'] > 0) || $this->attributes['payment_type'] == 1 || $this->attributes['payment_type'] == 2) ? true : false;
	}
	//is_payout_create_or_not
	public function getIsPayoutCreateOrNotAttribute() {
		if ($this->store->user->payout_id) {
			if ($this->driver) {
				if ($this->driver->user->payout_id) {
					$data['button'] = trans('admin_messages.payout');
					$data['message'] = trans('admin_messages.updated_successfully');
				} else {
					$data['button'] = trans('admin_messages.create_payout');
					$data['message'] = trans('admin_messages.payout_create_successfully');
				}
			} else {
				$data['button'] = trans('admin_messages.payout');
				$data['message'] = trans('admin_messages.updated_successfully');
			}

		} else {
			$data['button'] = trans('admin_messages.create_payout');
			$data['message'] = trans('admin_messages.payout_create_successfully');
		}
		return $data;
	}

	//card_refund_amount
	public function getCardRefundAmountAttribute() {
		if ($this->attributes['payment_type'] == 1) {
			return $this->attributes['total'] - $this->attributes['wallet_amount'];
		}

	}

	public function getUserTotalAttribute() {
		$delivery_fee = ($this->delivery_type == 'delivery') ?  $this->delivery_fee : 0;
		$tips = ($this->attributes['tips']) ?? 0.00;
		$fees = $this->subtotal + $this->tax + $delivery_fee + $this->booking_fee + $this->user_penality ;
		$total = $fees - $this->promo_amount;
		if ($fees <= $this->promo_amount) {
			$total = 0;
		}
		return number_format_change($total,2);

	}


	public function decline_order()
	{
		$payment_controller = resolve('App\Http\Controllers\Api\PaymentController');
		$payment_controller->refundOrder($this->id, '', $this->user_id);
		$this->status = $this->statusArray['declined'];
		$this->declined_at = date('Y-m-d H:i:s');
		$this->save();
	}

	public function accept_order()
	{
		$getUserLocale = $this->getUserLanguage();
		$this->status = $this->statusArray['accepted'];
		$this->accepted_at = date('Y-m-d H:i:s');
		$this->save();

		if ($this->schedule_status == 0) {
			$user = $this->user;
			$push_notification_title = trans('api_messages.orders.food_preparation_order',array(),$getUserLocale) . $this->id;
			$push_notification_data = [
				'type' => 'order_accepted',
				'order_id' => $this->id,
			];

			push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
		}

		$this->payout($this->id, $this->store_total, $this->currency_code, $this->userTypeArray['store']);
	}

	public function deliver_order()
	{
		searchDrivers($this->id);
	}

	public function cancel_order($user_type = "user", $cancel_reason = null, $cancel_message = "")
	{
		$getUserLocale = $this->getUserLanguage();
		if ($user_type == "driver" && ($this->order_delivery->status_text == 'pending' || $this->order_delivery->status_text == 'confirmed')) {
			// Change driver status to online
			$driver_count = Order::where('status' ,5 )->where('id','!=',$this->id)->where('driver_id',$this->driver_id)->count();
			if(!$driver_count)
			{
				$this->driver->status == 1;
				$this->driver->save();
			}

			$this->status = $this->statusArray['accepted'];
			$this->driver_id = NULL;
			$this->save();

			$user = $this->store->user;
			$push_notification_title = trans('api_messages.orders.your_order_id',array(),$getUserLocale). $this->id . trans('api_messages.orders.has_been_cancelled',array(),$getUserLocale) . $user_type;
			$push_notification_data = [
				'type' => 'order_cancelled',
				'order_id' => $this->id,
				'redirect' => '0',
			];

			push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
		}
		else {
			$this->status = $this->statusArray['cancelled'];
			$this->cancelled_at = date('Y-m-d H:i:s');
			$this->cancelled_by = $this->userTypeArray[$user_type];
			$this->cancelled_reason = $cancel_reason;
			$this->cancelled_message = $cancel_message;

			if ($user_type == 'user') {
				$this->payout_is_create = 1;
			}

			$this->save();

			$user = ($user_type == "user") ? $this->store->user : $this->user;
			$push_notification_title = trans('api_messages.orders.your_order_id',array(),$getUserLocale) . $this->id .trans('api_messages.orders.has_been_cancelled',array(),$getUserLocale) . $user_type;
			$push_notification_data = [
				'type' => 'order_cancelled',
				'order_id' => $this->id,
				'redirect' => '1',
			];

			push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

			if ($user_type == "driver") {
				$user = $this->store->user;
				$push_notification_title = trans('api_messages.orders.your_order_id',array(),$getUserLocale) . $this->id . trans('api_messages.orders.has_been_cancelled',array(),$getUserLocale) . $user_type;
				$push_notification_data = [
					'type' => 'order_cancelled',
					'order_id' => $this->id,
					'redirect' => '1',
				];

				push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
			}

			if (isset($this->driver_id) && $user_type != "driver") {
				$user = $this->driver->user;
				$push_notification_title = trans('api_messages.orders.your_order_id',array(),$getUserLocale) . $this->id . trans('api_messages.orders.has_been_cancelled',array(),$getUserLocale) . $user_type;
				$push_notification_data = [
					'type' => 'order_cancelled',
					'order_id' => $this->id,

				];

				$this->order_delivery->res_cancelled();

				// Change driver status to online
				$this->driver->status = 1;
				$this->driver->save();

				push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
			}
		}
	}

	public function delay_order($seconds = 0, $delay_message = "")
	{
		$getUserLocale = $this->getUserLanguage();
		//update Negative seconds to delay seconds 
		$seconds = $this->getRemainingSecondsAttribute() < 0 ? (abs($this->getRemainingSecondsAttribute())+$seconds):$seconds;
		$this->delay_min = $this->delay_min ? Carbon::parse($this->delay_min)->addSeconds($seconds)->format('H:i:s') : getTimeFromSeconds($seconds);
		$this->est_preparation_time = Carbon::parse($this->est_preparation_time)->addSeconds($seconds)->format('H:i:s');
		
		$this->est_delivery_time = Carbon::parse($this->est_delivery_time)->addSeconds($seconds)->format('H:i:s');

		$this->delay_message = $delay_message;
		$this->save();

		$user = $this->user;
		$push_notification_title = trans('api_messages.orders.your_order_id',array(),$getUserLocale) . $this->id . trans('api_messages.orders.delay_for',array(),$getUserLocale) . (int) gmdate("i", $seconds) . trans('api_messages.orders.mins',array(),$getUserLocale);
		$push_notification_data = [
			'type' => 'order_delayed',
			'order_id' => $this->id,

		];

		push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

		if (isset($this->driver_id)) {

			$user = $this->driver->user;
			$push_notification_title = trans('api_messages.orders.your_pickup_orderId',array(),$getUserLocale) . $this->id . trans('api_messages.orders.delay_for',array(),$getUserLocale) . (int) gmdate("i", $seconds) . trans('api_messages.orders.mins',array(),$getUserLocale);
			$push_notification_data = [
				'type' => 'order_delayed',
				'order_id' => $this->id,
			];

			push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

		}
	}

	public function driver_accepted(DriverRequest $driver_request) {
		$getUserLocale = $this->getUserLanguage();
		$this->driver_id = $driver_request->driver_id;
		$this->save();
		// $order_delivery = new OrderDelivery;
		// $order_delivery->order_id = $driver_request->order_id;
		$order_delivery = $this->order_delivery;
		$order_delivery->request_id = $driver_request->id;
		$order_delivery->driver_id = $driver_request->driver_id;
		$order_delivery->pickup_latitude = $driver_request->pickup_latitude;
		$order_delivery->pickup_longitude = $driver_request->pickup_longitude;
		$order_delivery->drop_latitude = $driver_request->drop_latitude;
		$order_delivery->drop_longitude = $driver_request->drop_longitude;
		$order_delivery->pickup_location = $driver_request->pickup_location;
		$order_delivery->drop_location = $driver_request->drop_location;
		$order_delivery->status = $order_delivery->statusArray['pending'];
		$order_delivery->drop_distance = 0;
		$order_delivery->save();
		$store = $this->store->user;
		$push_notification_title = trans('api_messages.orders.driver_accepted_orderId',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'driver_accepted',
			'order_id' => $this->id,
		];

		push_notification($store->device_type, $push_notification_title, $push_notification_data, $store->type, $store->device_id);

	}

	public function delivery_started() {
		$getUserLocale = $this->getUserLanguage();
		$user = $this->user;
		$push_notification_title = trans('api_messages.orders.order_delivery_orderId',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'order_delivery_started',
			'order_id' => $this->id,
		];

		push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

		$store = $this->store->user;
		$push_notification_title = trans('api_messages.orders.order_delivery_orderId',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'order_delivery_started',
			'order_id' => $this->id,
		];
		
		push_notification($store->device_type, $push_notification_title, $push_notification_data, $store->type, $store->device_id);

	}

	public function delivery_delivered($recipient) {
		$this->recipient = $recipient;
		$this->save();
	}

	public function delivery_completed($pickup_drop_fare='true')
	{
		$getUserLocale = $this->getUserLanguage();
		$this->status = $this->statusArray['completed'];
		$this->completed_at = date('Y-m-d H:i:s');
		$this->save();

		$user = $this->user;
		$push_notification_title = trans('api_messages.orders.order_delivery_completed_orderId',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'order_delivery_completed',
			'order_id' => $this->id,
		];

		push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

		$store = $this->store->user;
		$push_notification_title = trans('api_messages.orders.order_delivery_completed_orderId',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'order_delivery_completed',
			'order_id' => $this->id,
		];

		push_notification($store->device_type, $push_notification_title, $push_notification_data, $store->type, $store->device_id);
		// if($pickup_drop_fare=='true'){	
		//  $amount = $this->delivery_fee;			
		// }
		// else{	
			$amount = $this->order_delivery->total_fare  - $this->driver_commision_fee;
		// }	
		
		$order_id = $this->id;
		$currency_code = $this->currency_code;
		$type = $this->userTypeArray['driver'];
		$payment_method = $this->payment_type;

		// Driver payout
		$this->payout($order_id, $amount, $currency_code, $type, $payment_method,$pickup_drop_fare);
	}

	public function getDropoffOptions()
	{
		$DropoffOptions = FoodReceiver::pluck('name', 'id')->toArray();
		$DropoffOptions['0'] = $this->user->name . trans('api_messages.intended_recipient');
		ksort($DropoffOptions);
		$dropoff_options = collect($DropoffOptions)->values();
		return $dropoff_options;
	}

	public function payout($order_id, $amount, $currency_code, $type, $payment_method = '',$pickup_drop_fare='') {
		// create Store Payout
		
		if ($type == 1) {
			// Admin commission fee
			$commission = site_setting('store_commision_fee');
			$commission_fee = number_format_change($amount * $commission / 100);
			$this->store_commision_fee = $this->currency_convert($this->currency_code,$this->original_currency_code,$commission_fee);
			$this->save();

			$penality = penality($order_id);

			$penality_details = PenalityDetails::where('order_id', $this->id)->first();
			$penality_amount = 0;
			if ($penality_details) {
				$penality_amount = $penality_details->previous_store_penality;
			}
			$store_payout = $amount - $commission_fee - $penality_amount;
			$delivery_type = Order::find($order_id);
			if($this->payment_type == 0)
			{
				if($delivery_type->status == '6' && $delivery_type->delivery_type ='takeaway')
				{ 
					$owe_amount = StoreOweAmount::where('user_id', get_current_login_user_id())->first();
					$storeoweAmount = $this->booking_fee +  $this->store_commision_fee;
					if ($payment_method == 0) {
						if ($owe_amount) {
							$total_owe_amount = $owe_amount->amount + $storeoweAmount;
							$owe_amount->amount = $total_owe_amount;
							$owe_amount->save();
							
						} else {
							$driver = new StoreOweAmount;
							$driver->user_id = get_current_login_user_id();
							$driver->amount = $storeoweAmount ;
							$driver->currency_code = $currency_code;
							$driver->save();
						}
					}
				}			
			}
			if($this->delivery_type =='delivery')
			{
				if ($store_payout != 0) {
					$payout = new Payout;
					$payout->order_id = $order_id;
					$payout->user_id = get_current_login_user_id();
					$payout->amount = $store_payout;
					$payout->status = 0;
					$payout->currency_code = $currency_code;
					$payout->save();
				}
			}
			else if($this->delivery_type =='takeaway' && $this->payment_type != 0)
			{
				$payout_store = Payout::where('order_id',$order_id)->where('user_id',get_current_login_user_id())->first();
				if ($store_payout != 0) {
					if(isset($payout_store))
						$payout = $payout_store;
					else	
						$payout = new Payout;
					$payout->order_id = $order_id;
					$payout->user_id = get_current_login_user_id();
					$payout->amount = $store_payout;
					$payout->status = 0;
					$payout->currency_code = $currency_code;
					$payout->save();
				}
			}
		} else {
			//Admin commission fee
			$payout_amount = 0;

			$commission = site_setting('driver_commision_fee');
			$commission_fee = number_format_change($amount * $commission / 100);
			
			$this->driver_commision_fee = $this->currency_convert($this->currency_code,$this->original_currency_code,$commission_fee);
			$this->save();
			$order_owe_amount = 0;
			$driver_payout = number_format( $amount + $this->tips - $this->driver_commision_fee, 2 );
			$owe_amount = DriverOweAmount::where('user_id', get_current_login_user_id())->first();
			$penality = penality($this->id);
			if ($payment_method == 0) {
				if ($owe_amount) {
					$total_owe_amount = $owe_amount->amount + $this->owe_amount + $penality;
					$owe_amount->amount = $total_owe_amount;
					$owe_amount->save();
				} else {
					$driver = new DriverOweAmount;
					$driver->user_id = get_current_login_user_id();
					$driver->amount = $this->owe_amount  + $penality;
					$driver->currency_code = $currency_code;
					$driver->save();
				}
			}

			if (($this->total_amount == 0 && $payment_method == 0) || $payment_method == 1 || $payment_method == 2) {
				$payout_amount = $driver_payout;
				$this->save();
				$payout = new Payout;
				$payout->order_id = $order_id;
				$payout->user_id = get_current_login_user_id();
				$payout->amount = $payout_amount;
				$payout->status = 0;
				$payout->currency_code = $currency_code;
				$payout->save();
			}
		}
	}

	//currency
	public function currency()
	{
		return $this->belongsTo('App\Models\Currency', 'currency_code', 'code');
	}

	/**	User penality previous order **/
	public function getUserPenalityAttribute()
	{
		$penalty  = isset($this->user->penalty)  ? $this->user->penalty->remaining_amount : 0 ;
		return  $penalty ;
	}

	/**	User Applied penality **/
	public function getUserAppliedPenalityAttribute()
	{
		$penality = PenalityDetails::where('order_id', $this->id)->where('is_user_penality', '1')->first();
		if($penality) {
			return (string) $penality->user_penality;
		}
		return '0';
	}

	/** Store penality previous order **/
	public function getStorePenalityAttribute()
	{
		$penality = $this->penality_details;

		if($penality) {
			if (isset($penality->previous_store_penality)) {
				return (string) $penality->previous_store_penality;
			}
		}
		return '0';
	}

	/** Store Applied penality  **/
	public function getResAppliedPenalityAttribute()
	{
		$penality = $this->penality_details()->where('is_store_penality', '1')->first();

		if ($penality) {
			return (string) $penality->store_penality;
		}

		return '0';
	}

	/**Driver penality **/
	public function getDriverPenalityAttribute()
	{
		$penality = PenalityDetails::where('order_id', $this->id)->where('is_driver_penality', 0)->first();

		if ($penality) {
			return (string) $dri_penality = isset($penality->driver_penality) ? $penality->driver_penality : 0;
		}

		return '0';
	}

	/**Applied Driver penality **/
	public function getAppDriverPenalityAttribute()
	{
		$penality = PenalityDetails::where('order_id', $this->id)->where('is_driver_penality', 1)->first();
		if($penality) {
			return (string) $dri_penality = isset($penality->driver_penality) ? $penality->driver_penality : 0;
		}
		return '0';
	}

	public function completeOrder()
	{
		$getUserLocale = $this->getUserLanguage();
		$this->status = $this->statusArray['completed'];
		$this->completed_at = date('Y-m-d H:i:s');
		$this->save();

		$user = $this->user;

		$push_notification_title = trans('api_messages.orders.order_takeaway',array(),$getUserLocale) . $this->id;
		$push_notification_data = [
			'type' => 'order_delivery_completed',
			'order_id' => $this->id,
		];
			
		push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);
		$this->payout($this->id, $this->store_total, $this->currency_code, $this->userTypeArray['store']);
	}

	// Join with  table
    public function user_address() {
        return $this->belongsTo('App\Models\UserAddress', 'user_id', 'user_id');
    }

    	
    public function getOrderReviewAttribute() {
		$review = Review::where('order_id', $this->attributes['id'])->wherein('type', [0,1,2,3,4,5,6])->get();
		if ($review) {
			return $review;
		}
		return [];
	}

	//accepted_at_time
	public function getEstimationDeliveryTimeAttribute()
	{	
		if (isset($this->est_delivery_time)) {
		  $day='d';
		  return $this->change_time_format($this->est_delivery_time,$day);
		}
	}

}
