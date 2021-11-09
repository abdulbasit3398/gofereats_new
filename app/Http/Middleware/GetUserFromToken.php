<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use JWTAuth;
use App;
use Auth;
use Request;

class GetUserFromToken extends BaseMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, \Closure $next)
	{
		$validate_token = $this->validateToken($request);

		if($validate_token) {
			return $validate_token;
		}

		$user = JWTAuth::parseToken()->authenticate();

		if (!$user) {
			return response()->json([
				'status_code' => "0",
				'status_message' => __('api_messages.driver.user_not_found'),
			], 400);
		}

		if ($user->status == '0') {
			return response()->json([
				'status_message' => __('api_messages.driver.inactive_user') ,
				'status_code' => "0",
			], 401);
		}

		if($user->type == 2) {
			$timezone = $user->driver->default_timezone;
			date_default_timezone_set($timezone);
		}
		else if ($user->user_address) {
			$timezone = $user->user_address->default_timezone;
			date_default_timezone_set($timezone);
		}

		$language = $user->language ?? 'en';
		App::setLocale($language);

		return $next($request);
	}

	protected function validateToken($request)
	{
		try {
			$user = JWTAuth::parseToken()->authenticate();
			if($user == '') {
				return response()->json(['status' => 'token_invalid'],400);
			}

			if(Request::segment(1) == 'api_payments' || Request::segment(1) == 'api') {
				$language = $request->language ?? $user->language ?? 'en';
				\Session::put('language', $language);
				App::setLocale($language);
	        }

		}
		catch (\Exception $e) {
			if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
				return response()->json(['error' => 'token_invalid'],400);
			}
			else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
				return $this->getRefreshToken($request->token);
			}
			else {
				return response()->json(['error' => 'token_not_provided'],400);
			}
		}
		return false;
	}

	protected function getRefreshToken($token)
	{
		try {
			$refreshed = JWTAuth::refresh($token);
		}
		catch (\Exception $e) {
			return response()->json(['error' => 'token_invalid'],400);
		}

		return response()->json([
			'status_code' 		=> "0",
			'success_message' 	=> "Token Expired",
			'refresh_token' 	=> $refreshed,
		]);
	}
}
