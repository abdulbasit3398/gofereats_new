<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Support extends Model
{
    //
    protected $table = 'supports';

    public function getSupportImageAttribute() {
		$type = 28;
		$file = File::where('type',$type)->where('source_id',$this->attributes['id'])->first();
		if($file){
			return $file->support_image;
		}
	}
	
	public function scopeActive($query)
	{
		return $query->where('status', '1');
	}
}
