@include('common.head')

@if (Route::current()->uri() != 'login' && Route::current()->uri() != 'forgot_password' && Route::current()->uri() != 'signup' && Route::current()->uri() != 'signup_confirm' && Route::current()->uri() != 'otp_confirm' && Route::current()->uri() != 'reset_password' && Route::current()->uri() != 'about/{page}')
	@include('common.new-header')
@endif

@yield('main')
@if(in_array(Route::currentRouteName(),array('home','feeds','newdetails')))

@include('common.new-footer')
@else
@include('common.new-footer')
@endif
@include('common.foot')