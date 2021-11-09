<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Session;
use Request;
use App;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ServiceType extends Model
{
	use Translatable;
	protected $table = 'service_type';
	public $translatedAttributes = ['service_name','service_description'];
	public $translationModel = 'App\Models\ServiceTypeTranslation';


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


	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}

	public function translate()
	{
		return $this->hasmany('App\Models\ServiceTypeTranslation','service_type_id','id');
	}

	public function category()
	{
		return $this->hasMany('App\Models\Cuisine', 'service_type', 'id');
	}


	public function scopeActive($query)
	{
		return $query->where('status', '1');
	}

	public function getServiceImageAttribute() {
		$type = 21;
		$file = File::where('type',$type)->where('source_id',$this->attributes['id'])->first();
		if($file){
			return $file->service_type_image;
		}
	}

	public function getMobileServiceImageAttribute() {
		$type = 29;
		$file = File::where('type',$type)->where('source_id',$this->attributes['id'])->first();
		if($file){
			return $file->mobile_service_type_image;
		}
	}

	public function getServiceNameAttribute() {
		if (Request::segment(1) == ADMIN_URL) {
			return $this->attributes['service_name'];
		}
		if(request('token')) {
	 		$user = JWTAuth::parseToken()->authenticate();
	 		if($user) {
	 			$lan = $user->language;
	 		}
	 	} else {
	 		$lan = App::getLocale();
	 	}

		if($lan =='en')
			return $this->attributes['service_name'];
		else{ 
			$get=[];
			if(isset($this->attributes['service_name'])) 
				{
					$service_name = ServiceType::where('service_name',$this->attributes['service_name'])->first();
					$get = ServiceTypeTranslation::where('service_type_id',$service_name->id)->where('locale',$lan)->first();
				}
			else if(isset($this->attributes['id'])) 
				{
					$get = ServiceTypeTranslation::where('service_type_id',$this->attributes['id'])->where('locale',$lan)->first();
				}
			if($get)
				return $get->name;
			else
				return $this->attributes['service_name'];
		}
	}
	
	// Get ServiceType Status
	public function getServiceTypeStatusAttribute()
	{	
		return get_status_text($this->attributes['status']);
	}

	public function getServiceTypeBannerImageAttribute() {
		$type = 30;
		$file = File::where('type',$type)->where('source_id',$this->attributes['id'])->first();
		if($file){
			return $file->service_type_banner_image;
		}
	}

	public function getServiceDescriptionAttribute() {
		if (Request::segment(1) == ADMIN_URL) {
			return $this->attributes['service_description'];
		}
		if(request('token')) {
	 		$user = JWTAuth::parseToken()->authenticate();
	 		if($user) {
	 			$lan = $user->language;
	 		}
	 	} else {
	 		$lan = App::getLocale();
	 	}
	 	if($lan =='en')
			return $this->attributes['service_description'];
		else{ 
			$get=[];
			if(isset($this->attributes['id'])) 
				{
					$get = ServiceTypeTranslation::where('service_type_id',$this->attributes['id'])->where('locale',$lan)->first();
				}
			if($get)
				return $get->description;
			else
				return $this->attributes['service_description'];
		}	
	}
}
