<?php

/**
 * MenuItemTranslations Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    MenuItemTranslations
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class MenuItemTranslations extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'menu_item_lang';

    public $timestamps = false;

    protected $fillable = ['name','description'];

    public function language()
    {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
