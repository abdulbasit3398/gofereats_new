<?php

/**
 * StoreOffer Model
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

class StoreOffer extends Model
{
    protected $table = 'store_offer';

    public function scopeActiveOffer($query)
    {
    	$date = \Carbon\Carbon::today();
        return $query->where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('status', '1')->where('percentage', '>', '0')->orderBy('id' , 'DESC');
    }
}