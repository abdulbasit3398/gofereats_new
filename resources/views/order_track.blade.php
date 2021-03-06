@extends('template2')

@section('main')
<main id="site-content" role="main" ng-controller="orders_detail">
	<div class="track-map my-lg-5">
		<div class="row cls_trackorder">
			<div class="col-lg-8 col-12" style="position: relative;">
				<div class="d-flex flex-wrap">
					<div id="map" class="col-lg-8 pr-lg-0" style="width: 100%;object-fit: cover;">
						<img width="100%" src="{{$map_url}}">
					</div>
					<div class="cls_track col-lg-4 py-3" id="order_loading">
						@if($order_detail->status!=4 && $order_detail->status!=2)
						<ul class="track-order clearfix {{($order_detail->delivery_type =='delivery')?'':'cls_takeaway'}}">
							<li class="{{($order_detail->status>=3)?'active':''}}">
								<div class="track-img">
									<img src="{{url('/')}}/images/1.png"/>
								</div>
								@if($order_detail->status==1)
								<p>{{ trans('messages.profile_orders.confirming_order_with_store') }}
									<input type="hidden" id="order_id" value="{{$order_detail->id}}">
									<a href="#" data-toggle="modal" class="text-danger" data-target="#cancel_modal">&empty; {{ trans('messages.profile_orders.cancel_order') }}</a>
								</p>
								@endif
								@if($order_detail->status>=3)
								<p class="text-success" >{{ trans('messages.profile_orders.order_confirmed') }}</p>
								<span>{{$order_detail->accepted_at_time}}</span>
								@endif
							</li>
							@if($order_detail->delivery_type =='delivery')
							<li class="{{($order_detail->status>=3)?'active':''}}">
								<div class="track-img">
									<img src="{{url('/')}}/images/3.png"/>
								</div>
								<p>{{ trans('messages.profile_orders.food_is_being_prepared') }}</p>
								<span>{{$order_detail->delivery_at_time}}</span>
							</li>
							<li class="{{($order_detail->status >=5)?'active':''}}">
								<div class="track-img">
									<img src="{{url('/')}}/images/6.png"/>
								</div>
								<p>{{ trans('messages.profile_orders.courier_is_on_way') }}</p>
								<span>{{$order_detail->started_at_time}}</span>
							</li>
							<li class="{{($order_detail->status>=6)?'active':''}}">
								<div class="track-img">
									<img src="{{url('/')}}/images/4.png"/>
								</div>
								<p>{{ trans('messages.profile_orders.order_completed') }}</p>
								<span>{{$order_detail->completed_at_time}}</span>
							</li>
							@else 
								<li class="{{($order_detail->status ==8 || $order_detail->status >= 6 )?'active':''}}">
									<div class="track-img">
										<img src="{{url('/')}}/images/6.png"/>
									</div>
									<p>{{ trans('messages.profile_orders.collect_order') }}</p>

									@if($order_detail->status == 8 || $order_detail->status == 6 )

									<span>{{$order_detail->delivery_at_time}}</span>
									@elseif($order_detail->status == 3)	
										<span>{{$order_detail->estimation_delivery_time}}</span>
									@endif
								</li>
									
									<li class="{{($order_detail->status == 6 )? 'active':''}}">
										<div class="track-img">
											<img src="{{url('/')}}/images/4.png"/>
										</div>
										<p>{{ trans('messages.profile_orders.order_completed') }}</p>
										<span>{{$order_detail->completed_at_time}}</span>
									</li>
							
							@endif
						</ul>
						@elseif($order_detail->status==4)
						<h2 class="text-center">{{ trans('messages.profile_orders.your_order_is') }} {{trans('messages.profile_orders.'.$order_detail->status_text)}}
							<br>
							<small class="text-center">{{$order_detail->cancelled_at_time}}</small>
						</h2>
						@else
						<h2 class="text-center">{{ trans('messages.profile_orders.your_order_is') }} {{trans('messages.profile_orders.'.$order_detail->status_text)}}
							<br>
							<small class="text-center">{{$order_detail->declined_at_time}}</small>
						</h2>
						@endif
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-12 pr-lg-5 my-md-4 cls_tracklist">
				<div class="track-help">

					<p>{{ trans('messages.profile_orders.order_id') }} : #{{ $order_detail->id }}</p>
					<p>{{ trans('messages.modifiers.delivery_type') }} : {{ trans('messages.modifiers.'.$order_detail->delivery_type) }}</p>
					<p>{{ trans('messages.store.store_name') }} : <a href="{{ url('newdetails/'.$order_detail->store_id)}} ">{{ $order_detail->store->name }}</a></p>
					<p>{{ trans('messages.profile_orders.contact_support') }} ({{site_setting('site_support_phone')}})</p>
				</div>

				<div class="deliver-end d-none d-md-flex">
					<div class="cash-info w-50 theme-color">
						<p>
							<span>
								@if($order_detail->payment_type==0)
								{{ trans('messages.profile_orders.cash_on_delivery') }}

								@elseif($order_detail->payment_type==2)
								{{ trans('messages.profile.paypal') }}
								@elseif($order_detail->payment_type==1)
								{{ trans('messages.store.debit_or_credit_card') }}
								@endif
								@if($order_detail->wallet_amount > 0)
								 + 	{{ trans('messages.profile.wallet') }}
								@endif
								<span>:</span> 
							</span> <span><span>{!!$order_detail->currency->code!!} </span>{{$order_detail->total_amount}} </span></p>
						</div>

						<div class="deliver-user-info delivery_data {{($order_detail->status>=3)?'d-md-flex':''}}">
							<input type="hidden" id="order_status" value="{{$order_detail->status}}">
							@if($order_detail->driver)
							<div class="deliver-img text-center">
								<img src="{{@$order_detail->driver->driver_profile_picture}}"/>
								<div class="likes">
									{{@$order_detail->driver->review->user_driver_rating}} 
									<i class="icon icon-thumbs-up ml-1"></i>
								</div>
							</div>
							<div class="deliver-name">
								<h3>{{@$order_detail->driver->user->name}}</h3>
								<p>{{@$order_detail->driver->vehicle_name}} 
									<span>{{@$order_detail->driver->vehicle_number}}</span>
								</p>
								<div class="msg-tooltip">
									<i class="icon icon-comment"></i>
									<div class="tooltip-content">
										<p>{{ trans('messages.profile_orders.contact_number') }}
											<span>{{@$order_detail->driver->user->mobile_number_phone_code}}</span>
										</p>
									</div>
								</div>
							</div>
							@endif
						</div>
						@if($order_detail->delivery_type !='takeaway')
						<div class="deliver-time d-md-flex text-center ml-auto align-items-center">
							<p>
								
								@if($order_detail->status!=4 && $order_detail->status!=2)
								@if(!$order_detail->completed_at_time)
								<span>
									{{date('h:i',strtotime($order_detail->estimation_delivery_time)).' '.trans('messages.driver.'.date('a',strtotime($order_detail->estimation_delivery_time)))}}
								</span>
								@else
								<span>
									{{date('h:i',strtotime($order_detail->completed_at_time)).' '.trans('messages.driver.'.date('a',strtotime($order_detail->completed_at_time)))}}
								@endif </span>
								@elseif($order_detail->status==4)
								<span class="text-center">{{date('h:i',strtotime($order_detail->cancelled_at)).' '.trans('messages.driver.'.date('a',strtotime($order_detail->cancelled_at)))}}</span>
								@else
								<span class="text-center">{{date('h:i',strtotime($order_detail->declined_at)).' '.trans('messages.driver.'.date('a',strtotime($order_detail->declined_at)))}}

								</span>
								@endif

							</p>
						</div>
						@endif
				</div>

			</div>
				
			</div>
			<div class="modal fade" id="cancel_modal" role="dialog">
				<div class="modal-dialog">
					{!! Form::open(['url'=>route('cancel_order'),'method'=>'POST'])!!}
					<div class="modal-content">
						<div class="modal-header">
							<h3>{{ trans('messages.profile_orders.cancel_reason') }}</h3>
							<button type="button" class="close" data-dismiss="modal">
								<i class="icon icon-close-2"></i>
							</button>
						</div>
						<div class="modal-body">
							<div class="flash-container" id="popup1_flash-container"></div>
							<div id="select">
								<select id="cancel_reason" name="reason">
									@foreach($cancel_reason as $row=>$value)
									<option value="{{$value->id}}">{{$value->name}}</option>
									@endforeach
								</select>
							</div>
							<br>
							<input type="hidden" name="order_id" value="{{$order_detail->id}}">
							<textarea id="cancel_message" name="message" placeholder="{{trans('messages.profile_orders.comments')}}" style="width: 100%"></textarea>
							<div class="panel-footer mt-4">
								<input type="submit" value="{{ trans('messages.driver.submit') }}" class="btn btn-theme">
							</div>
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</main>
		@stop
