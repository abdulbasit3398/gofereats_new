<?php

/**
 * File Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   File
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class File extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'file';

	protected $fillable = ['name'];

	protected $appends = ['image_name', 'store_document','file_extension','image_social_name'];

	public $fileTypeArray = [];

	public function __construct() {
		parent::__construct();
		$this->fileTypeArray = FileType::get()->pluck('id', 'name');
	}

	public function getImageNameAttribute() {
		if ($this->attributes['type'] == 3 || $this->attributes['type'] == 4) {
			$folder = 'store/' . $this->attributes['source_id'];
		} elseif ($this->attributes['type'] == 2) {
			$folder = 'user';
		}
		elseif ($this->attributes['type'] == 22) {
			return  $this->attributes['name'] ;
		}
		else {
			$folder = 'driver';
		}
		
		if ($this->attributes['name']) {
			$images = url(Storage::url('images/' . $folder . '/' . $this->attributes['name']));
		}
		else {
			$images = '';
		}

		return $images;
	}

	public function scopeType($query, $type) {
		$file_type = $this->fileTypeArray[$type];
		return $query->where('type', $file_type);
	}

	public function getStoreDocumentAttribute() {

		if ($this->name) {
			return url(Storage::url('images/store')) . '/' . $this->source_id . '/documents/' . $this->name;
		} else {
			return sample_image();
		}

	}
	//store_home_slider_image
	public function getStoreHomeSliderImageAttribute() {
		if ($this->attributes['name']) {
			return url(Storage::url('images/store_home_slider')) . '/'. $this->name;
		} else {
			return sample_image();
		}

	}
	//user_home_slider_image
	public function getUserHomeSliderImageAttribute() {
		if ($this->attributes['name']) {
			return url(Storage::url('images/user_home_slider')) . '/'. $this->name;
		} else {
			return sample_image();
		}

	}

	//site_image_url
	public function getSiteImageUrlAttribute() {
		return Storage::url("public/images/site_setting/" . $this->attributes['name']);
	}

	//file_extension
	public function getFileExtensionAttribute() {
		$name = explode('.', $this->attributes['name']);
		if(isset($name[1]))
			return strtolower($name[1]);
	}

	public function getServiceTypeImageAttribute() {
		if ($this->attributes['name']) {
			return url(Storage::url('images/service_type')) . '/'. $this->name;
		} else {
			return sample_image();
		}
	}

	public function getMobileServiceTypeImageAttribute() {
		if ($this->attributes['name']) {
			return url(Storage::url('images/mobile_service_type')) . '/'. $this->name;
		} else {
			return sample_image();
		}
	}

	public function getSupportImageAttribute() {
		if ($this->attributes['name']) {
			return url(Storage::url('images/support_image')) . '/'. $this->name;
		} else {
			return sample_image();
		}
	}

	public function getImageSocialNameAttribute() {
			if( $this->attributes['source'] == 2)
			{
				return  url(Storage::url('images/user')). '/'.  $this->attributes['name'];	 
			}
			else
				return  $this->attributes['name'];	 
	}

	public function getServiceTypeBannerImageAttribute() {
		if ($this->attributes['name']) {
			logger("storage ".url(Storage::url('images/service_type_banner_image')) . '/'. $this->name);
			return url(Storage::url('images/service_type_banner_image')) . '/'. $this->name;

		} else {
			return sample_image();
		}
	}


}
