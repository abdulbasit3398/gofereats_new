<?php

/**
 * MenuItemModifier Translations Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   MenuItemModifier Translations
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemModifierTranslations extends Model
{    
    protected $table = 'menu_item_modifier_translations';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function language()
    {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
