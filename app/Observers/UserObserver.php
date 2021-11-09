<?php

namespace App\Observers;

use App\Models\User;
use Session;
use DB;

class UserObserver
{
    /**
     * Listen to the User deleted event.
     *
     * @param  User $user
     * @return void
     */
    public function deleting(User $user)
    {
    	$this->clearUserSession($user->id);
    }

    public function clearUserSession($user_id)
    {
        $session_id = Session::getId();

        DB::table('sessions')->where('user_id', $user_id)->where('id', '!=', $session_id)->delete();

        $current_session = DB::table('sessions')->where('id', $session_id)->first();
        if($current_session) {
            $current_session_data = unserialize(base64_decode($current_session->payload));
            foreach ($current_session_data as $key => $value) {
                if('login_user_' == substr($key, 0, 11)) {
                    if(Session::get($key) == $user_id) {
                        Session::forget($key);
                        Session::forget($value);
                        Session::save(); 
                        DB::table('sessions')->where('id', $session_id)->update(array('user_id' => NULL));;
                    }
                }
            }
        }
        return true;
    }
}