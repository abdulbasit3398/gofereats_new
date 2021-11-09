@include('common.head')

@if (Route::current()->uri() != 'store/login' && Route::current()->uri() != 'store/password' && Route::current()->uri() != 'store/forget_password' && Route::current()->uri() != 'store/mail_confirm' && Route::current()->uri() != 'store/set_password')
	@include('common.new-header')
@endif


@yield('main')

@include('common.new-footer')

@include('common.foot')
