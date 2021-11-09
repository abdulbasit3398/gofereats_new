<?php

/**
 * OrderItem Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   OrderItem
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model {

	use CurrencyConversion;

	protected $guarded = [];

	protected $table = 'order_item';
	protected $convert_fields = ['price','total_amount','tax'];
	public $timestamps = false;
	public $appends = ['offer_price'];

	// Join with Menu table
	public function menu_item() {
		return $this->belongsTo('App\Models\MenuItem', 'menu_item_id', 'id');
	}

	public function menu_item_name() {
		return $this->belongsTo('App\Models\MenuItem', 'menu_item_id', 'id');
	}

	// Join with order table
	public function order() {
		return $this->belongsTo('App\Models\Order', 'order_id', 'id');
	}
	// Join with review table
	public function review() {

		return $this->belongsTo('App\Models\Review', 'id', 'order_item_id')->where('order_id', $this->order_id);
	}

	// Join with OrderItemModifier table
	public function order_item_modifier() {
		return $this->hasMany('App\Models\OrderItemModifier', 'order_item_id', 'id');
	}

    // Join with OrderItemModifierItem table
    public function order_item_modifier_item()
    {
        return $this->hasMany('App\Models\OrderItemModifierItem', 'order_item_modifier_id', 'id');
    }

	public function getTotalAmountAttribute()
	{
		$order = Order::find($this->attributes['order_id']);
		if ($order) {
			// $offer = $order->offer_percentage;
			$offer = 0;
			if ($offer != 0) {
				return $this->attributes['total_amount'] - (($this->attributes['total_amount'] - $this->attributes['modifier_price']) * $offer / 100);
			}
		}

		return $this->attributes['total_amount'];
	}

	public function getOfferPriceAttribute() {

		$order = Order::find($this->attributes['order_id']);
		if ($order) {
			$offer = $order->offer_percentage;
			if ($offer != 0) {
				 if(request()->segment(1) == 'api') {
					return $this->attributes['price'];
				 }
				return $this->attributes['price'] - ($this->attributes['price'] * $offer / 100);
			}
			return 0;
		}
		return 0;
	}

	

}
