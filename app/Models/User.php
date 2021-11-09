<?php

/**
 * User Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   User
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use App\Models\Country;
use App\Models\Wallet;
use Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
	protected $table = 'user';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password','auth_type','apple_id','google_id'];

	protected $appends = ['users_image', 'status_text', 'user_image_url', 'wallet_amount', 'wallet_currency','type_text'];

	public $typeArray = [
		'user' => 0,
		'store' => 1,
		'driver' => 2,
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	public $statusArray = [
		'inactive' => 0,
		'active' => 1,
		'vehicle_details' => 2,
		'document_details' => 3,
		'pending' => 4,
		'waiting for approval' => 5,
	];

	public $statusTextArray = [
		0 => 'Inactive',
		1 => 'Active',
		2 => 'Vehicle Details',
		3 => 'Document Upload',
		4 => 'Pending',
		5 => 'Waiting for Approval',
	];

	// JWT Auth Functions Start
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }

    
    // JWT Auth Functions End

	public function delete_data()
 	{
 		try {
	 		if($this->order) {
	 			$this->order->each(function($order){
	 				try {
						\DB::beginTransaction();
						$order_items = OrderItem::where('order_id', $order->id)->get();

						$order_item_modifiers = OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->get();
						
						if($order_item_modifiers->count()) {
							$order_item_modifier_items = OrderItemModifierItem::whereIn('order_item_modifier_id',$order_item_modifiers->pluck('id')->toArray())->delete();
						}

						OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->delete();
						\DB::commit();
					}
					catch (\Exception $e) {
						\DB::rollback();
					}
		            $order->order_item()->forcedelete();
		            $order->order_delivery()->forcedelete();
		            $order->forcedelete();
		        });
	 		}
	 		$this->user_payment_method()->forcedelete();

	 		if($this->user_address) {
		        $this->user_all_address()->forcedelete();
		    }
	        $this->forcedelete();
	        return true;
	    }
	    catch(\Exception $e) {
	    	return false;
	    }
    }

	public function delete_driver_data()
 	{
 		if($this->user_all_address->count()) {
	       	$this->user_all_address()->forcedelete();
	    }
 		$this->user_payment_method()->forcedelete();

 		if($this->driver->driver_cancel_history()->get()->count()) {
       		$this->driver->driver_cancel_history()->forcedelete();
 		}
 		
 		if($this->driver->driver_request()->withTrashed()->get()->count()) {
       		$this->driver->driver_request()->withTrashed()->forcedelete();
 		}
       	
       	if($this->driver) {
       		$this->driver->forcedelete();
       	}
        $this->forcedelete();
    }

    /**
	 * To check Email or mobile number already Exisits
	 */
	public function scopeValidateUser($query, $type, $country_code, $mobile_number, $email = '') {
		return $query->where(function($query) use($type, $country_code, $mobile_number, $email) {
			if($email != '') {
				// dd("fghfgh");
				$query->where('type',$type)
				->where(function($query) use($country_code,$mobile_number, $email) {
					$query->where('country_code', $country_code)->where('mobile_number', $mobile_number)->Orwhere('email', $email);
				});
			}
			else {
				$query->where('type',$type)->where('country_code', $country_code)->where('mobile_number', $mobile_number);
			}
		});
	}




	public function scopeAuth($query)
	{
		$user_id = JWTAuth::parseToken()->authenticate()->id;
		return $query->where('id', $user_id);
	}

	/**
	 * To check the status
	 */
	public function scopeStatus($query, $status = 'active') {
		$status_value = $this->statusArray[$status];
		return $query->where('status', $status_value);
	}

	public function getStatusTextAttribute() {
		return array_search($this->status, $this->statusArray);
	}
	//status_text_show
	public function getStatusTextShowAttribute() {
		return $this->statusTextArray[$this->status];
	}

	//type_text
	public function getTypeTextAttribute() {
		return array_search($this->type, $this->typeArray);
	}

	/**
	 * To check the type
	 */
	public function scopeType($query, $status = 'user') {
		$type_value = $this->typeArray[$status];

		return $query->where('type', $type_value);
	}

	/**
	 * To check the type
	 */
	public static function getType($type) {
		$user = new User;
		$type_value = array_flip($user->typeArray);
		return $type_value[$type];
	}

	// Join with store table

	public function store() {
		return $this->belongsTo('App\Models\Store', 'id', 'user_id');
	}



	// Join with penalty table

	public function penalty() {
		return $this->belongsTo('App\Models\Penality', 'id', 'user_id');
	}

	// Join with driver table

	public function driver() {
		return $this->belongsTo('App\Models\Driver', 'id', 'user_id');
	}
	public function payout_preference() {
		return $this->belongsTo('App\Models\PayoutPreference', 'id', 'user_id');
	}
	public function default_payout_preference() {
		return $this->belongsTo('App\Models\PayoutPreference', 'id', 'user_id')->where('default', 'yes');
	}

	public function payout() {
		return $this->hasMany('App\Models\Payout', 'user_id', 'id');
	}

	public function getUsersImageAttribute() {
		return $this->user_image_url;
	}
	//store_id
	public function getStoreIdAttribute() {
		return $this->store()->first()->id;
	}

	//name
	public function getNameAttribute()
	{
		$name = explode('~', $this->attributes['name']);
		if (count($name)) {
			return (count($name) > 1) ? $name[0]." ".$name[1] : $name[0];
		}
	}


	public function getFirstNameAttribute()
	{
		$name = explode('~', $this->attributes['name']);
		if (count($name)) {
			return html_entity_decode($name[0]);
		}
	}

	//last_name
	public function getLastNameAttribute()
	{
		$name = explode('~', $this->attributes['name']);
		if (count($name) > 1) {
			return html_entity_decode($name[1]);
		}
	}

	//Email restrictions 
	public function getEmailAttribute() {
		if (in_array(request()->segment(1),['admin'])) {
		    $local = env("APP_ENV"); 
			if($local=="live")
			return substr($this->attributes['email'], 0, 1) . '****' . substr($this->attributes['email'],  -4);
			else
			return $this->attributes['email'];
		}else{
			return $this->attributes['email'];
		}
	}

	//phone number restrictions 
	public function getMobileNumberAttribute() {
		if (in_array(request()->segment(1),['admin'])) {
			$local = env("APP_ENV"); 
			if($local=="live")
				return substr($this->attributes['mobile_number'], 0, 1) . '****' . substr($this->attributes['mobile_number'],  -4);
			else
				return $this->attributes['mobile_number'];
		}else{
			return $this->attributes['mobile_number'];
		}
	}

	public function user_image() {
		if($this->attributes['type'] !=2)
		{	
			if($this->attributes['auth_type'] && $this->attributes['auth_type'] != 'mobile_number')
			{
				return $this->belongsTo('App\Models\File', 'id', 'source_id')->type('social_login');	
			}
			else
			{
				return $this->belongsTo('App\Models\File', 'id', 'source_id')->type('user_image');
			}
		}
		return $this->belongsTo('App\Models\File', 'id', 'source_id')->type('driver_image');
	}


	public function user_social_image() {
		return $this->belongsTo('App\Models\File', 'id', 'source_id')->type('social_login');
	}

	// get date of birth
	public function getDobArrayAttribute() {
		$dob_array = explode('-', @$this->attributes['date_of_birth']);
		return $dob_array;
	}

	public function getDateOfBirthAttribute() {
		if(request()->segment(1) == 'api'){
			if(is_null($this->attributes['date_of_birth'])){
				$dob = '';
			}else{
				$dob = date("d-m-Y",strtotime($this->attributes['date_of_birth']));
			}
		}else{
			$dob = @$this->attributes['date_of_birth'];
		}
		return $dob;
	}


	public function getUserImageUrlAttribute() {
		if(!isset($this->attributes['auth_type']))
			return '';
		if($this->attributes['auth_type'] && $this->attributes['auth_type'] != 'mobile_number')
		{
			return $this->user_social_image->image_social_name ?? getEmptyUserImageUrl() ;
		}
		else 
		{
			return $this->user_image->image_name ?? getEmptyUserImageUrl();
		}
		return getEmptyUserImageUrl();
	}

	public function getWalletAmountAttribute() {
		$wallet = $this->wallet()->first();

		if ($wallet) {
			return $wallet->amount;
		}
		return 0;
	}
	public function getPayoutIdAttribute() {
		$payout = $this->payout_preference()->where('default', 'yes')->first();

		if ($payout) {
			return $payout->paypal_email;
		}

		return '';

	}

	public function getWalletCurrencyAttribute() {
		$wallet = $this->wallet()->first();
		if ($wallet) {
			return $wallet->currency_code;
		}
		return DEFAULT_CURRENCY;
	}

	//store_total_paid_amount
	public function getTotalPaidAmountAttribute() {
		$amount = $this->payout()->whereStatus(1)->get();

		if (count($amount) > 0) {
			return $amount->sum('amount');
		}

		return '0';

	}
	//total_earnings_amount
	public function getTotalEarningsAmountAttribute() {

		$amount = Payout::where('user_id', $this->attributes['id'])->with('order')
			->whereHas('order', function ($query) {
				$query->history();
			})->get();
		if (count($amount) > 0) {
			return $amount->sum('amount');
		}

		return '0';

	}

	public function wallet() {
		return $this->belongsTo('App\Models\Wallet', 'id', 'user_id');
	}
	public function user_payment_method() {
		return $this->belongsTo('App\Models\UserPaymentMethod', 'id', 'user_id');
	}
	public function user_address() {
		return $this->belongsTo('App\Models\UserAddress', 'id', 'user_id')->default();
	}
	public function user_all_address() {
		return $this->hasMany('App\Models\UserAddress', 'user_id', 'id');
	}
	public function order() {
		return $this->hasMany('App\Models\Order', 'user_id', 'id');
	}

	public function scopeLocation($query) {

		$user_id = @Auth::user()->id;
		$user_city = UserAddress::where('user_id', $user_id)->where('default', 1)->first()->city;

		return $query->whereHas(
			'user_address',
			function ($query) use ($user_city) {
				$query->where('city', $user_city);
			}
		);
	}

	public function getCurrencyCodeAttribute($currency_code) {
        $currency = Currency::where('code', $currency_code)->first();
        if(!$currency) {
            $currency = Currency::defaultCurrency()->first();
        }
        return $currency;
	}

	public function country() {
		return $this->belongsTo('App\Models\Country', 'country_code', 'id');
	}

	public static function user_facebook_authenticate($email, $fb_id){
        $user = User::where(function($query) use($email, $fb_id){
            $query->where('email', $email)->where('type',0)->orWhere('facebook_id', $fb_id);
        });
        return $user;
    }


    public function getCountryCodeAttribute($currency_code) {
    	if(isApiRequest())
    	{
    		if($this->attributes['type'] != 1)
    			$country_code = Country::whereid($this->attributes['country_id'])->value('code');
    		else
    		 	$country_code=$this->attributes['country_code']	;
    		return $country_code;
    	}
        return $this->attributes['country_code'];
	}


	public function getMobileNumberPhoneCodeAttribute()
	{
		if(isApiRequest())
    	{
    		$phone_code = Country::where('id',$this->attributes['country_id'])->first();
    		if(isset($phone_code->phone_code))
    		    return "+".$phone_code->phone_code." ". $this->attributes['mobile_number'];
    		else
    			return "+1 " .$this->attributes['mobile_number'];
    	}
		else if (in_array(request()->segment(1),['admin'])) {
			$local = env("APP_ENV"); 
			if($local=="live")
				return substr($this->attributes['mobile_number'], 0, 1) . '****' . substr($this->attributes['mobile_number'],  -4);
			else
				return $this->attributes['mobile_number'];
		}else{
			return $this->attributes['mobile_number'];
		}
	}


	public function scopeValidateUserEmail($query, $type, $email) {
		return $query->where(function($query) use($type, $email) {
			$query->where('type',$type)->where('email', $email);
		});
	}
}
