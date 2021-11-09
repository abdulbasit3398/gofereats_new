<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOweAmount extends Model
{
    //
    use CurrencyConversion;

    protected $convert_fields = ['amount'];

    protected $table = 'store_owe_amount';

    protected $fillable = ['user_id', 'amount','currency_code'];
    
    public $timestamps = false;

    public function getAmountAttribute()
    {
        return number_format(($this->attributes['amount']),2,'.',''); 
    }

//paid_amount
    public function getPaidAmountAttribute()
    {
    	$amount = $this->payment()->get()->sum('amount');
        return  number_format(($amount),2,'.',''); 
    }
//driver_name
    public function getNameAttribute()
    {
    	return $this->user()->first()->name;
    }

    // Join with Menu table
    public function payment()
    {
        return $this->hasMany('App\Models\Payment', 'user_id', 'user_id');
    }
	
	public function user() {
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}


}
