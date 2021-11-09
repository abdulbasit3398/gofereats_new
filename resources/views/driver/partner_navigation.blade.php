	<div class="profile-img text-center col-12 col-md-3 col-lg-3 d-none d-md-block">
					@if(Auth::guard('driver')->user()->driver->driver_profile_picture =='')
					<img src="{{@$profile_image}}" class="profile_picture" />
					@else
					<img src="{{@Auth::guard('driver')->user()->driver->driver_profile_picture}}" class="profile_picture"/>
					@endif
					@if($driver_details)
						<h4>{{str_replace('~',' ',$driver_details->name)}}</h4>
					@endif
					<div class="pro-nav">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.profile')}}">{{trans('messages.profile.profile')}}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.payment')}}">{{trans('messages.profile.earnings')}}</a>
							</li>
							<!-- <li class="nav-item">
								<a class="nav-link" href="{{route('driver.invoice')}}">Invoice</a>
							</li> -->
							<li class="nav-item ">
								<a class="nav-link" href="{{route('driver.trips')}}">{{trans('messages.profile.my_trips')}}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.logout')}}">{{trans('messages.profile.log_out')}}</a>
							</li>
							<li class="nav-item">
								<a data-toggle="collapse" class="nav-link"  href="#dropdown-lvl2">@lang('messages.profile.support') <span class="caret cls_caret"></span></a>
								 <div id="dropdown-lvl2" class="panel-collapse collapse cls_drivermenu">
                                    <div class="panel-body">
                                        <ul class="">
									@foreach($support_links as $support_link)
									@if($support_link->id==1)
				                        @php $support_link->link = 'https://web.whatsapp.com/send?phone=+'.$support_link->link @endphp
				                    @elseif($support_link->id==2)
								        @php $support_link->link = 'skype:'.$support_link->link.'?chat' @endphp
								    @endif
									<li class="nav-item">
										@if (is_numeric($support_link->link) || str_starts_with($support_link->link,'+') )
											<a href="javascript:void(0)" data-toggle="modal" data-target="#mobile_number_tab" name='mobile_number_tab' data-index='{{$support_link->link}}' class="nav-link"><img src="{{ $support_link->support_image }}" style="width: 45px;height:45px;margin:0px 10px 0px 0;object-fit: cover;display: inline-block;border-radius: 50%;"> 
			                            	<span>{{ $support_link->name }} </span></a>
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
</nav>

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
<script type="text/javascript">
	$("a[name=mobile_number_tab]").on("click", function () { 
	    var mobile_number = $(this).attr("data-index");
	    $("#pop_up_mobile_number").text(mobile_number);
	});
</script>