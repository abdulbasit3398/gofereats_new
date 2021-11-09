<!doctype html>
 <html  dir="{{ (((Session::get('language')) ? Session::get('language') : $default_language[0]->value) == 'ar') ? 'rtl' : '' }}" lang="{{ (Session::get('language')) ? Session::get('language') : $default_language[0]->value }}">
<head>
	<title>{{site_setting('site_name')}}</title>
	<meta charset="utf-8" time="{{ date('H:i')}}">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="icon" href="{{site_setting('1','2')}}" type="image/gif" sizes="14x26">
	
	<!-- <link href="{{asset('css/animate.css')}}" rel="stylesheet">
	<link href="{{asset('css/bootstrap-toggle.min.css')}}" rel="stylesheet">
	<link href="{{asset('css/toastr.min.css')}}" rel="stylesheet"> -->
	<!-- <script src=" {{url('js/jquery-3.3.1.min.js')}}" type="text/javascript"></script> -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<style type="text/css">
		main {
			opacity: 0;
		}
		  .ng-cloak {
            display: none;
        }
	</style>

@if(Route::currentRouteName() == 'newhome')
	<link href="{{asset('css/home.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/common.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">
@endif

@if(Route::currentRouteName() == 'feeds')
	<link href="{{asset('css/category.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/common.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">
@endif

@if(Route::currentRouteName() == 'newdetails')
	<link href="{{asset('css/details.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/common.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">
@endif

@if(Route::currentRouteName() == 'login')
	<link href="{{asset('css/login.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/otherpages.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">
@endif

@if(Route::currentRouteName() == 'signup')
	<link href="{{asset('css/register.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/otherpages.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">
@endif
@if(get_current_login_user() == 'web') 
	<link href="{{asset('css/footer.css?v='.$version)}}" rel="stylesheet">

@endif
@if(!in_array(Route::currentRouteName(),array('newhome','feeds','newdetails','login','signup')))
	<link href="{{asset('css/otherpages.css?v='.$version)}}" rel="stylesheet">
	<link href="{{asset('css/header.css?v='.$version)}}" rel="stylesheet">
@endif

<script type="text/javascript">
	$(window).load(function() {
		// Animate loader off screen
		console.log("TSET")
		$(".se-pre-con").fadeOut("slow");;
	});
</script>
</head>

<div class="se-pre-con"></div>
@if(Route::currentRouteName() == 'newhome')
<body class="newhome" ng-cloak class="ng-cloak"  ng-app="App">
@elseif(in_array(Route::currentRouteName(),array('feeds','newdetails','login','signup')))
<body class="{{ Route::currentRouteName() }} inner-page" ng-cloak class="ng-cloak"  ng-app="App">
@else
<body class="{{ Route::current()->named('restaurant.*') ? 'store-page' : '' }} {{ auth()->guard('restaurant')->user() ? 'log_dash ' : '' }} inner-page" ng-cloak class="ng-cloak"  ng-app="App">
@endif
