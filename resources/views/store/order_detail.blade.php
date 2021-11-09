@extends('template')

@section('main')
<main id="site-content" role="main" ng-controller="preparation_time">
	<div class="partners">
		@include ('store.navigation')
		<div class="pickup-times my-md-4 panel-content">
			<h1>{{ $form_name }}</h1>
			<div class="row align-items-baseline">
				<label class="col-sm-4 col-form-label">@lang('admin_messages.order_id')</label>
				<div class="col-sm-8">
					<div class="form-group">
						<p> {{@$order->id}}</p>
					</div>
				</div>
			</div>
			<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.user_name')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{@$order->user->name}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_name')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{@$order->store->name}}</p>
							</div>
						</div>
					</div>
				<!-- 	@if($order->driver_id)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_name')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{@$order->driver->user->name}}</p>
							</div>
						</div>
					</div>
					@endif -->
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.payment_type')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{@$order->payment_type_text }}</p>
							</div>
						</div>
					</div>
					@if($order->payment_type==1)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.transaction_id')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{@$order->payment->transaction_id}}</p>
							</div>
						</div>
					</div>
					@endif
					@foreach($order->order_item as $item)
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label"> {{ $item->quantity }} &nbsp; x &nbsp; {{ $item->menu_name }} </label>
							<div class="col-sm-8">
								<p>
									{!! currency_symbol() !!} {{ $item->total_amount }}
									<?php $check = 0; ?>
									@foreach($item->order_item_modifier as $order_item_modifier)
											@if($order_item_modifier->order_item_modifier_item->count())
											<?php $check= 1; ?>
											@endif
									@endforeach
									@if($check > 0)
									<a href="" data-toggle="collapse" data-target="#modifier_item_{{ $item->id }}" aria-expanded="false" aria-controls="collapseExample">
										<span class="ml-2 modifier_text_modifier_item_{{ $item->id }}"> show modifier items </span>
										<span class="ml-2 modifier_text_modifier_item_{{ $item->id }} d-none"> hide modifier items </span>
									    <i class="modifier_item_{{ $item->id }} fa fa-arrow-down" aria-hidden="true"></i>
									</a>
									@endif
									<div class="collapse m-0" id="modifier_item_{{ $item->id }}">
									  <div class="card card-body my-0 py-2 w-50">
									  	@foreach($item->order_item_modifier as $order_item_modifier)
											@foreach($order_item_modifier->order_item_modifier_item as $order_item_modifier_item)
											<div class="row align-items-baseline">
												<label class="col-sm-8 py-0">
													{{ (int)$order_item_modifier_item->count }} x
												{{ $order_item_modifier_item->modifier_item_name }} </label>
												<div class="col-sm-4">
													<p class="my-0 py-0"> {!! currency_symbol() !!} 
														{{ number_format($order_item_modifier_item->count * $order_item_modifier_item->price,2)}} </p>
												</div>
											</div>
											@endforeach
										@endforeach
									  </div>
									</div>
								</p>
							</div>
						</div>
					@endforeach
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.subtotal')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p>{!! currency_symbol() !!} {{@$order->subtotal}}</p>
							</div>
						</div>
					</div>
					@if($order->offer_amount>0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.offre_discount')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p>- {!! currency_symbol() !!}{{number_format_change($order->offer_amount)}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.tax')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!} {{@$order->tax}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.delivery_fee')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!} {{@$order->delivery_fee}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.booking_fee')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!} {{@$order->booking_fee}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_commision_fee')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!}{{number_format_change($order->store_commision_fee)}}</p>
							</div>
						</div>
					</div>
					<!-- <div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_commision_fee')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!}{{number_format_change($order->driver_commision_fee)}}</p>
							</div>
						</div>
					</div> -->
					@if($order->promo_amount > 0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.promo_amount')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> - {!! currency_symbol() !!}{{number_format_change($order->promo_amount)}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.total')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {!! currency_symbol() !!}  {{number_format_change(@$order->total_amount +$order->wallet_amount-$order->tips)}}
								</p>
							</div>
						</div>
					</div>
					@if($order->wallet_amount>0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.wallet_amount')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p>{!! currency_symbol() !!} {{$order->wallet_amount}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.status')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->status_text}}</p>
							</div>
						</div>
					</div>
					@if($order->status!=2)
						@if($order->accepted_at)
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.accepted_at')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {{$order->accepted_at}}</p>
								</div>
							</div>
						</div>
						@endif
						@if($order->status_text=='completed')
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.delivery_at')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->delivery_at}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.completed_at')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->completed_at}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.est_preparation_time')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->est_preparation_time}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.est_delivery_time')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->est_delivery_time}}</p>
							</div>
						</div>
					</div>
					@if($order->driver_id)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.pickup_location')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->order_delivery->pickup_location}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.drop_location')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->order_delivery->drop_location}}</p>
							</div>
						</div>
					</div>
					@endif

					@if($order->status_text=='cancelled')
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.cancelled_by')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->canceled_by_text}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.cancelled_reason')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->cancelled_reason_text}}</p>
							</div>
						</div>
					</div>
					@if($order->cancelled_message)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.cancelled_message')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->cancelled_message}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.cancelled_at')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->cancelled_at}}</p>
							</div>
						</div>
					</div>
					@endif
					@if($order->delay_min)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.delay_min')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->delay_min}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.delay_message')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->delay_message}}</p>
							</div>
						</div>
					</div>
					@endif
					@if($order->status_text=='declined')
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.declined_at')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->declined_at}}</p>
							</div>
						</div>
					</div>
					@endif
					@if(@$order->penality_details->user_penality > 0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.user_penality')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->penality_details->user_penality}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.user_notes')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->user_notes?$order->user_notes:'-'}}</p>
							</div>
						</div>
					</div>
					@if($order->get_store_payout('amount'))
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_payout')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_store_payout('amount')}}</p>
							</div>
						</div>
					</div>
					@if(@$order->penality_details->store_penality > 0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_penality')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->penality_details->store_penality}}</p>
							</div>
						</div>
					</div>
					@endif
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_payout_status')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_store_payout('status_text')}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.store_notes')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->store_notes?$order->store_notes:'-'}}</p>
							</div>
						</div>
					</div>
					@if($order->get_store_payout('transaction_id'))
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.transaction_id')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_store_payout('transaction_id')}}</p>
							</div>
						</div>
					</div>
					@endif
					@endif
					@if($order->get_user_payout('amount'))
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.user_payout')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_user_payout('amount')}}</p>
							</div>
						</div>
					</div>
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.user_payout_status')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_user_payout('status_text')}}</p>
							</div>
						</div>
					</div>
					@endif
					@endif
					

		</div>
	</div>
</main>
@stop