<?php

/**
 * UserPaymentMethod Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    UserPaymentMethod
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    /**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

   	protected $table = 'user_payment_method';
}