<?php

/**
 * FoodReceiver Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   FoodReceiver
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Request;
use Session;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App;

class FoodReceiver extends Model {

	public $translatedAttributes = ['name'];
	public $timestamps = false;
	use Translatable;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if(Request::segment(1) == 'admin') {
            $this->defaultLocale = 'en';
        }
        else {
            $this->defaultLocale = Session::get('language');
        }
    }

    public function getNameLangAttribute()
    {
      $lan = App::getLocale();
      if($lan=='en')
        return $this->attributes['name'];
      else{ 
         $get = FoodReceiverTranslations::where('food_receiver_id', $foo_reciver)->where('locale',$lan)->first();
         if($get)
          return $get->name;
        else
          return $this->attributes['name'];
      }
    }


    public function getRecipIdAttribute()
    {
      $lan = App::getLocale();
      if($lan=='en')
      {
        $name_food = FoodReceiver::where('id',$this->attributes['id'])->get();
        return $name_food[0]->name_lang;
      }
      else{ 
        $get = FoodReceiverTranslations::where('food_receiver_id',$this->attributes['id'])->where('locale',$lan)->first();
         if($get)
          return $get->name;
        else
          $name_food = FoodReceiver::where('id',$this->attributes['id'])->get();
          return $name_food[0]->name_lang;
      }
    }

    public function getNameAttribute()
    {
      $lan = App::getLocale();
      if($lan=='en')
        return $this->attributes['name'];
      else{ 
         $foo_reciver = FoodReceiver::where('name',$this->attributes['name'])->first()->value('id');
         $get = FoodReceiverTranslations::where('food_receiver_id',$foo_reciver)->where('locale',$lan)->first();
         if($get)
          return $get->name;
        else
          return $this->attributes['name'];
      }
    }
	protected $table = 'food_receiver';


}
