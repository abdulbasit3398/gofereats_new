<?php

/**
 * Payment Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Payment
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

  use CurrencyConversion;
  protected $table = 'payment';
  protected $convert_fields = ['amount'];
  public $timestamps = true;
 
  protected $guarded = [];
  
  public $Type = [

        'user_order'      => 0,
        'user_wallet'   => 1,
        'driver_admin'  => 2,
        
    ];
 


}
