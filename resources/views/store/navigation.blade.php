<nav id="sidebar">
	<button id="sidebarCollapse" type="button" data-toggle="active" data-target="#sidebar" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="icon-bar"></span>
	</button>
	<ul class="list-unstyled components">
		<li class="{{navigation_active('store.dashboard') ? 'active':''}}">
			<a href="{{route('restaurant.dashboard')}}">
				<i class="icon icon-dashboard"></i>
				<span>{{ trans('admin_messages.dashboard') }}</span>
			</a>
		</li>

		<li class="d-md-none {{navigation_active('store.profile') ? 'active':''}}">
			<a href="{{url('restaurant/profile')}}">
				<i class="icon icon-user"></i>
				<span>{{ trans('messages.profile.profile') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('store.offers') ? 'active':''}}">
			<a href="{{route('restaurant.offers')}}">
				<i class="icon icon-offer"></i>
				<span>{{ trans('messages.store_dashboard.offers') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('store.payout_preference') ? 'active':''}}">
			<a href="{{route('restaurant.payout_preference')}}">
				<i class="icon icon-credit-card"></i>
				<span>{{ trans('messages.store_dashboard.payout_details') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('store.menu') ? 'active':''}}">
			<a href="{{route('restaurant.menu')}}">
				<i class="icon icon-store-eating-tools-set-of-three-pieces"></i>
				<span>{{ trans('admin_messages.category') }}</span>
			</a>
		</li>
		<li class="{{navigation_active('store.preparation') ? 'active':''}}">
			<a href="{{route('restaurant.preparation')}}">
				<i class="icon icon-timer"></i>
				<span>{{ trans('messages.store_dashboard.timings') }}</span>
			</a>
		</li>
		<li class="" style="position: relative;">
			<a data-toggle="collapse" class=""  href="#dropdown-lvl2"><i class="icon icon-trioangle"></i> <span >@lang('messages.profile.support')</span> <span class="caret cls_caret"></span></a>
			 <div id="dropdown-lvl2" class="panel-collapse collapse ">
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

		@if(isset($static_pages))
		<!-- 	<li>
				<a href="{{url($static_pages[0]->url)}}">
					<i class="icon icon-question-mark"></i>
					<span>Help</span>
				</a>
			</li> -->
		@endif

		@if(@get_current_store_id()!=='')
		<li class="d-md-none">
			<a href="{{route('restaurant.logout')}}">
				<i class="icon icon-logout"></i>
				<span>{{ trans('messages.profile.log_out') }}</span>
			</a>
		</li>
		@endif
	</ul>
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
@push('scripts')
<script type="text/javascript">
	$("a[name=mobile_number_tab]").on("click", function () { 
	    var mobile_number = $(this).attr("data-index");
	    $("#pop_up_mobile_number").text(mobile_number);
	});
</script>
@endpush