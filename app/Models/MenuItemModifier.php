<?php

/**
 * MenuItemModifier Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   MenuItemModifier
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemModifier extends Model
{
    // Change model translatable
     use CurrencyConversion, Translatable {
        Translatable::attributesToArray insteadof CurrencyConversion;
        Translatable::getAttribute insteadof CurrencyConversion;        
    }

    /**
     * Indicates Which attributes are translated.
     *
     * @var Array
     */
    public $translatedAttributes = ['name'];

    protected $table = 'menu_item_modifier';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $appends = ['count','is_selected'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getPriceAttribute() {
        return $this->currency_convert($this->attributes['currency_code'],'',$this->attributes['price']);
    }

    
    public function scopeMenuRelations($query)
    {
        return $query->with('menu_item_modifier_item');
    }

    public function scopeStore($query, $store_id)
    {
        return $query->with([
            'menu_item' => function ($query) use($store_id) {
                $query->store($store_id);
            }
        ])
        ->whereHas('menu_item', function ($query) use($store_id) {
            $query->store($store_id);
        });
    }

    public function menu_item()
    {
        return $this->belongsTo('App\Models\MenuItem', 'menu_item_id', 'id');
    }

    public function menu_item_sub_addon()
    {
        return $this->hasMany('App\Models\MenuItemModifierItem', 'menu_item_modifier_id', 'id');
    }

    public function menu_item_modifier_item()
    {
        return $this->hasMany('App\Models\MenuItemModifierItem', 'menu_item_modifier_id', 'id');
    }
    public function getCountAttribute()
    {
        if(request()->segment(1) != 'api') {
            return 0;
        }

        if(!request()->order_id) {
            if($this->max_count == 0 && $this->min_count == 0 && $this->is_required) {
                return 1;
            }
        }
        
        $order_items = OrderItem::with('order_item_modifier.order_item_modifier_item')->where('order_id',request()->order_id)->get();
       
        $count = 0;
        foreach ($order_items as $order_item) {
            $order_item_modifiers = $order_item->order_item_modifier->where('modifier_id',$this->id);
            $order_item_modifiers->each(function($order_item_modifier)  use (&$count){
                $count += $order_item_modifier->order_item_modifier_item->sum('count');
            });
        }

        return $count;
    }

    public function getIsSelectedAttribute()
    {
        if(request()->segment(1) != 'api') {
            return false;
        }

        if(!request()->order_id) {
            if($this->max_count == 0 && $this->min_count == 0 && $this->is_required) {
                return 1;
            }
        }

        $order_item = OrderItem::with('order_item_modifier')->where('order_id',request()->order_id)->get();
        $selected =0;
        foreach ($order_item as $key => $value) {
            $menu_item = $value->order_item_modifier;
            foreach ($menu_item as $key => $value) {
                if($this->id == $value->modifier_id) {
                    $selected = 1;
                }
            }
        }
        return $selected;
    }

    public function language_menuModifiers()
    {
        return $this->hasMany('App\Models\MenuItemModifierItemTranslations', 'menu_item_modifier_item_id', 'id');
    }
    
}
