@extends('template2')

@section('main')

<main id="site-content" role="main" ng-controller="user_data">
	<div class="container">
		<div class="profile py-5">
			<h1 class="text-center">{{trans('messages.profile.profile')}}</h1>

			<div class="d-md-flex ">
				<div class="col-12 col-md-3 col-lg-3 driver">
					<div class="profile" style="height: 100%">
					<div class="profile-img text-center" style="height: 100%">
						<img src="{{$profile_image}}"/>
						<h4>{{$user_details->name}}</h4>
					<div class="pro-nav">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a data-toggle="collapse" class="nav-link"  href="#dropdown-lvl2">@lang('messages.profile.support') <span class="caret"></span></a>
								 <div id="dropdown-lvl2" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <ul class="">
									@foreach($support_links as $support_link)
									@if($support_link->id==1)
				                        @php $support_link->link = 'https://web.whatsapp.com/send?phone=+'.$support_link->link @endphp
				                    @elseif($support_link->id==2)
								        @php $support_link->link = 'skype:'.$support_link->link.'?chat' @endphp
								    @endif
									<li class="nav-item" style="display:block;width: 100%;">
										@if (is_numeric($support_link->link) || str_starts_with($support_link->link,'+') )
											<a href="javascript:void(0)" data-toggle="modal" data-target="#mobile_number_tab" name='mobile_number_tab' data-index='{{$support_link->link}}' class="nav-link"><img src="{{ $support_link->support_image }}" style="width: 45px;height:45px;margin:0px 10px 0px 0;object-fit: cover;display: inline-block;border-radius: 50%;"> 
		                                	<span>{{ $support_link->name }}</span></a>
										@else 
										   <a target="_blank" class="nav-link" href="{{ $support_link->link }}"><img src="{{ $support_link->support_image }}" style="width: 45px;height:45px;margin:0px 10px 0px 0;object-fit: cover;display: inline-block;border-radius: 50%;">
		                                	<span>{{ $support_link->name }}</span>
		                                	</a>
										@endif
		                                
		                            </li>
									@endforeach
								</ul>
								</div>
							</div>
							</li>
						</ul>
					</div>
				</div>
				</div>
				</div>
				<div class="profile-info col-12 col-md-8 col-lg-8 ml-lg-5 mt-4 offset-md-1 mt-md-0 user_profi">
					<h3>{{trans('messages.profile.general_information')}}</h3>

					<div class="row">
						<form class="mt-4 w-100" action="{{route('user_details_store')}}" method="POST" enctype="multipart/form-data">
						@csrf
							<div class="form-group d-md-flex">
								<div class="col-md-5">
									<label>{{trans('messages.driver.first_name')}}</label>
								</div>								
								<div class="col-md-7">
									<input type="text" name="first_name" id="first_name" value="{{$user_details->first_name}}">
									 <span class="text-danger">{{ $errors->first('first_name') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-5">
									<label>{{trans('messages.driver.last_name')}}</label>
								</div>
								<div class="col-md-7">
									<input type="text" name="last_name" id="last_name" value="{{$user_details->last_name}}">
									 <span class="text-danger">{{ $errors->first('last_name') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-5">
									<label>{{trans('messages.profile.location')}}</label>
								</div>
								<div class="col-md-7">
									<input type="text" name="user_address" id="user_address" value="{{($user_address)?$user_address->address:''}}" placeholder="{{trans('messages.profile.enter_a_location')}}">

									<input type="hidden" name="user_street" id="user_street" value="{{($user_address)?$user_address->street:''}}">
									<input type="hidden" name="user_city" id="user_city" value="{{($user_address)?$user_address->city:''}}">
									<input type="hidden" name="user_state" id="user_state" value="{{($user_address)?$user_address->state:''}}">
									<input type="hidden" name="user_country" id="user_country" value="{{($user_address)?$user_address->country:''}}">
									<input type="hidden" name="user_postal_code" id="user_postal_code" value="{{($user_address)?$user_address->postal_code:''}}">
									<input type="hidden" name="user_latitude" id="user_latitude" value="{{($user_address)?$user_address->latitude:''}}">
									<input type="hidden" name="user_longitude" id="user_longitude" value="{{($user_address)?$user_address->longitude:''}}">

									<span class="text-danger">{{ $errors->first('user_address') }}</span>
									<!-- <span class="text-danger">{{ $errors->first('user_city') }}</span>
									<span class="text-danger">{{ $errors->first('user_state') }}</span>
									<span class="text-danger">{{ $errors->first('user_country') }}</span> -->
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-5">
									<label>{{trans('messages.profile.mobile')}}</label>
								</div>
								<div class="col-md-7">
									<span>+{{$user_details->country_code}}</span> {{$user_details->mobile_number}}
									{{--<span class="d-block mt-2">Not Verified (<a href="#">resend</a>)</span>--}}
								</div>
							</div>

							<div class="form-group d-md-flex">
								<div class="col-md-5">
									<label>{{trans('messages.profile.email_address')}}</label>
								</div>
								<div class="col-md-7">
									<p class="m-0">{{$user_details->email}}</p>
								</div>
							</div>

							<div class="form-group d-md-flex m-0">
								<div class="col-md-5">
									<label>{{trans('messages.profile.profile_photo')}}</label>
								</div>
								<div class="col-md-7">
									<input type="file" name="profile_photo" id="profile_photo" style="visibility:hidden;">
									<a class="choose_file_type" id="profile_choose_file"><span id="profile_name">{{trans('messages.profile.choose_file')}}</span></a>
									<span class="upload_text" id="file_text"></span>
									 <span class="text-danger">{{ $errors->first('profile_photo') }}</span>
								</div>
							</div>

							<div class="profile-submit text-center mt-3 pt-3">
								<button type="submit" id="user_detail_store" class="btn btn-theme">{{trans('messages.profile.save')}}</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

		<div class="modal fade payment-popup" id="mobile_number_tab" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content payment-modal_load">
				<div class="modal-header">
					<h5 class="modal-title"> @lang('messages.profile_orders.contact_number')  </h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">
							<i class="icon icon-close-2"></i>
						</span>
					</button>
				</div>
				<div class="modal-body text-center">
				<span id=pop_up_mobile_number style="font-size: 22px;">  </span>
			</div>
			</div>
		</div>
	</div>
</main>    
@stop
@push('scripts')
<script type="text/javascript">

	$('#profile_choose_file').click(function(){
    	$('#profile_photo').trigger('click');
    	$('#profile_photo').change(function(evt) {
    			var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
        		$('#profile_choose_file').css("background-color","#43A422");
        		$('#profile_choose_file').css("color","#fff");
        		$('#profile_name').text(Lang.get('js_messages.file.file_attached'));
        		$('#file_text').text(fileName);
        		$('span.upload_text').attr('title',fileName)
    		});
    });

	$("a[name=mobile_number_tab]").on("click", function () { 
	    var mobile_number = $(this).attr("data-index");
	    $("#pop_up_mobile_number").text(mobile_number);
	});

</script>
@endpush
