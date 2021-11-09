<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class LiveRestrict
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!isLiveEnv()) {
            return $next($request);
        }

        if (in_array(request()->segment(1),['admin'])) {
            $url = url()->current();

            $delete_url = strlen((string)stripos($url,"delete"));

            $unrestricted_routes = [
                'admin.authenticate',
                // 'admin.add_store',
                // // 'admin.edit_store',
                // 'admin.add_driver',
                // 'admin.edit_driver',
                // 'admin.add_user',
                // 'admin.edit_user',
                // 'admin.send_message',
            ];
            $ip_whitelist = explode(",",env('IP_ADDRESS'));
            if(($request->isMethod('POST') || $delete_url) && !in_array($request->route()->getName(),$unrestricted_routes) ) {
                if(in_array($_SERVER['REMOTE_ADDR'], $ip_whitelist)) {
                    return $next($request);
                }
                Session::flash('alert-class', 'alert-danger');
                Session::flash('message', 'Data add,edit & delete Operation are restricted in live.');
                return redirect(url()->previous());
            }


            // if (($request->isMethod('POST') || $delete_url ) && !in_array($request->route()->getName(),$unrestricted_routes) ) {

            //     if (in_array($_SERVER['REMOTE_ADDR'], $ip_whitelist)) {
                
            //         return $next($request);
            //     }
            //     Session::flash('alert-class', 'alert-danger');
            //     Session::flash('message', 'Data add,edit & delete Operation are restricted in live.');
            //     return redirect(url()->previous());
            // }
        }

        return $next($request);
    }
}
