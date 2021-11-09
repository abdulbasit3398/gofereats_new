<?php

/**
 * Menu Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   Menu
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
	// use Translatable;

	 use CurrencyConversion, Translatable {
        Translatable::attributesToArray insteadof CurrencyConversion;
        Translatable::getAttribute insteadof CurrencyConversion;        
    }
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'menu';

	public $translatedAttributes = ['name'];

	protected $appends = ['menu_start_time', 'menu_end_time', 'menu_time', 'menu_closed','menu_closed_status'];

	protected $guarded = [];

	public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if(request()->segment(1) == 'admin' || request()->segment(1) == 'store') {
            $this->defaultLocale = 'en';
        }
        else {
            $this->defaultLocale = session('language');
        }
    }

    // Get store value based category and item
    public function scopeGetMenu($query)
    {
    	$query->whereHas('menu_category',function($query){
			$query->whereHas('menu_item', function($query){ });
		 });

		return $query;
    }


	public function scopeMenuRelations($query)
	{		
		return $query->with([
			'menu_category' => function ($query) {
				$query->menuRelations();
			},
		]);
	}

	//Get store details based on given menu id or get first record
	public function scopestoreDetail($query) {
		if (request()->menu_id) {
			return $query->has('menu_category.menu_item')->where('id',request()->menu_id);	
		}else{
			return $query->has('menu_category.menu_item')->first();	
		}
	}

	public function scopeStore($query, $store_id)
	{
		return $query->where('store_id', $store_id);
	}

	public function getMenuTimeAttribute()
	{
		$menu_time = $this->menu_time();
		if ($menu_time) {
			return time_format($menu_time->start_time) . ' - ' . time_format($menu_time->end_time);
		}
		else if ($menu_time_atleast = $this->menu_time_atleast()) { //check atleast one time add for menu
			return $menu_time_atleast;
		} 
		else if ($store_time = $this->StoreTime()) {
			return time_format($store_time->start_time_for_english) . ' - ' . time_format($store_time->end_time_for_english);
		}

		return '';
	}

	public function getMenuStartTimeAttribute()
	{
		$menu_time = $this->menu_time();

		if ($menu_time) {
			return time_format($menu_time->start_time);
		}
		else if ($this->menu_time_atleast()) { //check atleast one time add for menu
			return '';
		}
		else if ($store_time = $this->StoreTime()) {
			return time_format($store_time->start_time_for_english);
		}

		return '';
	}

	public function getMenuEndTimeAttribute()
	{
		$menu_time = $this->menu_time();

		if ($menu_time) {
			return time_format($menu_time->end_time);
		}
		else if ($this->menu_time_atleast()) { //check atleast one time add for menu
			return '';
		}
		else if ($store_time = $this->StoreTime()) {
			return time_format($store_time->end_time_for_english);
		}

		return '';
	}

	public function StoreTime()
	{
		$date = $this->menu_available_time();
		$day = date('N', $date);

		$store_time = StoreTime::where('day', $day)->where('store_id', $this->attributes['store_id'])->where('status', '1')->first();

		if ($store_time) {
			return $store_time;
		}
		return '';
	}

	public function getMenuClosedAttribute()
	{
		$menu_time = $this->menu_time(); //menu  available time
		
		if ($menu_time) {
			$time = $this->menu_available_time(); //User search time
			if ($time >= strtotime($menu_time->start_time) && $time <= strtotime($menu_time->end_time)) {
				return 1;
			}
			return 0;
		}
		if ($this->menu_time_atleast()) { //check at least one time add for menu
			return 0;
		}
		if ($this->StoreTime()) {
			$store_time = $this->StoreTime(); //store opening time
			if ($store_time->closed == 1) {
				return 1;
			}
			return 0;
		}
		return 0;
	}

	//menu_closed_status
	public function getMenuClosedStatusAttribute()
	{

		$menu_time = $this->menu_time(); //menu  available time		
		if ($menu_time) {
			$time = $this->menu_available_time(); //User search time
			if ($time >= strtotime($menu_time->start_time) && $time <= strtotime($menu_time->end_time)) {
				return 'Available';
			}
			return 'Un Available';
		}
		if ($this->menu_time_atleast()) {
		 //check at least one time add for menu
			return 'Un Available';
		}

		if ($this->StoreTime()) {
			$store_time = $this->StoreTime(); //store opening time
			if ($store_time->closed == 1) {
				return 'Available';
			}
			return 'Un Available';
		}
		return 'Closed';
	}

	public function menu_available_time()
	{
		$schedule_data = session('schedule_data');
		$user = User::where('id', get_current_login_user_id())->first();
		if (get_current_login_user_id() && isset($user->user_address)) {
			list('order_type' => $order_type, 'delivery_time' => $delivery_time) =
			collect($user->user_address)->only(['order_type', 'delivery_time'])->toArray();

			if ($order_type == 0) {
				return time();
			}

			return strtotime($delivery_time);

		}
		else {
			if ($schedule_data['status'] == 'Schedule') {
				return strtotime($schedule_data['date'] . ' ' . $schedule_data['time']);
			}
			return time();
		}
	}

	// Join with Menu table
	public function menu_category()
	{
		return $this->hasMany('App\Models\MenuCategory', 'menu_id', 'id');
	}

	// Join with Menu  Time table

	public function menu_time()
	{
		$date = $this->menu_available_time();
		$day = date('N', $date);
		return $this->belongsTo('App\Models\MenuTime', 'id', 'menu_id')->where('day', $day)->first();
	}
	// Join with Menu  Time table

	public function menu_time_atleast()
	{
		$menu = $this->hasMany('App\Models\MenuTime', 'menu_id', 'id')->orderBy('day', 'ASC')->get();

		if (count($menu) > 0) {
			$user_id = get_current_login_user_id();
			$address = get_user_address($user_id);

			if (isset($address) && $address->order_type == 1) {
				$date = strtotime($address->delivery_time);
			}
			else {
				$date = time();
			}
			$cur_Date = date('N', $date);
			foreach ($menu as $key => $opening) {
				if ($cur_Date <= $opening->day) {
					return trans('api_messages.menu.available_on') . day_name($opening->day);
				}
			}
			$day = $menu[0]->day;
			return trans('api_messages.menu.available_on') . day_name($day);
		}
		return;
	}

	// Join with Menu  Item table

	public function menu_item()
	{
		return $this->hasMany('App\Models\MenuItem', 'menu_id', 'id')->where('status', 1);
	}

	// Join with All Menu Item table

	public function all_menu_item()
	{
		return $this->hasMany('App\Models\MenuItem', 'menu_id', 'id');
	}

	public function menu_item_main_addon()
	{
		return $this->hasMany('App\Models\MenuItemModifier', 'menu_item_id', 'id');
	}

	// Get Translated value of given column
	protected function getTranslatedValue($field)
	{
		if(!isset($this->attributes[$field])) {
			return '';
		}
		$value = $this->attributes[$field];

		$lang_code = getLangCode();
		if ($lang_code == 'en') {
			return $value;
		}
		$trans_value = @MenuTranslations::where('menu_id',$this->attributes['id'])->where('locale',$lang_code)->first()->$field;
		if ($trans_value) {
			return $trans_value;
		}
		return $value;
	}

	// menu_name_lang
    public function getMenuNameLangAttribute()
    {
    	return $this->getTranslatedValue('name');
    }

    public function getCategoryIdsAttribute()
    {
    	$this->load('menu_category');
        return $this->menu_category->pluck('id');
    }
}
