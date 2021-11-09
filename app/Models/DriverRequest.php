<?php

/**
 * DriverRequest Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   DriverRequest
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTime;

class DriverRequest extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    use SoftDeletes;
    protected $table = 'request';

    protected $guarded = [];

    // protected $appends = ['accepted_count','pending_count','cancelled_count','total_count','date_time','total_fare','payment_status','currency_code','currency_symbol'];


    protected $appends = ['user_name','estimated_distance','order_item','is_picked','trip_status'];    
    protected $dates = ['deleted_at'];

    public $statusArray = [
        'pending'   => 0,
        'accepted'  => 1,
        'cancelled' => 2
    ];

    /**
     * To filter status
     */
    public function scopeStatus($query, $status = ['accepted'])
    {
        $array_status = array_map(
            function ($value) {
                return $this->statusArray[$value];
            },
            $status
        );
        return $query->whereIn('status', $array_status);
    }

    /**
     * To filter order
     */
    public function scopeOrderId($query, $order_id = [])
    {
        return $query->whereIn('order_id', $order_id);
    }

    /**
     * To filter groupId
     */
    public function scopeGroupId($query, $group_id = [])
    {
        return $query->whereIn('group_id', $group_id);
    }


    // Join with users table
    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    // Join with driver table
    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id', 'id');
    }

    // Join with profile_picture table
    public function profile_picture()
    {
        return $this->belongsTo('App\Models\ProfilePicture', 'user_id', 'user_id');
    }

    public function getStatusTextAttribute()
    {
        return array_search($this->status, $this->statusArray);
    }
    
    public function getAcceptedTripsAttribute()
    {
        $trips = $this->trips()->first();
        $accpted_request = Request::where('group_id', $this->attributes['group_id'])->where('status', 'Accepted')->first();
        if ($accpted_request) {
            $trips = Trips::where('request_id', $accpted_request->id)->first();
        }
        return $trips;
    }
    
    //get user Accepted count
    public function getAcceptedCountAttribute()
    {
        return Request::where('driver_id', $this->attributes['driver_id'])->where('status', 'Accepted')->count();
    }
    //get user Pending count
    public function getPendingCountAttribute()
    {
        return Request::where('driver_id', $this->attributes['driver_id'])->where('status', 'Pending')->count();
    }
    //get user Cancelled count
    public function getCancelledCountAttribute()
    {
        return Request::where('driver_id', $this->attributes['driver_id'])->where('status', 'Cancelled')->count();
    }
    
    //get user Total count
    public function getTotalCountAttribute()
    {
        return Request::where('driver_id', $this->attributes['driver_id'])->count();
    }

    //get trip total fare
    public function getTotalFareAttribute()
    {
        $trips= Trips::where('request_id', $this->attributes['id']);
        if ($trips->count()) {
            return number_format(($trips->get()->first()->total_fare), 2, '.', '');
        } else {
            return "N/A";
        }
    }

    //get trip payment status
    public function getPaymentStatusAttribute()
    {
        $trips= Trips::where('request_id', $this->attributes['id']);
        if ($trips->count()) {
            return @$trips->get()->first()->payment_status;
        } else {
            return "Not Paid";
        }
    }

    //get trip currency code
    public function getCurrencyCodeAttribute()
    {
        $trips= Trips::where('request_id', $this->attributes['id']);
        if ($trips->count()) {
            return  @$trips->get()->first()->currency_code;
        } else {
            return DEFAULT_CURRENCY;
        }
    }

    //get trip currency code
    public function getCurrencySymbolAttribute()
    {
        $trips= Trips::where('request_id', $this->attributes['id']);
        if ($trips->count()) {
            $code =  @$trips->get()->first()->currency_code;

            return Currency::where('code', $code)->first()->symbol;
        } else {
            return "$";
        }
    }

    public function getDateTimeAttribute()
    {
        $full = false;

        $now = new DateTime;
        $ago = new DateTime($this->attributes['updated_at']);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    //get Eater Name
    public function getUserNameAttribute()
    {   
        $order=Order::where('id',$this->attributes['order_id'])->first();
        return $order->user->first_name.' '.$order->user->last_name;
    } 

    //get Distance between eater and driver
    public function getEstimatedDistanceAttribute()
    {
        $driver=Driver::where('id',$this->attributes['driver_id'])->first();
        $driver_user=User::where('id',$driver['user_id'])->first();
        $drop_latitude  = $this->attributes['drop_latitude'] ? $this->attributes['drop_latitude'] : 0;
        $drop_longitude = $this->attributes['drop_longitude'] ? $this->attributes['drop_longitude'] : 0;
        $distance=$this->distance_km($driver->latitude,$driver->longitude,$drop_latitude,$drop_longitude);
        return number_format($distance,2);
    }

    public function distance_km($lat1, $lon1, $lat2, $lon2) { 
        $pi80 = M_PI / 180; 
        $lat1 *= $pi80; 
        $lon1 *= $pi80; 
        $lat2 *= $pi80; 
        $lon2 *= $pi80; 
        $r = 6372.797; // mean radius of Earth in km 
        $dlat = $lat2 - $lat1; 
        $dlon = $lon2 - $lon1; 
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2); 
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
        $km = $r * $c;      
        return $km; 
    }

    public function getOrderItemAttribute()
    {
        $order=Order::where('id',$this->attributes['order_id'])->first();
        $order_items = $order->order_item->map(
                function ($order_item) {
                    if($order_item->menu_item)
                    {
                        return [
                            "id" => $order_item->menu_item->id,
                            "name" => $order_item->menu_item->name,
                            "quantity" => $order_item->quantity,
                        ];
                    }
                }
            );
        return $order_items;
    }

    public function getDeliveryConfirmedAttribute()
    {
       $delivery=OrderDelivery::where('order_id', $this->attributes['order_id'])->where('driver_id',$this->attributes['driver_id'])->where('request_id',$this->attributes['request_id'])->where('status',1)->first();
       if($delivery)
        return 1;
       else
        return 0;
    }

    public function getIsPickedAttribute()
    {
       $delivery=OrderDelivery::where('order_id', $this->attributes['order_id'])->where('driver_id',$this->attributes['driver_id'])->where('request_id',@$this->attributes['id'])->where('status',1)->first();
       if($delivery)
        return '1';
       else
        return '0';
    }

    public function getTripStatusAttribute()
    {
       $delivery=OrderDelivery::where('order_id', $this->attributes['order_id'])->where('driver_id',$this->attributes['driver_id'])->where('request_id',@$this->attributes['id'])->first();
       if($delivery)      
        return @$delivery->status;
       else
        return '6';       
    }
    
}
