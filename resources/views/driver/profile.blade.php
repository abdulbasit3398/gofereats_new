@extends('driver.template')

@section('main')
<div class="flash-container">
	@if(Session::has('message'))
	<div class="alert {{ Session::get('alert-class') }} text-center" role="alert">
		<a href="#" class="alert-close" data-dismiss="alert">&times;</a> {{ Session::get('message') }}
	</div>
	@endif
</div>
<main id="site-content" role="main" class="log-user driver" ng-controller="driver_signup">
	<div class="container">
		<div class="profile mb-5">
			<div class="d-md-flex">
			@include('driver.partner_navigation')
				<div class="profile-info col-12 col-md-9 col-lg-9">
					<div class="row d-block">
						<div class="profile-title py-md-4">
							<h1 class="text-center text-uppercase">{{trans('messages.profile.profile')}}</h1>
						</div>
						<div class="pro-photo py-4 col-12 d-md-flex align-items-center justify-content-between text-center text-md-left">
							<div class="col-md-6">

								@if($driver_details)
								<h4>{{str_replace('~',' ',$driver_details->name)}}</h4>
								@if($driver_details->status==1)
								<label class="active-label my-2">{{trans('messages.profile.active')}}</label>
								@elseif($driver_details->status== 4)
								<label class="label my-2">
								{{trans('messages.profile.pending')}}</label>
								@if($driver_details->documents->count() < 5)
								<label>
								{{trans('messages.profile.document_details')}}</label>
								@endif
								@if(is_null($driver_details->vehicle_type))
								<label>
								{{trans('messages.profile.vehicle_details')}}</label>
								@endif
								@else
								<label>{{trans('messages.driver.'.$driver_details->user->status_text_show)}}</label>
								@endif
								@endif
							</div>
							<div class="col-md-6 mt-3 mt-md-0">
								<button type="button" class="btn btn-theme" ng-click="selectFile()">{{trans('messages.profile.add_photo')}}</button>

								<input type="file" ng-model="profile_image" style="display:none" accept="image/*" id="file" name='profile_image' accept=".jpg,.jpeg,.png" onchange="angular.element(this).scope().fileNameChanged(this)" />
							</div>
						</div>
						<div class="manage-doc text-center text-md-left py-4 col-12">
							<a class="m-1 m-md-0 d-inline-block" href="{{url('/driver/documents').'/'.$driver_details->id}}">
								<button type="button" class="btn btn-theme">{{trans('messages.profile.manage_documents')}}</button>
							</a>
							<a class="m-1 m-md-0 d-inline-block" href="{{route('driver.vehicle_details')}}">
								<button type="button" class="btn btn-theme">{{trans('messages.profile.vehicle_details')}}</button>
							</a>
						</div>

						{!! Form::open(['url'=>route('driver.profile'),'method'=>'post','class'=>'mt-4' , 'id'=>'profile_update_form'])!!}
						<div class="form-group d-md-flex">
							<div class="col-md-5">
								<label class="required-a"> @lang('messages.driver.first_name') </label>
							</div>
							<div class="col-md-7">
								<input type="text" name="first_name" placeholder="@lang('messages.driver.first_name')" value="{{ $driver_details->user->first_name }}">
								<span class="text-danger">{{ $errors->first('first_name') }}</span>
							</div>
						</div>
						<div class="form-group d-md-flex">
							<div class="col-md-5">
								<label class="required-a"> @lang('messages.driver.last_name') </label>
							</div>
							<div class="col-md-7">
								<input type="text" name="last_name" placeholder="@lang('messages.driver.last_name')" value="{{ $driver_details->user->last_name }}">
								<span class="text-danger">{{ $errors->first('last_name') }}</span>
							</div>
						</div>
						<div class="form-group d-md-flex">
							<div class="col-md-5">
								<label class="required-a">{{trans('messages.profile.email_address')}}</label>
							</div>
							<div class="col-md-7">
								<input type="text" name="email" placeholder="{{trans('messages.profile.email_address')}}" value="{{$driver_details->user->email}}">
								<span class="text-danger">{{ $errors->first('email') }}</span>
							</div>
						</div>
						<input type="hidden" name="apply_country_code" id="apply_country_code" value=""/>
						<div class="form-group d-md-flex">
							<div class="col-md-5">
								<label class="required-a">{{trans('messages.driver.phone')}}</label>
							</div>
							<div class="col-md-7 ">
								<div class="d-md-flex">

								<div class="select mob-select col-md-3 ">
								<span class="phone_code">+{{$driver_details->user->country_code}}</span>
									<select id="phone_code" name="country_code" class="form-control">
						                    @foreach ($country_code as $key => $country)
						                        <option value="{{ $country->phone_code }}"
						                        data-id="{{ $country->id }}"{{ $country->phone_code == $driver_details->user->country_code ? 'selected' : '' }} >{{ $country->name }}</option>
						                    @endforeach
						                </select>
						            </div>
								<input type="text" name="mobile" id ='mobile'placeholder="{{trans('messages.profile.phone_number')}}" value="{{$driver_details->user->mobile_number}}">
							</div>
							<span class="text-danger">{{ $errors->first('mobile') }}</span>	
							</div>	
							
						</div>

						<div class="form-group d-md-flex">
							<div class="col-md-5">
								<label class="required-a">{{trans('admin_messages.date_of_birth')}}</label>
							</div>
							<div class="col-md-7">
								<!-- <input type="text" id="driver_dob" name="date_of_birth" value="{{set_date_on_picker($driver_details->user->date_of_birth)}}" > -->
								<input type="text" id="driver_dob" name="date_of_birth" value="{{set_date_on_picker($driver_details->user->date_of_birth)}}" autocomplete="off">
								<span class="text-danger">{{ $errors->first('date_of_birth') }}</span>
							</div>
						</div>
						<div class="profile-submit col-12 mt-4 pt-3">
							<button type="submit" class="btn btn-theme">{{trans('messages.profile.update')}}</button>
						</div>
						{{ Form::close() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<script type="text/javascript">
	$('#phone_code').change(function() {
    $('#apply_country_code').val('');
    var phone_code = $(this).find('option:selected').data('id');
    console.log(phone_code);
    if($(this).val())
    	$('#apply_country_code').val(phone_code);
	});
	$( document ).ready(function() {
	    var phone_code = $('#phone_code').find('option:selected').data('id');
	   	if($('#phone_code').val())
	 		$('#apply_country_code').val(phone_code );
	});	
</script>

@stop