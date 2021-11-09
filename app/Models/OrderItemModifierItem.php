<?php

/**
 * OrderItemModifierItem Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   OrderItemModifierItem
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;

class OrderItemModifierItem extends Model
{
    use CurrencyConversion;

    protected $table = 'order_item_modifier_item';
    protected $convert_fields = ['price'];
    public $timestamps = false;
    protected $appends = ['name'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    // Join with OrderItemModifierItemItem table
    public function getNameAttribute()
    {
        return $this->modifier_item_name;
    }

    // Join with Menu table
    public function menu_item_modifier_item()
    {
        return $this->belongsTo('App\Models\MenuItemModifierItem', 'menu_item_modifier_item_id', 'id');
    }
}
