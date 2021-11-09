<?php

namespace App\Http\Middleware;

use App\Models\Country;
use Closure;
use Session;
use Auth;
use App\Models\User;

class ClearCache
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
		if(auth()->guard('web')->user()) {
            $user_details = auth()->guard('web')->user();

		    $user = User::with('user_address')->where('id', $user_details->id)->first();
			if (isset($user->user_address)) {
				$timezone = $user->user_address->default_timezone;
				date_default_timezone_set($timezone);
			}
		}
		else {
            $ip = getenv("REMOTE_ADDR");
            $timezone = $request->session()->get('time_zone');
			if(!$request->session()->get('time_zone')) {
				$result = unserialize(@file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));

				if (@$result['geoplugin_currencyCode']) {
					if(isset($result['geoplugin_timezone'])) {
						Session::put('time_zone', $result['geoplugin_timezone']);
					}
					else {
						Session::put('time_zone', 'Asia/Calcutta');
					}
					Session::put('country_code', $result['geoplugin_countryCode']);
					$phone_code = Country::where('code', $result['geoplugin_countryCode'])->first()->phone_code;
					Session::put('phone_code', $phone_code);
				}
			}

			$timezone = $request->session()->get('time_zone');

			if (isset($timezone) && isValidTimezone($timezone)) {
				date_default_timezone_set($timezone);
			}
			else {
				date_default_timezone_set('Asia/Calcutta');
				Session::put('time_zone', 'Asia/Calcutta');
				Session::put('country_code', 'IN');
				$phone_code = Country::where('code', 'IN')->first()->phone_code;
				Session::put('phone_code', $phone_code);
			}
		}

		schedule_data_update();

		$response = $next($request);
		$response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

		return $response;
	}
}