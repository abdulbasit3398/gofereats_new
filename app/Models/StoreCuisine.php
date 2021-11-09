<?php

/**
 * StoreCuisine Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Store
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;
class StoreCuisine extends Model
{
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $table = 'store_cuisine';

    protected $appends = ['cuisine_name'];

    public $timestamps =false; 

    protected $guarded = [];

    // Join with cuisine table

    public function cuisine()
    {
        return $this->belongsTo('App\Models\Cuisine','cuisine_id','id')->where('status','1');
    }

  	public function getCuisineNameAttribute() {
        if(!is_null($this->cuisine))
        {
            return $this->cuisine->name;
        }
  	}
  

}
