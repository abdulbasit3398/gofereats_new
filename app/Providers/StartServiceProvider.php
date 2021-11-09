<?php

/**
 * StartService Provider
 *
 * @package     GoferEats
 * @subpackage  Provider
 * @category    Service
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Currency;
use App\Models\SiteSettings;
use View;
use Config;
use Schema;
use Auth;
use App;
use Session;
use Request;
use App\Models\Admin;
use App\Models\Pages;
use App\Models\Support;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

class StartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	if(env('DB_DATABASE') != '') {
            if(Schema::hasTable('currency')) {
                $this->currency();
            }
            if(Schema::hasTable('site_setting')) {
                $this->site_settings();
            }
            if(Schema::hasTable('language')) {
                $this->language();
            }
            if(Schema::hasTable('static_page')) {
                $this->pages();
            }
            if(Schema::hasTable('supports')) {
                 $this->supports();
            }
		}
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
	 
    public function currency()
    {
        $currency = Currency::codeSelect();
        View::share('currency_select', $currency);
        
        $default_currency = Currency::active()->defaultCurrency()->first();        

        if(!@$default_currency)
            $default_currency = Currency::active()->first();

        Session::put('currency', $default_currency->code);
        Session::put('symbol', $default_currency->symbol);

        define('default_currency_symbol', html_entity_decode($default_currency->symbol));
        define('DEFAULT_CURRENCY', $default_currency->code); 
    }

	// Share Language Details to whole software
	public function language()
	{
        $language = resolve('language');
		// Language lists for footer
        View::share('lang', $language);
        View::share('language', $language->pluck('name', 'value'));  
		
		// Default Language for footer
		$default_language = $language->where('default_language', '=', '1')->values();

        View::share('default_language', $default_language);
        
        if(Request::segment(1) == ADMIN_URL) {
		    $default_language = $language->where('value', 'en')->values();
		}

        if($default_language->count() > 0) {
			Session::put('language', $default_language[0]->value);
			App::setLocale($default_language[0]->value);
		}
	}

    public function site_settings()
    {

        $payment_methods = array(
            ["key" => "cash", "value" => 'Cash', 'icon' => asset("images/icon/cash.png")],
            ["key" => "paypal", "value" => 'Paypal', 'icon' => asset("images/icon/paypal.png")],
            ["key" => "stripe", "value" => 'Stripe', 'icon' => asset("images/icon/stripe.png")],
        );

        if(!defined('PAYMENT_METHODS')) {
            define('PAYMENT_METHODS', $payment_methods);    
        }

        $site_settings = SiteSettings::all();
        $admin_prefix = $site_settings->where('name','admin_prefix')->first();
        $site_version = $site_settings->where('name','version')->first();
        $site_analystics = $site_settings->where('name','analystics')->first();
        View::share('analystics', $site_analystics->value);
        View::share('version', str_random(4));
        define('ADMIN_URL', optional($admin_prefix)->value ?? 'admin');

        $site_url = $site_settings->where('name','site_url')->first();

        if($site_url->value == '' && @$_SERVER['HTTP_HOST'] && !App::runningInConsole()) {
            $url = "http://".$_SERVER['HTTP_HOST'];
            $url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
            SiteSettings::where('name','site_url')->update(['value' =>  $url]);
        }
        define('PAGINATION', '10');
    }

    public function pages()
    {
        if (Schema::hasTable('static_page')) {
            $root = check_current_root();
            $page = $root == 'web' ? 'user' : $root;
            
            if($page != 'admin' && $page != 'api') {
                $static_pages_changes = Pages::User($page)->where('footer', 1)->where('status', '1')->get();
                View::share('static_pages_changes', $static_pages_changes->split(2));
            }
        }
    }


    public function supports()
    {
        $support_links = resolve('supports');
        $support_links = Support::where('status','Active')->get();    
        View::share('support_links', $support_links);
    }

}
