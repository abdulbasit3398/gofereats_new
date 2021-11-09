<?php

/**
 * SiteSettingsController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\Currency;
use App\Models\PromoCode;
use App\Traits\FileProcessing;
use Illuminate\Http\Request;
use Storage;
use Validator;
use DB;
use App;
use Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutPreference;

class SiteSettingController extends Controller {
	use FileProcessing;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function site_setting(Request $request)
	{
		if ($request->getMethod() == 'GET') {
			$firbase = resolve("App\Services\FirebaseService");
			$this->view_data['tab'] = $request->tab ?: 'site_setting';
			$this->view_data['form_name'] = trans('admin_messages.site_setting');
			$this->view_data['paypal_currency_select'] = Currency::codeSelectPaypal();
			$this->view_data['payout_methods'] = ['1'=>'Paypal','2'=>'Stripe','3'=>'BankTransfer'];
			$this->view_data['maintenance_mode'] = (App::isDownForMaintenance()) ? 'down' : 'up';
			return view('admin/site_setting', $this->view_data);
		}
// 
		// dd($request->all());	
// 
		$submit = $request->submit;
		// dd($submit);
		if ($submit == 'site_setting') {
			$rules = array(
				'site_name' => 'required',
				'version' => 'required',
				'admin_prefix' => 'required',
				'store_km' => 'required',
				'driver_km' => 'required',
				'site_support_phone' => 'required',
				'default_currency' =>'required|exists:currency,code',
				'paypal_currency_code' =>'required|exists:currency,code',
				'number_of_delivery' => 'required|numeric|min:2',
				'delivery_radius' => 'required|numeric|min:0.1',
				'preperation_time_interval' => 'required|numeric|min:1|max:120',
				'otp_verification' => 'required',
			);

			// Add Admin User Validation Custom Names
			$attributes = array(
				'site_name' => trans('admin_messages.site_name'),
				'default_currency' => trans('admin_messages.default_currency'),
				'paypal_currency_code' => trans('admin_messages.paypal_currency_code'),
				'version' => trans('admin_messages.version'),
				'store_km' => trans('admin_messages.store_km'),
				'driver_km' => trans('admin_messages.driver_km'),
				'site_support_phone' => trans('admin_messages.site_support_phone'),
				'otp_verification' => trans('admin_messages.otp_verification'),
			);
		} elseif ($submit == 'site_images') {

			$rules = array(
				'site_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'email_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'site_favicon' => 'image|mimes:jpg,png,jpeg,gif',
				'store_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'footer_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'app_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'driver_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'driver_white_logo' => 'image|mimes:jpg,png,jpeg,gif',
				'user_home_image' => 'image|mimes:jpg,png,jpeg,gif',
			);

			// Add Admin User Validation Custom Names
			$attributes = array(
				'site_logo' => trans('admin_messages.site_logo'),
				'email_logo' => trans('admin_messages.email_logo'),
				'site_favicon' => trans('admin_messages.site_favIcon'),
				'store_logo' => trans('admin_messages.store_logo'),
				'footer_logo' => trans('admin_messages.footer_logo'),
				'app_logo' => trans('admin_messages.app_logo'),
				'driver_logo' => trans('admin_messages.driver_logo'),
				'driver_white_logo' => trans('admin_messages.driver_white_logo'),
				'user_home_image' => trans('admin_messages.user_home_image'),
			);
		} elseif ($submit == 'join_us') {
			$rules = array(
				'join_us_facebook' => 'nullable|active_url',
				'join_us_twitter' => 'nullable|active_url',
				'join_us_youtube' => 'nullable|active_url',
				'user_android_link' => 'required|active_url',
				'store_android_link' => 'required|active_url',
				'driver_android_link' => 'required|active_url',
				'user_apple_link' => 'required|active_url',
				'store_apple_link' => 'required|active_url',
				'driver_apple_link' => 'required|active_url',
			);

			$attributes = array(
				'user_android_link' => trans('admin_messages.user_android_link'),
				'store_android_link' => trans('admin_messages.store_android_link'),
				'driver_android_link' => trans('admin_messages.driver_android_link'),
				'user_apple_link' => trans('admin_messages.user_apple_link'),
				'store_apple_link' => trans('admin_messages.store_apple_link'),
				'driver_apple_link' => trans('admin_messages.driver_apple_link'),
			);
		} elseif ($submit == 'fees_manage') {
			$rules = array(
				'delivery_fee_type' => 'required',
				'delivery_fee' => 'required|numeric|max:100',
				'booking_fee' => 'required|numeric|max:100',
				'store_commision_fee' => 'required|numeric|max:100',
				'driver_commision_fee' => 'required|numeric|max:100',
				'pickup_fare' => 'required|numeric|max:1000000',
				'drop_fare' => 'required|numeric|max:1000000',
				'distance_fare' => 'required|numeric|max:1000000',
			);
			// Add Admin User Validation Custom Names
			$attributes = array(
				'service_fee' => trans('admin_messages.service_fee_percentage'),
			);
		} elseif ($submit == 'api_credentials') {
			$rules = array(
				'fcm_server_key' => 'required',
				'fcm_sender_id' => 'required',
				'google_api_key' => 'required',
				'google_server_key' => 'required',
				'twillo_id' => 'required',
				'twillo_token' => 'required',
				'twillo_from_number' => 'required',
				'apple_service_id'  => 'required',
				'apple_team_id'  => 'required',
				'apple_key_id'  => 'required',
				'database_url'          => 'required|url',
				'facebook_client_secret'=>'required',
				'facebook_client_id' =>'required',
				'google_client_id' =>'required',
				'google_client_secret' =>'required',
            	//'service_account'       => 'valid_extensions:json',
 			);
			// Add Admin User Validation Custom Names
			$attributes = array(
				'fcm_server_key' => trans('admin_messages.fcm_server_key'),
				'fcm_sender_id' => trans('admin_messages.fcm_sender_id'),
				'google_api_key' => trans('admin_messages.google_api_key'),
				'google_server_key' => trans('admin_messages.google_server_key'),
				'twillo_id' => trans('admin_messages.twillo_id'),
				'twillo_token' => trans('admin_messages.twillo_token'),
				'twillo_from_number' => trans('admin_messages.twillo_from_number'),
				'apple_service_id'  => trans('admin_messages.apple_service_id'),
				'apple_team_id'  => trans('admin_messages.apple_team_id'),
				'apple_key_file'  => trans('admin_messages.apple_key_file')
			);
		} elseif ($submit == 'payment_gateway') {
			$payment = $request->$submit['payment_methods'] ?? '';
			$payout = $request->$submit['payout_methods'] ?? '';
			if($payment =='')
			{
				flash_message('danger', trans('Please select Atlease once Payment Methods '));
				return redirect()->route('admin.site_setting', ['tab' => $submit]);
			}
			
			$rules['payment_methods'] = 'required';
			// $rules['payout_methods'] = 'required';
			$attributes['payment_methods'] = trans('admin_messages.payment_methods');
			if(in_array('Paypal', $request->$submit['payment_methods'])) {
				$rules['paypal_access_token'] = 'required';
				$rules['paypal_client'] = 'required';
				$rules['paypal_secret'] = 'required';
				$rules['paypal_mode'] = 'required';

				$attributes['paypal_access_token'] = 'paypal access token';
				$attributes['paypal_client'] = 'paypal client';
				$attributes['paypal_secret'] = 'paypal secret';
				$attributes['paypal_mode'] = 'paypal mode';
			}

			if(in_array('Stripe', $request->$submit['payment_methods'])) {
				$rules['stripe_publish_key'] = 'required';
				$rules['stripe_secret_key'] = 'required';
				$rules['stripe_api_version'] = 'required';

				$attributes['stripe_publish_key'] = trans('admin_messages.stripe_publish_key');
				$attributes['stripe_secret_key'] = trans('admin_messages.stripe_secret_key');
				$attributes['stripe_api_version'] = trans('admin_messages.stripe_api_version');
			}
		} elseif ($submit == 'email_setting') {
			$rules = array(
				'email_driver' => 'required',
				'email_host' => 'required',
				'email_port' => 'required',
				'email_from_address' => 'required|email',
				'email_to_address' => 'required|email',
				'email_from_name' => 'required',
				'email_encryption' => 'required',
			);
			if ($request->$submit['email_driver'] == 'smtp') {
				$rules = array('email_user_name' => 'required',
					'email_password' => 'required');
			}
			if ($request->$submit['email_driver'] == 'mailgun') {
				$rules['email_domain'] = 'required';
				$rules['eamil_secret'] = 'required';
			}

			// Add Admin User Validation Custom Names
			$attributes = array(
				'email_driver' => trans('admin_messages.email_driver'),
				'email_host' => trans('admin_messages.email_host'),
				'email_port' => trans('admin_messages.email_port'),
				'email_from_address' => trans('admin_messages.email_from_address'),
				'email_to_address' => trans('admin_messages.email_to_address'),
				'email_from_name' => trans('admin_messages.email_from_name'),
				'email_encryption' => trans('admin_messages.email_encryption'),
				'email_user_name' => trans('admin_messages.email_user_name'),
				'email_password' => trans('admin_messages.email_password'),
				'email_domain' => trans('admin_messages.email_domin'),
				'eamil_secret' => trans('admin_messages.email_secret'),
			);
		}
		elseif ($submit == 'social_media') {
				$rules = array(
				'facebook_login' => 'required',
				'google_login' => 'required',
				'apple_login' => 'required',
				
			);
			$attributes = array(
				'facebook_login' => trans('admin_messages.continue_with_facebook'),
				'google_login' => trans('admin_messages.continue_with_google'),
				'apple_login' => trans('admin_messages.continue_with_apple'),
				
			);	
		}
		else {
			return redirect()->route('admin.site_setting');
		}
		
		if (!$request->$submit) {
	
			return redirect()->route('admin.site_setting', ['tab' => $submit]);
		}

		$messages = [
			'default_currency.exists' => "Please Enter Valid Currency Code",
		];

		$validator = Validator::make($request->$submit, $rules, $messages, $attributes);

		if ($validator->fails()) {

			return redirect()->route('admin.site_setting', ['tab' => $submit])->withErrors($validator)->withInput();
		}

		
		if ($submit == 'site_setting') {
			$default_currency = $request->site_setting['default_currency'];
		}
		if ($submit == 'payment_gateway') {
			 if(in_array('Paypal', $request->$submit['payment_methods']) == false && in_array('Stripe', $request->$submit['payment_methods']) == false ) {
				if(in_array('Wallet', $request->$submit['payment_methods'])==true)
				{
					flash_message('danger', trans('Please choose Stripe / PayPal for wallet option'));
					return redirect()->route('admin.site_setting', ['tab' => $submit]);
				}
			}

			if($payout !='')
			{
				$exclude_data = array_diff(explode(",",site_setting('payout_methods')),$request->$submit['payout_methods']);
				if(!is_null($exclude_data))
				$payout_as_default = PayoutPreference::wherein('payout_method',$exclude_data)->where('default','Yes')->get()->count();
				if($payout_as_default > 0 )
				{
					flash_message('danger', trans('Some Users Uses '.implode(",",$exclude_data).' as Default One'));
					return redirect()->route('admin.site_setting', ['tab' => $submit]);
				}
			}
			else
			{
				flash_message('danger', trans('Please select Atlease once Payout Methods '));
				return redirect()->route('admin.site_setting', ['tab' => $submit]);
			}
			
			
		}

		foreach ($request->$submit as $key => $value) {
			if ($submit == 'site_images') {
				$file = $request->file('site_images')[$key];
				$file_path = $this->fileUpload($file, 'public/images/site_setting');

				if ($key == 'site_logo') {
					$this->fileSave('site_setting', '1', $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
				}
				else if ($key == 'site_favicon') {
					$this->fileSave('site_setting', '2', $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
				}
				else if ($key == 'store_logo') {
					$this->fileSave('site_setting', '3', $file_path['file_name'], '1');
				}
				else if ($key == 'email_logo') {
					$this->fileSave('site_setting', '4', $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$this->fileCrop($orginal_path, get_image_size('email_logo')['width'], get_image_size('email_logo')['height'], $orginal_path);
				}
				else if ($key == 'footer_logo') {
					$this->fileSave('site_setting', '5', $file_path['file_name'], '1');
				}
				else if ($key == 'app_logo') {
					$this->fileSave('site_setting', '6', $file_path['file_name'], '1');
				}
				else if ($key == 'driver_logo') {
					$this->fileSave('site_setting', '7', $file_path['file_name'], '1');
				}
				else if ($key == 'driver_white_logo') {
					$this->fileSave('site_setting', '8', $file_path['file_name'], '1');
				}
				else if ($key == 'user_grocery_image') {
					$this->fileSave('site_setting', '27', $file_path['file_name'], '1');
				}
				else if ($key == 'user_service_image') {
					$this->fileSave('site_setting', '23', $file_path['file_name'], '1');
				}
				else if ($key == 'user_food_image') {
					$this->fileSave('site_setting', '24', $file_path['file_name'], '1');
				}
				else if ($key == 'user_alcohol_image') {
					$this->fileSave('site_setting', '25', $file_path['file_name'], '1');
				}
				else if ($key == 'user_medicine_image') {
					$this->fileSave('site_setting', '26', $file_path['file_name'], '1');
				}
				$orginal_path = Storage::url($file_path['path']);
			}
			else {
				if($key =='payment_methods') {
					$value = implode(',', $request->$submit['payment_methods']);
				}
				if($key =='payout_methods') {
					$value = implode(',', $request->$submit['payout_methods']);
				}
				
				SiteSettings::where('name', $key)->update(['value' => $value]);

				Currency::where('status', '1')->update(['default_currency' => '0']);
				Currency::where('code', $request->site_setting['default_currency'])->update(['default_currency' => '1']);

				PromoCode::where('promo_type', 0)->each(function($promo) use($request) {
					$promo->price = $promo->price;
					$promo->currency_code = $request->site_setting['default_currency'];
					$promo->save();
				});
			}
			if ($key == 'apple_key_file') {
                    $key_file = $request->api_credentials['apple_key_file'];
                    $extension = $key_file->getClientOriginalExtension();
                    $filename = 'key.txt';
                    $success = $key_file->move(public_path(), $filename);
                    if(!$success) {
                        return back()->withError('Could not upload Image');
                    }
                    SiteSettings::where('name', $key)->update(['value' => $filename]);
			}
			
			$image_uploader = resolve('App\Contracts\ImageHandlerInterface');
			$dir_name = resource_path();

			if($key == 'service_account'){
				$service_account = $request->api_credentials['service_account'];
	            $target_dir = '/credentials';
	            $file_name = "service_account.json";
	            $extensions = ['json'];
	            $options = compact('dir_name','target_dir','file_name','extensions');

	            $upload_result = $image_uploader->upload($service_account,$options);
	            if(!$upload_result['status']) {
	                flash_message('danger', $upload_result['status_message']);
	                return back();
	            }
	            $file_name = $dir_name.$target_dir.'/'.$file_name;
	            $file_name = str_replace(base_path(),"",$file_name);

	            SiteSettings::where(['name' => 'service_account'])->update(['value' => $file_name]);
			}

			Artisan::call($request->maintenance_mode);
		}
		

		flash_message('success', trans('admin_messages.updated_successfully'));
		if($request->site_setting['admin_prefix'] != site_setting('admin_prefix')) {
			$redirect_url = url($request->site_setting['admin_prefix'].'/site_setting').'?tab='.$submit;
		}
		else {
			$redirect_url = route('admin.site_setting', ['tab' => $submit]);
		}
		return redirect($redirect_url);
	}

}
