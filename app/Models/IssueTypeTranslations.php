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

class IssueTypeTranslations extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'issue_type_lang';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function language() {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
