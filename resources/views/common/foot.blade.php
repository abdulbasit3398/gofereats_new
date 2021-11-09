<!-- {!! $analystics !!} -->
<script src="{{asset('js/app.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/owl.carousel.min.js')}}" type="text/javascript"></script>
<script src="{{asset('js/underscore-min.js')}}" type="text/javascript"></script>
<script src="{{asset('js/bootstrap-select.min.js')}}" type="text/javascript"></script>
<script src="{{asset('js/bootstrap-toggle.min.js')}}" type="text/javascript"></script>
<script src="{{ url('admin_assets/js/plugins/jquery.validate.min.js') }}"></script>
<script src="{{ url('admin_assets/js/plugins/additional-methods.min.js?v='.$version) }}"></script>
<script src="{{asset('js/toastr.min.js')}}" type="text/javascript"></script>

<!-- angular js -->
<script src="{{asset('admin_assets/js/angular.js')}}"></script>
<script src="{{asset('admin_assets/js/angular-sanitize.js')}}"></script>

<script type="text/javascript">
  var app = angular.module('App', ['ngSanitize']);
  var jquery_datetimepicker_date_format = "{{site_setting('jquery_date_format')}}";
  var ui_date_format = "{{site_setting('ui_date_format')}}";
  var STRIPE_PUBLISH_KEY = "{{ site_setting('stripe_publish_key') }}";
  var GOOGLE_CLIENT_ID = '{{ $google_client_id }}';
</script>
    
<script src="https://apis.google.com/js/api:client.js"></script>
<script src="{{url('js/googleapilogin.js?v1=1.0')}}"></script>

<!--  Google Maps Plugin  -->
<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key={{site_setting('google_api_key')}}&libraries=places&language={{ (session('language')) ? session('language') : $default_language[0]->value }}"></script>

@if (Route::current()->uri() !='restaurant/payout_preference' && Route::current()->uri() !='user_payment' )
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@endif

@if(Route::currentRouteName() == 'feeds')
    <script src="{{asset('js/category.js?v='.$version)}}" type="text/javascript"></script>
@endif
@if(Route::currentRouteName() == 'newdetails')
    <script src="{{asset('js/detail.js?v='.$version)}}" type="text/javascript"></script>
@endif



<script src="{{asset('messages.js')}}" type="text/javascript"></script>
<script src="{{asset('js/store_detail.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/home.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/driver.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/store.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/common.js?v='.$version)}}" type="text/javascript"></script>
<script src="{{asset('js/locationpicker.jquery.js')}}?dasd" type="text/javascript"></script>


<!-- Paypal scripts -->
@if(Route::currentRouteName() =='checkout'  || Route::currentRouteName() == 'user_payment')
<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4></script>
<script src="https://js.braintreegateway.com/web/3.39.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.39.0/js/paypal-checkout.min.js"></script>
@endif

@if(in_array(Route::currentRouteName(),array('home','feeds','newdetails','login','signup')))
    <script src="{{asset('js/footer.js?v='.$version)}}" type="text/javascript"></script>
@endif

<script src="{{asset('js/select.js?v='.$version)}}" type="text/javascript"></script>    

<script type="text/javascript">
var APP_URL = {!! json_encode(url('/')) !!};

// get url data
var ajax_url_list = {
    search                               : '{{route("search")}}',
    store_location                       : '{{route("store_location")}}',
    search_result                        : '{{route("search_result")}}',
    signup2                              : '{{route("signup2")}}',
    signup_data                          : '{{route("signup_data")}}',
    item_details                         : '{{route("item_details")}}',
    orders_store                         : '{{route("orders_store")}}',
    orders_remove                        : '{{route("orders_remove")}}',
    orders_change                        : '{{route("orders_change")}}',
    category_details                     : '{{route("category_details")}}',
    checkout                             : '{{route("checkout")}}',
    card_details                         : '{{route("card_details")}}',
    paypal_currency_conversion           : '{{route("paypal_currency_conversion")}}',
    place_order_details                  : '{{route("place_order_details")}}',
    order_track                          : '{{route("order_track")}}',
    search_data                          : '{{route("search_data")}}',
    session_clear_data                   : '{{route("session_clear_data")}}',
    place_order                          : '{{route("place_order")}}',
    order_invoice                        : '{{route("order_invoice")}}',
    add_cart                             : '{{route("add_cart")}}',
    add_promo_code_data                  : '{{route("add_promo_code_data")}}',
    schedule_store                       : '{{route("schedule_store")}}',
    password_change                      : '{{route("password_change")}}',
    change_password                      : '{{route("restaurant.change_password")}}',
    dasboard                             : '{{route("restaurant.dashboard")}}',
    cancel_order                         : '{{route("cancel_order")}}',
    location_check                       : '{{route("location_check")}}',
    location_not_found                   : '{{route("location_not_found")}}',
    get_payout_preference                : '{{route("restaurant.get_payout_preference")}}',
    offers_status                        : '{{route("restaurant.offers_status")}}',
    remove_time                          : '{{route("restaurant.remove_time")}}',
    send_message                         : '{{route("restaurant.send_message")}}',
    confirm_phone_no                     : '{{route("restaurant.confirm_phone_no")}}',
    profile_pic_upload                   : '{{route("driver.profile_upload")}}',
    status_update                        : '{{route("restaurant.status_update")}}',
    show_comments                        : '{{route("restaurant.show_comments")}}',
    particular_order                     : '{{route("driver.particular_order")}}',
    invoice_filter                       : '{{route("driver.invoice_filter")}}',
    ajax_help_search                     : '{{route("ajax_help_search")}}',
    help                                 : '{{route("help")}}',
    update_modifier                      : '{{route("restaurant.update_modifier")}}',
    delete_modifier                      : '{{route("restaurant.delete_modifier")}}',
    remove_promo_code                    : '{{route("remove_promo_code_data")}}',
    add_driver_tips                      : '{{route("add_driver_tips")}}',
    remove_driver_tips                   : '{{route("remove_driver_tips")}}',
    store_signup_data                    : '{{route("store_signup_data")}}',
    add_promo_code_order                 : '{{route("add_promo_code_order")}}',
    add_wallet_amount_paypal             : '{{route("add_wallet_amount_paypal")}}',
    add_wallet_amount                    : '{{route("add_wallet_amount")}}',
    user_payment                         : '{{route("user_payment")}}',
    add_wallet_stripe                    : '{{route("add_wallet_stripe")}}',
    add_wb_wallet                        : '{{route("add_wb_wallet")}}',
    delete_documents                     : '{{route("restaurant.delete_documents")}}',
    get_category                         : '{{route("restaurant.get_category")}}',
    get_item                             : '{{route("restaurant.get_item")}}',
    get_category_item                    : '{{route("get_category_item")}}',
    feeds                                :  '{{route("feeds")}}',
    set_service_type                     : '{{route("set_service_type")}}',
    get_menu_items                       : '{{route("get_menu_items")}}'
};

function getUrl(key, replaceValues={}) {
	url = ajax_url_list[key];
	var replace_url = url;
	$.each(replaceValues, function(i, v) {
	  replace_url = replace_url.replace('@'+i, v);
	});
	return replace_url;
}

function getUrls(key) {
	return ajax_url_list[key];
}
// var lang_session = "{{ (session('language')) ? session('language') : $default_language[0]->value }}";
// console.log(lang_session);
// $.datepicker.setDefaults($.datepicker.regional[lang_session]);
</script>
@if(env('Live_Chat') == "true")
<script>
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/57223b859f07e97d0da57cae/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
  })();
</script>
@endif
@if(session('language') != 'en')
  {!! Html::script('js/i18n/datepicker-'.session('language').'.js') !!}
@endif
@stack('scripts')
</body></html>
