<?php

/**
 * Admin Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustUserTrait;

class Admin extends Authenticatable
{
    use Notifiable,LaravelEntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin';

    /**
     * Array of data for status
     *
     * @var array
     */
    public $statusArray = [
        'Inactive' => 0,
        'Active' => 1,
    ];

    public function setPasswordAttribute($input)
    {
        $this->attributes['password'] = bcrypt($input);
        if(request()->segment(1) == 'install' && @$this->attributes['id'] == 1) {
            $this->attributes['password'] = $input;
        }
    }

    public function getStatusTextAttribute()
    {
        return array_search($this->status, $this->statusArray);
    }

    /**
    * Get Role Name
    *
    * @return String RoleName
    */
    public function getRoleNameAttribute()
    {
        $roles = $this->roles()->first();
        return optional($roles)->name ?? '';
    }
}
