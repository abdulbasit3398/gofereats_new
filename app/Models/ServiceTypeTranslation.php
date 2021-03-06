<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTypeTranslation extends Model
{
    //
    public $timestamps = false;
    protected $fillable = ['name'];
    
    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }
}
