<?php

/**
 * HelpCategoryLang Us Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    HelpCategoryLang Us
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class CuisineTranslations extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cuisine_lang';

    public $timestamps = false;

    protected $fillable = ['name','description'];

    public function language() {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
