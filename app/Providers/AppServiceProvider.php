<?php

namespace App\Providers;

use App\Models\Pages;
use App\Models\SiteSettings;
use App\Models\Language;
use App\Models\Currency;
use App\Models\Support;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Config;
use DB;
use View;
use App\Http\Helper\FacebookHelper;


class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {

     
		Schema::defaultStringLength(191);

		Blade::if('checkPermission', function ($permission) {
		     return Auth::guard('admin')->user()->can($permission);
		});
		
		if (env('DB_DATABASE') != '') {
		if (Schema::hasTable('site_setting')) {
			config()->set('fcm.http', [
				'server_key' => site_setting('fcm_server_key'),
				'sender_id' => site_setting('fcm_sender_id'),
				'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
				'server_group_url' => 'https://android.googleapis.com/gcm/notification',
				'timeout' => 10,
			]);

			config()->set([
                'mail.default' => site_setting('email_driver'),
                'mail.mailers.smtp.host' 		=> site_setting('email_host'),
                'mail.mailers.smtp.port'       	=> site_setting('email_port'),
                'mail.mailers.smtp.encryption' 	=> site_setting('email_encryption'),
                'mail.mailers.smtp.username'   	=> site_setting('email_user_name'),
                'mail.mailers.smtp.password'   	=> site_setting('email_password'),
                'mail.from' => [
                	'address' => site_setting('email_from_address'),
                	'name'    => site_setting('email_from_name')
                ],
            ]);

			if (site_setting('email_domain') == 'mailgun') {
				config()->set([
					'services.mailgun.domain' => site_setting('email_domain'),
					'services.mailgun.secret' => site_setting('email_secret'),
				]);
			}

			$this->app->bind('braintree_paypal', function($app) {
				$access_token = site_setting('paypal_access_token');
				try{
			        $config = new \Braintree_Configuration([
						   'accessToken' => $access_token,
						]);
					return new \Braintree_Gateway($config);
				}
				catch(\Exception $e){
					View::share('paypal_error', $e->getMessage());
				}
		    });

		    $paypal_currency = site_setting('paypal_currency_code');
        	define('PAYPAL_CURRENCY_CODE', $paypal_currency);

        	 // Custom Validation for File Extension
	        \Validator::extend('valid_extensions', function($attribute, $value, $parameters) 
	        {
	            if(count($parameters) == 0) {
	                return false;
	            }
	            $ext = strtolower($value->getClientOriginalExtension());
	            
	            return in_array($ext,$parameters);
	        });

	       	define('TWILLO_SID', site_setting('twillo_id'));
			define('TWILLO_TOKEN', site_setting('twillo_token'));
			define('TWILLO_FROM', site_setting('twillo_from_number'));

	       $this->app->bind('App\Contracts\SMSInterface','App\Services\SMS\TwillioSms');
	       $this->app->bind('App\Contracts\ImageHandlerInterface','App\Services\LocalImageHandler');
		}
	 }
   }

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {

		foreach (glob(app_path() . '/Helpers/*.php') as $file) {
			require_once $file;
		}

		if (env('DB_DATABASE') != '') {
			if (Schema::hasTable('site_setting')) {
				$this->app->singleton('site_setting', function ($app) {
					$setting = SiteSettings::all()->pluck('value', 'name');
					$setting['jquery_date_format'] = convertPHPToMomentFormat($setting['site_date_format']);
					$setting['ui_date_format'] = convertPHPToJqueryUIFormat($setting['site_date_format']);
					$setting['store_new_order_expiry_time'] = "00:01:00";
					return $setting;
				});
			}
			if (Schema::hasTable('static_page')) {
				$this->app->singleton('static_page', function ($app) {
					$page = request()->route()->getPrefix() == '' ? 'user' : request()->route()->getPrefix();
					$static_pages = Pages::User($page)->where('footer', 1)->where('status', '1')->pluck('name', 'url');
					return $static_pages->split(2);
				});
			}

			if (Schema::hasTable('language')) {
				$this->app->singleton('language', function () {
					return Language::translatable()->get();
				});
			}
			if (Schema::hasTable('currency')) {
				$this->app->singleton('Currency', function () {
					return Currency::get();
				});
			}

			if (Schema::hasTable('site_setting')) {	
				$google_client_id = DB::table('site_setting')->where('name', 'google_client_id')->value('value');
				$google_secret = DB::table('site_setting')->where('name', 'google_client_secret')->value('value');		
				
				Config::set(['services.google' => [
						'client_id' => $google_client_id,
						'client_secret' => $google_secret,
						'redirect' => url('/googleAuthenticate'),
					],
				]);
				View::share('google_client_id', $google_client_id);

				// For Facebook app id and secret
				$fb_result_clientsecret =DB::table('site_setting')->where('name', 'facebook_client_secret')->value('value');
				$fb_result_client =DB::table('site_setting')->where('name', 'facebook_client_id')->value('value');
				
				Config::set([
					'facebook' => [
						'client_id' => $fb_result_client ,
						'client_secret' => $fb_result_clientsecret,
						'redirect' => url('/facebookAuthenticate'),
					],
				]);

				$fb = new FacebookHelper;
				View::share('fb_url', $fb->getUrlLogin());
				define('FB_URL', $fb->getUrlLogin());	
			}

			if (Schema::hasTable('supports')) {
				$this->app->singleton('supports', function ($app) {
					$support_links = Support::where('status','Active')->get();
					View::share('support_links', $support_links);
				});
			}

		}
		
		$this->app->singleton('time_data', function () {

			$day = array('1' => trans('messages.monday'), '2' => trans('messages.tuesday'), '3' => trans('messages.wedsday'), '4' => trans('messages.thursday'), '5' => trans('messages.friday'), '6' => trans('messages.saturday'), '7' => trans('messages.sunday'));
			for ($i = 300; $i <= 3600; $i = $i + 300) {
				$hours = floor($i / 3600);
				$mins = floor($i / 60 % 60);
				$secs = floor($i % 60);
				$v = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
				if ($mins == 0) {
					$time[$v] = '1 Hour';
				} else {
					$time[$v] = $mins . ' Minutes';
				}
			}
			
			$start = strtotime(date('Y-m-d') . "0 minutes");
			$end = strtotime(date('Y-m-d') . " 24:00:00");
			while ($start < $end) {
				$time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start));
				$schedule_time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start)) . ' - ' . date('h:i', strtotime("+60 minutes", $start)).trans('messages.driver.'.date('a', strtotime("+60 minutes", $start)));
				$start = strtotime("+60 minutes", $start);
			}

			$start = strtotime(date('Y-m-d') . "0 minutes");
			$end = strtotime(date('Y-m-d') . " 24:00:00");
			while ($start < $end) {
				$schedule_time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start)) . ' - ' . date('h:i', strtotime("+30 minutes", $start)).trans('messages.driver.'.date('a', strtotime("+30 minutes", $start)));
				$start = strtotime("+30 minutes", $start);
			}

			$time_drop[date('H:i:s', strtotime('23:59:00'))] = date('h:i', strtotime('23:59:00')).trans('messages.driver.'.date('a', strtotime('23:59:00')));
			$data['time'] = $time_drop;
			$data['day'] = $day;
			$data['minutes'] = $time;
			$data['schedule_time'] = $schedule_time_drop;
			return $data;
		});

		// Collection method to pluck mulitple items
		Collection::macro('pluckMultiple', function ($assoc) {
			return $this->map(function ($item) use ($assoc) {
				$list = [];
				foreach ($assoc as $key) {
					$list[$key] = data_get($item, $key);
				}
				return $list;
			}, new static );
		});

	}

	// protected function bindModels()
	// {
	// 	if (Schema::hasTable('site_settings')) {
 //            $this->app->singleton('site_settings', function ($app) {
 //                $site_settings = SiteSettings::get();
 //                return $site_settings;
 //            });
 //        }
	// }

}
