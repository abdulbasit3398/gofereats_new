<?php

/**
 * SearchController
 *
 * @package     GoferEats
 * @subpackage  Controller
 * @category    Search
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\Store;
use App\Models\ServiceType;
use Session;
use View;
use App;

class SearchController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	// index data

	public function index()
	{
		
		$this->view_data['service_type'] = request()->service_type ?? session('service_type');
		$this->view_data['user_details'] = auth()->guard('web')->user();
		$this->view_data['top_category_data'] = Cuisine::Active()->where('is_top', 1)->where('service_type',$this->view_data['service_type'])->get();
		$this->view_data['cuisine_data'] = Cuisine::Active()
			->where(function($q){
				$q->where('is_top', 0)->orWhereNull('is_top');
			})
			->where('service_type',$this->view_data['service_type'])
			->get();

		$this->view_data['request_cat'] = request()->q;

		session::forget('password_code');
		if (session::get('schedule_data') == null) {
			$schedule_data = array('status' => 'ASAP', 'date' => '', 'time' => '');
			session::put('schedule_data', $schedule_data);
		}
		$address = $this->address_details();
		if (session('location')) {
			return redirect()->route('feeds');
		}
		return redirect()->route('newhome');
	}

	// session store data function
	public function store_location_data(Request $request)
	{	
		if(!$request->city)
			$location = json_decode($request->location);
		else
			$location = $request;
		$service_type = $request->service_type ?? session('service_type');
		session()->put('locate', $request->location);
		$this->view_data['postal_code'] = $location->postal_code ?? session('postal_code');
		$this->view_data['locality'] = $location->locality ?? session('locality');
		$this->view_data['city'] = $location->city ?? session('city');
		$this->view_data['latitude'] = $location->latitude ?? session('latitude');
		$this->view_data['longitude'] = $location->longitude ?? session('longitude');
		$this->view_data['location'] = $location->location ? $location->location : session('location');
		$this->view_data['show_location'] = str_replace('+', ' ', $location->location);
		session::put('city', $this->view_data['city']);
		$this->view_data['categoryies'] =  Cuisine::select('id','name')->Active()->where(function($q){
				$q->where('is_top', 0)->orWhereNull('is_top');
			})->where('service_type',$service_type)
			->get()->toArray();
		$user_id = auth()->guard('web')->user();
		if ($user_id) {
			UserAddress::where('user_id', $user_id->id)->update(['default' => 0]);
			$user_address = UserAddress::where('user_id', $user_id->id)->where('type',2)->first();
			if (!$user_address) {
				$user_address = new UserAddress;
				$user_address->user_id = $user_id->id;
				$user_address->type = 2;
			}
			$user_address->default = 1;
			$country_name = '';
			if ($location->country) {
				$country_name = Country::where('code', $location->country)->first()->name;
			}
			$address = ($this->view_data['location'] != '') ? $this->view_data['location'] : $this->view_data['city'];
			$user_address->address = $address;
			$user_address->street = $location->address1;
			$user_address->first_address = $address;
			$user_address->second_address = $location->address1;
			if(isset($location->city))
				$user_address->city = $location->city;
			if($location->state)
			$user_address->state = $location->state;
			$user_address->country = ($country_name) ? $country_name : $location->country;
			$user_address->country_code = $location->country;
			$user_address->postal_code = $this->view_data['postal_code'];
			$user_address->latitude = $this->view_data['latitude'];
			$user_address->longitude = $this->view_data['longitude'];
			$user_address->save();
		}
		if($request->order_id) {
			$order  	= Order::with('store.user_address')->find($request->order_id);
			$store_address = optional($order->store)->user_address;
			if($store_address != '') {
				if (site_setting('delivery_fee_type') == 0) {
					$order->delivery_fee =( $request->delivery_type == 'takeaway') ? 0 : site_setting('delivery_fee');
				} else {
					list($pickup_fare,$drop_fare,$distance_fare,$delivery_fee) = get_delivery_fee($order->store->user_address->latitude, $order->store->user_address->longitude, $user_id->currency_code->code);
					$order->delivery_fee = ( $request->delivery_type == 'takeaway') ? 0 : $delivery_fee;
				}
				$order->save();
				$this->view_data['delivery_fee'] = $order->delivery_fee ;
			}
			$this->view_data['promo_amount'] = promo_calculation($request->delivery_type);
		}
		$service_type = $request->service_type ?? session('service_type');
		session()->put('locality', $this->view_data['locality']);
		session()->put('city', $this->view_data['city']);
		session()->put('address1', $location->address1);
		session()->put('state', $location->state);
		session()->put('country', $location->country);
		session()->put('location', $this->view_data['location']);
		session()->put('postal_code', $this->view_data['postal_code']);
		session()->put('latitude', $this->view_data['latitude']);
		session()->put('longitude', $this->view_data['longitude']);
		session()->put('service_type',$service_type);
		session()->save();
		if(isset($order))
			$this->view_data['order_detail_data'] = get_user_order_details($order->store_id, $order->user_id,$request->delivery_type);	
		return json_encode(['success' => 'true', 'data' => $this->view_data]);
	}

	//search based on top category
	
	public function search_result(Request $request)
	{	
		$delivery_type = $request->delivery_type ?? 'both';
		$category_id = $request->category ?? '';
		$user_details = auth()->guard('web')->user();
		$langugae =  App::getLocale() ?? $user_details->langugae;
		$user_details = auth()->guard('web')->user();
		$address_details = $this->address_details();
		return store_search($user_details, $address_details, $request->keyword,	session('service_type'), $delivery_type, $langugae ,$request->page,$category_id);
	}
	
	//search based on top category

	public function search_data(Request $request) 
	{

		if($request->delivery_type == ''){
			$deliveryType = 'both';
		}
		else
			$deliveryType = $request->delivery_type;
		$category_id = $request->category ?? '';
		$address_details = $this->address_details();
		$user_details = auth()->guard('web')->user();
		$langugae =  App::getLocale() ?? $user_details->langugae;
		$address_details = $this->address_details();
		return store_search($user_details, $address_details, $request->keyword,$request->service_type,$deliveryType,$langugae ,$request->page,$category_id);
	}

	public function schedule_store() {
		$status = request()->status;
		$date = request()->date;
		$time = request()->time;
		logger("status".$status);
		if ($status == "ASAP") {
			$schedule_data = array('status' => $status, 'date' => '', 'time' => '');
			$schedule_data1 = array('status' => $status, 'date' => '', 'time' => '','date_time' => '');
			$order_type = 0; //ASAP
		} else {
			$schedule_data = array('status' => $status, 'date' => $date, 'time' => $time);
			$schedule_data1 = array('status' => $status, 'date' => date('d M', strtotime($date)), 'time' => date('h:i A', strtotime($time)),'date_time' => $date.' '.$time);
			$order_type = 1; //schedule
		}

		//update delivery option
		if (get_current_login_user_id()) {
			$user_address = UserAddress::where('user_id', get_current_login_user_id())->where('default', 1)->first();
			if ($user_address) {
				$user_address->order_type = $order_type;
				$user_address->delivery_time = $schedule_data['date'] . ' ' . $schedule_data['time'];
				$user_address->save();
			}
		}
		session::put('schedule_data', $schedule_data);

		return json_encode(['schedule_data' => $schedule_data1]);
	}

	/**
	 * Default user address
	 */

	public function address_details() {

		return user_address_details();
	}

}
