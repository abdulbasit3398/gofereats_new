@extends('template2')

@section('main')
<div class="flash-container">
	@if(Session::has('message'))
	<div class="alert {{ Session::get('alert-class') }} text-center" role="alert">
		<a href="#" class="alert-close" data-dismiss="alert">&times;</a> {{ Session::get('message') }}
	</div>
	@endif
</div>
<main id="site-content" role="main" class="log-user" ng-controller="signup_page">
	<div class="container">
		<div class="logo text-center mt-5">
			<a href="{{url('/')}}">
				<img src="{{site_setting('1','1')}}" width="120" height="">
			</a>
		</div>
		<div class="login-form py-5 mb-5 col-md-8 col-lg-6 mx-auto">
			<h1>{{trans('messages.profile.create_an_account')}}</h1>
			<form name="signup2" id='user_signup_form' class="form-horizontal">
				<div class="form-group">
					<label>{{trans('messages.profile.enter_first_name')}}<span>({{trans('messages.profile.required')}})</span></label>
					{!! Form::text('first_name', old('first_name',session('user_data.first_name')), ['id'=>'first_name','class'=>'text sffocus form-control','autocomplete'=>'off','']) !!}
				</div>
				<div class="form-group">
					<label>{{trans('messages.profile.enter_last_name')}} <span>({{trans('messages.profile.required')}})</span></label>
					{!! Form::text('last_name', old('last_name',session('user_data.last_name')), ['id'=>'last_name','class'=>'text sffocus form-control','autocomplete'=>'off']) !!}
				</div>
				<input type="hidden" name='otp_verification' id='otp_verification' value ={{site_setting('otp_verification')}}>
				<input type="hidden" name="key_id" id="key_id" value="{{Session::get('user_data')['key_id']}}"/>
				<input type="hidden" name="source" id="source" value="{{Session::get('user_data')['source'] ?? 'mobile_number'}}"/>
				<input type="hidden" name="user_image" id="user_image" value="{{Session::get('user_data')['user_image'] ?? ''}}"/>
				<input type="hidden" name="apply_country_code" id="apply_country_code" value=""/>
					<div class="form-group">
						<label>{{trans('messages.profile.enter_your_phone_number')}} <span>({{trans('messages.profile.required')}})</span></label>
						<div class="d-flex w-100">
							<div class="select mob-select col-md-3 p-0">
								<span class="phone_code">+{{ @session::get('code') }}</span>
								<select id="phone_code" name="country_code" class="form-control">
				                    @foreach ($country as $key => $country)
				                        <option value="{{ $country->phone_code }}"  data-id="{{ $country->id }}"
				                        {{ $country->phone_code == @session::get('code') ? 'selected' : '' }} >{{ $country->name }}</option>
				                    @endforeach
				                </select>
							</div>
							<input type="number" name="phone_number" id="phone_number" data-error-placement="container" data-error-container=".phone_error" placeholder=""/>
						</div>
					</div>
				<p class="phone_error text-danger">  </p>
				<div class="form-group">
					<label>{{trans('messages.profile.enter_your_email_address')}} <span>({{trans('messages.profile.required')}})</span></label>
					<!-- <input type="text" name="email_address" id="email_address" placeholder=""/> -->
					 {!! Form::text('email_address', old('email_address',session('user_data.email')), ['id'=>'email_address','class'=>'text form-control','autocomplete'=>'off']) !!}
					<p id="email_address_error" style="color: red;display: none">Invalid email address</p>
					<p class="email_address_error text-danger">  </p>
				</div>
				@if(!@Session::get('user_data')['source'])
				<div class="form-group">
					<label>{{trans('messages.profile.password')}} <span>({{trans('messages.profile.required')}})</span></label>
					<input type="password" name="password" id="password" placeholder=""/>
				</div>
				@endif
				<p class="required_error" style="color: red; display: none">{{trans('messages.profile.invalid_email_address')}}</p>
				<button class="btn btn-theme w-100 mt-3 d-flex justify-content-between align-items-center" id="signup_form_submit" type="submit">{{trans('messages.profile.next_button')}} <i class="icon icon-right-arrow"></i></button>
			</form>
		</div>
	</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/jquery.session@1.0.0/jquery.session.js"></script>
<script type="text/javascript">

$('#phone_code').change(function() {
    $('#apply_country_code').val('');
    var phone_code = $(this).find('option:selected').data('id');
    if($(this).val())
    $('#apply_country_code').val(phone_code );
});

$( document ).ready(function() {
    var phone_code = $('#phone_code').find('option:selected').data('id');
   	if($('#phone_code').val())
 	$('#apply_country_code').val(phone_code);
});	

</script>
@stop
