<?php
/**
 * OrderController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\EloquentDataTableBase;
use App\Models\DriverOweAmount;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\Payout;
use App\Models\Penality;
use App\Models\PenalityDetails;
use App\Models\Store;
use App\Traits\PaymentProcess;
use Carbon;
use DataTables;
use DB;
use Validator;
use App\Http\Controllers\DataTableBase;
class OrderController extends Controller {

	use PaymentProcess;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view_order(Request $request)
	{
		$this->view_data['order_id'] = $request->order_id;
		$this->view_data['order'] = Order::getAllRelation()->Where('id', $this->view_data['order_id'])->firstOrFail();
		$this->view_data['form_action'] = route('admin.view_order', $this->view_data['order_id']);
		$this->view_data['form_name'] = trans('admin_messages.view_order');

		$this->view_data['cancel_reason'] = OrderCancelReason::where('type', 3)->where('status', 1)->pluck('name', 'id');
		$this->view_data['takeaway_cancel_reason'] =  OrderCancelReason::where('type', 3)->where('id' ,'!=','12')->where('status', 1)->pluck('name', 'id');
		$user_penality = Penality::where('user_id', $this->view_data['order']->user_id)->first();
		$this->view_data['user_penality'] = 0;
		if ($user_penality) {
			$this->view_data['user_penality'] = $user_penality->remaining_amount;
		}

		$rest = get_store_user_id($this->view_data['order']->store_id, 'user_id');
		$res_penality = Penality::where('user_id', $rest)->first();

		$this->view_data['store_penality'] = 0;

		if ($res_penality) {
			$this->view_data['store_penality'] = $res_penality->remaining_amount;
		}

		$this->view_data['driver_owe_amount'] = 0;
		if ($this->view_data['order']->driver_id) {
			$driver_id = get_driver_user_id($this->view_data['order']->driver_id);
			$driver_owe = DriverOweAmount::where('user_id', $driver_id)->first();
			if ($driver_owe) {
				$this->view_data['driver_owe_amount'] = $driver_owe->amount;
			}
		}
		return view('admin/orders/order_detail', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function cancel_order(Request $request)
	{
		$order_id = $request->order_id;
		$order = Order::find($order_id);

		if ($order->status == 4) {
			flash_message('danger', trans('admin_messages.already_cancel_order'));
			return redirect()->route('admin.view_order', request()->order_id);
		}

		if ($order->order_delivery) {
			$order->order_delivery->cancelled();
		}

		$order->status = $order->statusArray['cancelled'];
		$order->cancelled_at = date('Y-m-d H:i:s');
		$order->cancelled_by = $order->userTypeArray['admin'];
		$order->cancelled_reason = request()->cancel_reson;
		$order->cancelled_message = request()->cancel_message;
		$order->save();

		//Revert Penality amount if exists

		$penality_Revert = revertPenality($order->id);

		//push notification to all users
		$this->cancel_push_notification($order_id);
		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.view_order', request()->order_id);
	}

	/**
	 * Send cancel push notification
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function cancel_push_notification($order_id) {
		$order = Order::find($order_id);

		$push_notification_title = "Cancel order by admin";

		$push_notification_data = [
			'type' => 'order_cancelled',
			'order_id' => $order_id,
		];
		//driver
		if ($order->driver) {
			push_notification($order->driver->user->device_type, $push_notification_title, $push_notification_data, $order->driver->user->type, $order->driver->user->device_id);
		}

		//user
		push_notification($order->user->device_type, $push_notification_title, $push_notification_data, $order->user->type, $order->user->device_id);
		$push_notification_data['redirect'] = '0';
		//store
		push_notification($order->store->user->device_type, $push_notification_title, $push_notification_data, $order->store->user->type, $order->store->user->device_id);

	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function admin_payout() {

		$rules = array(
			'refund_to_user' => 'numeric|nullable',
			'penalty_to_user' => 'numeric|nullable',
			'payout_to_store' => 'numeric|nullable',
			'penalty_to_store' => 'numeric|nullable',
			'payout_to_driver' => 'numeric|nullable',
			'penalty_to_driver' => 'numeric|nullable',
		);

		// Validation Custom Names
		$niceNames = array(
			'refund_to_user' => trans('admin_messages.refund_to_user'),
			'penalty_to_user' => trans('admin_messages.penalty_to_user'),
			'payout_to_store' => trans('admin_messages.payout_to_store'),
			'penalty_to_store' => trans('admin_messages.penalty_to_store'),
			'payout_to_driver' => trans('admin_messages.payout_to_driver'),
			'penalty_to_driver' => trans('admin_messages.penalty_to_driver'),
		);

		$validator = Validator::make(request()->all(), $rules);
		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
		} else {
			$request = request();
			$order_id = $request->order_id;
			$order = Order::find($order_id);
			$order->user_notes = request()->user_notes;
			$order->store_notes = request()->store_notes;
			$order->driver_notes = request()->driver_notes;
			if (isset($request->refund_to_user)) {
				$refund_card_amount = 0;
				if($order->wallet_amount > 0)
				{
					if ($order->payment_type == 1) {
						$valitate_amount = $order->total_amount + $order->wallet_amount;
						if ($request->refund_to_user > $valitate_amount) {
							return back()->withErrors(['refund_to_user' => trans('admin_messages.refund_to_user_validation', ['amount' => currency_symbol() . $valitate_amount])])->withInput();
						}

						if ($request->refund_to_user > $order->wallet_amount) {
							$refund_card_amount = $request->refund_to_user - $order->wallet_amount;
							$refund_wallet_amount = $order->wallet_amount;
						} else {
							$refund_wallet_amount = $request->refund_to_user;
						}

						if ($refund_card_amount > 0) {
							$refund = $this->refund_to_users($refund_card_amount, $order->payment->transaction_id);
							if ($refund['success'] == true) {
								if ($refund_wallet_amount > 0) {
									$this->refund_to_wallet($order->user_id, $refund_wallet_amount);
								}

							} else {
								return back()->withErrors(['refund_to_user' => $refund['message']])->withInput();
							}
						} else if ($refund_wallet_amount > 0) {
							$this->refund_to_wallet($order->user_id, $refund_wallet_amount);
						}
					} else {
						if ($request->refund_to_user > $order->wallet_amount) {
							return back()->withErrors(['refund_to_user' => trans('admin_messages.refund_to_user_wallet_valitation', ['amount' => currency_symbol() . $order->wallet_amount])])->withInput();
						}
						$this->refund_to_wallet($order->user_id, $request->refund_to_user);
					}
				}
				else
				{
					$refund_card_amount = $request->refund_to_user;
					if ($refund_card_amount > 0) {
						if ($order->payment_type == 1) {
							$refund = $this->refund_to_users($refund_card_amount, $order->payment->transaction_id);
						} 
						else
						{
							$stripe_payment = resolve('App\Repositories\StripePayment');
							$refund = $stripe_payment->RefundPaypal($order->payment->transaction_id,$refund_card_amount);
						}
					}
					else
					{
						return back()->withErrors(['refund_to_user' => trans('admin_messages.refund_to_user_validation', ['amount' => currency_symbol() . $valitate_amount])])->withInput();
					}
				}
				$this->save_payout($order->id, $order->user_id, $request->refund_to_user, DEFAULT_CURRENCY, 1, $order->payment->transaction_id);
			}
			if ($request->penalty_to_user) {
				$this->save_penality('user_penality', $order->id, $order->user_id, $request->penalty_to_user, DEFAULT_CURRENCY, isset($request->is_penalty_to_user) ? $request->is_penalty_to_user : '');
			}

			// store payout create or update
			if($request->payout_to_store || $request->payout_to_store == 0){
				$this->save_payout($order->id, $order->store->user_id, $request->payout_to_store, DEFAULT_CURRENCY);
			}
			
			if ($request->penalty_to_store) {
				$this->save_penality('store_penality', $order->id, $order->store->user_id, $request->penalty_to_store, DEFAULT_CURRENCY, isset($request->is_penalty_to_store) ? $request->is_penalty_to_store : '');
			}

// driver payout create or update
			if (isset($order->driver_id)) {
				if($request->payout_to_driver){
					$this->save_payout($order->id, $order->driver->user_id, $request->payout_to_driver, DEFAULT_CURRENCY);
				}
				if ($request->penalty_to_driver) {
					$this->save_penality('driver_penality', $order->id, $order->driver->user_id, $request->penalty_to_driver, DEFAULT_CURRENCY, isset($request->is_penalty_to_driver) ? $request->is_penalty_to_driver : '', 'driver');
				}
			}

			$order->payout_is_create = 1;
			$order->save();
			$status = '';
			//payout to store
			if (request()->payout_to_store && $order->store->user->payout_id) {
				$store_status = $this->admin_payout_to_user($order->store->user_id, $order->id);
				$status = $store_status['success'];
				if ($status == false) {
					flash_message('danger', $store_status['message']);
					return redirect()->route('admin.view_order', $order_id);
				}
			}
			//payout to driver
			if (request()->payout_to_driver && $order->driver->user->payout_id) {
				$driver_status = $this->admin_payout_to_user($order->driver->user_id, $order->id);
				$status = $driver_status['success'];
				if ($status == false) {
					flash_message('danger', $driver_status['message']);
					return redirect()->route('admin.view_order', $order_id);
				}
			}

			if(request()->refund_to_user > 0 ||request()->payout_to_store > 0 ||request()->payout_to_driver > 0 )
				flash_message('success', $order->is_payout_create_or_not['message']);
			else if($request->penalty_to_user > 0 || $request->penalty_to_store > 0 ||$request->penalty_to_driver > 0 )
				flash_message('success', trans('admin_messages.penalty_apply_success'));
			else
				flash_message('success', trans('admin_messages.updated_successfully'));
			return redirect()->route('admin.view_order', $order_id);
		}
	}

	public function stripe_payout($amount, $currency, $payout_user_id, $transaction_id) {

		$stripe_key = site_setting('stripe_secret_key');
		\Stripe\Stripe::setApiKey($stripe_key);
		try
		{

			$response = \Stripe\Payout::create(array(
				"amount" => $amount,
				"currency" => $currency,
				"destination" => $payout_user_id,
				"source_type" => 'bank_account',
			));

		} catch (\Exception $e) {
			$data['success'] = false;
			$data['message'] = $e->getMessage();
			return $data;
		}
		if ($response->isSuccessful()) {
			$response_data = $response->getData();

			$correlation_id = @$response_data['id'];
			$data['success'] = true;
			$data['transaction_id'] = $correlation_id;
			return $data;
		} else {
			$data['success'] = false;
			$data['message'] = $response->getMessage();
			return $data;
		}
	}

	public function save_payout($order_id, $user_id, $amount, $currency_code, $status = 0, $transaction_id = null) {
		$payout = Payout::where('order_id', $order_id)->where('user_id', $user_id)->first();

		if ($payout == '') {
			$payout = new Payout;
		}
		$payout->order_id = $order_id;
		if ($transaction_id) {
			$payout->transaction_id = $transaction_id;
		}

		$payout->user_id = $user_id;
		$payout->amount = $amount ? $amount : 0;
		if ($amount < 1) {
			$payout->status = 1;
		} else {
			$payout->status = $status;
		}

		$payout->currency_code = $currency_code;
		$payout->save();
	}

	public function save_penality($type, $order_id, $user_id, $amount, $currency_code, $status = '', $user_type = '') {

		$is_user = 'is_' . $type;

		$order = PenalityDetails::where('order_id', $order_id)->first();

		if (!$order) {

			$order = new PenalityDetails;
			$order->order_id = $order_id;

		}

		if ($status == 'on') {

			$order->$is_user = 1;

		}

		$order->$type = $amount;
		$order->save();


		if ($user_type == '') {
			
			$total_amount = $amount;
			$remaining_amount = ($status != 'on') ? $amount : 0;
			$paid_amount = ($status == 'on') ? $amount : 0;
			$penality = Penality::where('user_id', $user_id)->first();
			
			if ($penality) {
				if ($status == 'on') {
					$remaining_amount = $penality->remaining_amount - $amount;
					$paid_amount = $penality->paid_amount + $amount;
					$total_amount = $penality->amount;
				} else {
					$remaining_amount = $penality->remaining_amount + $amount;
					$paid_amount = $penality->paid_amount;
					$total_amount = $penality->amount + $amount;
				}

			} else {
				$penality = new Penality;
			}

			$penality->user_id = $user_id;
			$penality->amount = $total_amount;
			$penality->remaining_amount = $remaining_amount > 0 ? $remaining_amount : 0;
			$penality->paid_amount = $paid_amount;
			$penality->currency_code = $currency_code;
			$penality->save();
		}
		if ($user_type == 'driver') {

			$owe_amount = DriverOweAmount::where('user_id', $user_id)->first();

			if ($owe_amount) {
				if ($status == '') {
					$total_owe_amount = $owe_amount->amount + $amount;
				} else {
					$total_owe_amount = $owe_amount->amount - $amount;
				}

				$owe_amount->amount = $total_owe_amount;
				$owe_amount->save();

			} elseif ($status == '') {

				$driver = new DriverOweAmount;
				$driver->user_id = $user_id;
				$driver->amount = $amount;
				$driver->save();
			}

		}

	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function orders() {
		$this->view_data['form_name'] = trans('admin_messages.orders', ['store_name' => '']);
		return view('admin/orders/orders', $this->view_data);

	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function sort_order() 
	{
		$order = new Order;
		$filter_type = request()->filter_type;
		$count_text = "Overall Statement";
		if ($filter_type == "custom") {
			$from = date('Y-m-d' . ' 00:00:00', strtotime(request()->from_dates));
			if (request()->has('to_dates')) {
				$to = date('Y-m-d' . ' 23:59:59', strtotime(request()->to_dates));
				$order = $order->whereBetween('created_at', array($from, $to));
				$count_text = "Statement from " . request()->from_dates . " to " . request()->to_dates;
			}
		}
		elseif ($filter_type == "daily") {
			$order = $order->where('created_at', '>=', Carbon\Carbon::today());
			$count_text = "Today Statement - " . date('d M Y');
		} 
		elseif ($filter_type == "weekly") {
			$fromDate = Carbon\Carbon::now()->subDay()->startOfWeek()->toDateString();
			$tillDate = Carbon\Carbon::now()->subDay()->endOfWeek()->toDateString();
			$order = $order->whereBetween(DB::raw('date(created_at)'), [$fromDate, $tillDate]);
			$count_text = "This Week Statement : " . $fromDate . " to " . $tillDate;
		}
		elseif ($filter_type == "monthly") {
			$order = $order->whereRaw('MONTH(created_at) = ?', [date('m')]);
			$count_text = "This Month Statement - " . date('F');
		}
		elseif ($filter_type == "yearly") {
			$order = $order->whereRaw('YEAR(created_at) = ?', [date('Y')]);
			$count_text = "This Year Statement - " . date('Y');
		}
		$ordermy = clone ($order);
		$total_order = $ordermy->count();
		$pending_order = $ordermy->where('status', 1)->count();
		$complete_order = $order->where('status', 6);
		$total_amount = $order->whereNotIn('status',[2,4])->sum('total_amount') + $order->sum('wallet_amount');
		$return_data['total_earning'] = html_entity_decode(currency_symbol()) . ' ' . $total_amount;
		$total_earning = $complete_order->sum('booking_fee') + $complete_order->sum('store_commision_fee') + $complete_order->sum('driver_commision_fee');
		$return_data['total_service_fee'] = html_entity_decode(currency_symbol()) . ' ' . $total_earning;

		$return_data['total_order'] 	= $total_order;
		$return_data['count_text'] 		= $count_text ?? '';
		$return_data['pending_order'] 	= $pending_order;

		return json_encode($return_data);
		
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function all_orders(Request $request)
	{
		$orders = new Order;
		$filter_type = $request->filter_type;
		if ($filter_type == "custom") {
			$from = date('Y-m-d' . ' 00:00:00', strtotime($request->from_dates));
			if ($request->has('to_dates')) {
				$to = date('Y-m-d' . ' 23:59:59', strtotime($request->to_dates));
				$orders = $orders->whereBetween('order.created_at', array($from, $to));
			}
		}
		elseif ($filter_type == "daily") {
			$orders = $orders->where('order.created_at', '>=', Carbon\Carbon::today());
		}
		elseif ($filter_type == "weekly") {
			$fromDate = Carbon\Carbon::now()->subDay()->startOfWeek()->toDateString();
			$tillDate = Carbon\Carbon::now()->subDay()->endOfWeek()->toDateString();
			$orders = $orders->whereBetween(DB::raw('date(order.created_at)'), [$fromDate, $tillDate]);
		}
		elseif ($filter_type == "monthly") {
			$orders = $orders->whereRaw('MONTH(order.created_at) = ?', [date('m')]);
		}
		elseif ($filter_type == "yearly") {
			$orders = $orders->whereRaw('YEAR(order.created_at) = ?', [date('Y')]);
		}
		
		$orders = $orders->join('user', function($join) {
                            $join->on('user.id', '=', 'order.user_id');
                    })
                    ->join('store', function($join) {
                            $join->on('store.id', '=', 'order.store_id');
                    })
                    ->join('service_type', function($join) {
                            $join->on('store.service_type', '=', 'service_type.id');
                    })
                    ->join('currency', function($join) {
                            $join->on('order.currency_code', '=', 'currency.code');
                    });
					

                    $default_currency =  DB::table('currency')->where('default_currency', '1')->first();
              		$orders->select('order.wallet_amount','order.id as id','order.store_id','order.payment_type','order.total_amount','order.status','user.name as user_name','store.name as store_name','store.service_type','service_type.service_name as store_service',DB::raw('CASE WHEN order.status = 0  THEN  "cart" WHEN order.status = 1  THEN  "pending" WHEN order.status = 2  THEN  "declined" WHEN order.status = 3  THEN  "accepted" WHEN order.status = 4  THEN  "cancelled" WHEN order.status = 5  THEN  "delivery" WHEN order.status = 6  THEN  "completed" WHEN order.status = 7  THEN  "expired" WHEN order.status = 8  THEN  "takeaway" ELSE "cart" END as status'),DB::raw('CASE 
									WHEN (order.total_amount > 0  and  order.wallet_amount > 0)   THEN  IF(order.payment_type>0,IF(order.payment_type=1,"Credit Card & Wallet","Paypal & Wallet"),"cash & Wallet")
									WHEN (order.total_amount = 0  and  order.wallet_amount > 0) THEN "Wallet"
								ELSE  IF(order.payment_type>0,IF(order.payment_type=1,"Credit Card","Paypal"),"cash")
								END as payment_type'),DB::raw('CONCAT("'.$default_currency->code.'", ROUND(((order.wallet_amount + order.total_amount) / currency.rate) * '.$default_currency->rate.',2)) as total'));
       
		$datatable = DataTables::of($orders)

			->addColumn('user_name', function ($orders) {
				$name = explode('~', $orders->user_name);
				if (count($name)) {
					return (count($name) > 1) ? $name[0]." ".$name[1] : $name[0];
				}
			})
			->addColumn('action', function ($orders) {
				return '<a title="' . trans('admin_messages.view') . '" href="' . route('admin.view_order', $orders->id) . '" ><i class="material-icons">edit</i></a>';
			});
			$datatable->filterColumn('status', function($query, $keyword) {
						
				$query->whereRaw('CASE 
									WHEN order.status = 1  THEN  "pending" LIKE "%'.$keyword.'%"
									WHEN order.status = 2  THEN  "declined" LIKE "%'.$keyword.'%"
									WHEN order.status = 3  THEN  "accepted" LIKE "%'.$keyword.'%"
									WHEN order.status = 4  THEN  "cancelled" LIKE "%'.$keyword.'%"
									WHEN order.status = 5  THEN  "delivery" LIKE "%'.$keyword.'%"
									WHEN order.status = 6  THEN  "completed" LIKE "%'.$keyword.'%"
									WHEN order.status = 7  THEN  "expired" LIKE "%'.$keyword.'%"
									WHEN order.status = 8  THEN  "takeaway" LIKE "%'.$keyword.'%"
								ELSE "cart" LIKE "%'.$keyword.'%"
								END'
							);
            });

			$datatable->filterColumn('payment_type', function($query, $keyword) {
						
				$query->whereRaw('CASE 
									WHEN (order.total_amount > 0  and  order.wallet_amount > 0)   THEN  IF(order.payment_type>0,IF(order.payment_type=1,"Credit Card & Wallet" LIKE "%'.$keyword.'%","Paypal & Wallet" LIKE "%'.$keyword.'%"),"cash & Wallet" LIKE "%'.$keyword.'%")
									WHEN (order.total_amount = 0  and  order.wallet_amount > 0) THEN "Wallet" LIKE "%'.$keyword.'%"
								ELSE  IF(order.payment_type>0,IF(order.payment_type=1,"Credit Card" LIKE "%'.$keyword.'%","Paypal" LIKE "%'.$keyword.'%"),"cash" LIKE "%'.$keyword.'%")
								END'
							);
            });
			$datatable->filterColumn('total', function($query, $keyword)use($default_currency) {
				$query->whereRaw('CONCAT("'.$default_currency->code.'", ROUND(((order.wallet_amount + order.total_amount) / currency.rate) * '.$default_currency->rate.',2))  LIKE "%'.$keyword.'%"');
            });
            $datatable->order(function ($query) {
					$filter_column = ['order.id', 'payment_type','user.name', 'store.name', 'service_type.service_name', 'total','status'];
					$column = $filter_column[request()->order[0]['column']];
					$query->orderBy($column,request()->order[0]['dir']);
                });

		$base = new EloquentDataTableBase($orders, $datatable, [], 'Orders');
		
		return $base->render(null);

	}

}
