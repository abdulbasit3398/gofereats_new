@extends('admin.template')
@section('main')
<div class="content">
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-rose card-header-text">
					<div class="card-text">
						<h4 class="card-title"> @lang('admin_messages.edit_order')</h4>
					</div>
				</div>
				<div class="card-body">
					{!! Form::open(['url' => route('admin.admin_payout'), 'class' => 'form-horizontal','id'=>'order_form']) !!}
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
					@if($order->driver_id)
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_name')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {{@$order->driver->user->name}}</p>
								</div>
							</div>
						</div>
					@endif
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
								<!-- @if($order->offer_amount > 0)
									<p ><strike> {!! currency_symbol() !!} {{ $item->price }}</strike> </p>
								@endif -->
								<p>
									{!! currency_symbol() !!} {{ $item->total_amount }}
									</p>
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
						<!-- @if($order->offer_amount>0)
							<div class="row align-items-baseline">
								<label class="col-sm-4 col-form-label">@lang('admin_messages.offre_discount')</label>
								<div class="col-sm-8">
									<div class="form-group">
										<p>- {!! currency_symbol() !!}{{number_format_change($order->offer_amount)}}</p>
									</div>
								</div>
							</div>
						@endif -->
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.delivery_type')</label>
							<div class="col-sm-8">
								<div class="form-group">
								 {{@$order->delivery_type}}</p>
								</div>
							</div>
						</div>
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.tax')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {!! currency_symbol() !!} {{@$order->tax}}</p>
								</div>
							</div>
						</div>
						@if($order->status_text =='completed')
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">Ratings & Commments</label>
							<div class="col-sm-8">
								<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
								  <div class="panel panel-default">
								    <div class="panel-heading cls_reviewcol" role="tab" id="headingOne">
								      <h4 class="panel-title">
								        <a class="accordion-toggle collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
								         Ratings & Commments <span class="caret"></span>
								        </a>
								      </h4>
								    </div>
								    <div id="collapseOne" class="panel-collapse collapse cls_reviewtxt" role="tabpanel" aria-labelledby="headingOne">
								      <div class="panel-body">
								      
										<div class="card" style="width: 100%">
										  <div class="card-header">
										 <strong class="card-link">Menu Item<span></span></strong>
										</div>
										<div class="card-body" style="padding-top: 0px">
											@foreach($order->order_item as $item)
											<div class="">
												<label style="color: #484848;"> {{ $item->menu_name }} </label><br>
												@if(isset($item->review->is_thumbs))
													@if($item->review->is_thumbs == '1')
														<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-up"></i> </span>
													@elseif(@$item->review->is_thumbs == '0') 
														<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-down"></i> </span>
													@endif
													<br>
												@endif
												<strong class="card-link">Comments : <span>
													{{@$item->review->review_comments->issues}}
													<strong style="@if(@$item->review->review_comments->comments) background: #565050;color: white;padding: 2px;   border-radius: 5px;@endif">{{@$item->review->review_comments->comments}}</strong> </span></strong>	
											</div>
											@endforeach
										   <br>
										    <strong class="card-link">Store Rating</strong>
										    	<div class="rating">
												    <div class="rating-upper" style="width:{{@$order->review->store_rating * 20}}%">
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												    </div>
												    <div class="rating-lower">
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												        <span>★</span>
												    </div>
												</div>
											<br>
											@if($order->review)
											    @if(@$order->delivery_type =='delivery')
													<strong class="card-link">User Driver Delivery</strong>
													@if(isset($order->review->user_to_driver_rating->is_thumbs))
														@if(@$order->review->user_to_driver_rating->is_thumbs == 1)
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-up"></i> </span>
														@elseif(@$order->review->user_to_driver_rating->is_thumbs == 0) 
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-down"></i> </span>
														@endif
													@endif
													<br>
													<strong class="card-link"> Comments : <span>
													{{@$order->review->user_to_driver_comments->issues}} 
													<strong style="@if(@$order->review->user_to_driver_comments->comments) background: #565050;color: white;padding: 2px;   border-radius: 5px;@endif"> {{@$order->review->user_to_driver_comments->comments}} </strong>
											    <br>
											    <strong class="card-link">Driver About Delivery</strong>
											    	@if(isset($order->review->driver_delivery_rating))
														@if(@$order->review->driver_delivery_rating->is_thumbs == 1)
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-up"></i> </span>
														@elseif(@$order->review->driver_delivery_rating->is_thumbs == 0 ) 
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-down"></i> </span>
														@endif<br>
													@endif</br>
													<strong class="card-link"> Comments :<span>
													{{@$order->review->driver_delivery_comments->issues}}	
													<strong style="@if(@$order->review->driver_delivery_comments->comments)background: #565050;color: white;padding: 2px;   border-radius: 5px;@endif"> {{@$order->review->driver_delivery_comments->comments}}</strong></span></strong>
												<br>
												@endif	
												@endif
												<br>
												<strong class="card-link">Store About The Order</strong>
													@if(isset($order->review->store_delivery_rating->is_thumbs))
														@if(@$order->review->store_delivery_rating->is_thumbs == 1)
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-up"></i> </span>
														@elseif(@$order->review->store_delivery_rating->is_thumbs == 0 ) 
															<span style="font-size: 20px;margin-left: 5px"><i class="fa fa-thumbs-down"></i> </span>
														@endif
													@endif	
													<br>
													<strong class="card-link"> Comments:<span>
													@if($order->review)
													{{@$order->review->store_delivery_comments->issues}}	
														@if($order->review->store_delivery_comments)
														<strong style="@if($order->review->store_delivery_comments->comments) background: #565050;color: white;padding: 2px;   border-radius: 5px; @endif">  
														<strong style="@if(@$order->review->store_delivery_comments->comments) background: #565050;color: white;padding: 2px;   border-radius: 5px;@endif">{{@$order->review->store_delivery_comments->comments}}</strong></span></strong>	
														</strong>
													</span></strong>	
													@endif
												@endif
										  </div>
										</div>
										
								      </div>
								    </div>
								  </div>
								</div>
								
							</div>
						</div>
						@endif
					@if(@$order->delivery_type == 'delivery')
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.delivery_fee')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {!! currency_symbol() !!} {{@$order->delivery_fee}}</p>
								</div>
							</div>
						</div>
					@endif
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
					@if($order->delivery_type == 'delivery')
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_commision_fee')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {!! currency_symbol() !!}{{number_format_change($order->driver_commision_fee)}}</p>
								</div>
							</div>
						</div>
					@endif
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
									<p> {!! currency_symbol() !!}  {{number_format_change(@$order->total_amount )}}</p>
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
					@if($order->tips>0)
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.tips')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p>{!! currency_symbol() !!} {{$order->tips}}</p>
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
						@if($order->store->user->default_payout_preference && $order->get_store_payout('amount'))
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.store_payout_preference')</label>
							<div class="col-sm-8">
								<p><b> {{$order->store->user->default_payout_preference->payout_method}}</b></p>
								@if($order->store->user->default_payout_preference->payout_method!='BankTransfer')
									<div class="form-group">
										<p> {{$order->store->user->default_payout_preference->paypal_email}}</p>
									</div>
								@else
									<div class="form-group">
										<p>
											<span>Country: </span> <span> {{$order->store->user->default_payout_preference->get_country->name}}</span>
										</p>
										<p>
											<span>Account Number: </span> <span> {{$order->store->user->default_payout_preference->account_number}}</span>
										</p>
										<p>
											<span>Account Holder Name: </span> <span> {{$order->store->user->default_payout_preference->holder_name}}</span>
										</p>
										<p>
											<span>Bank Name: </span> <span> {{$order->store->user->default_payout_preference->bank_name}}</span>
										</p>
										<p>
											<span>Bank Location: </span> <span> {{$order->store->user->default_payout_preference->branch_name}}</span>
										</p>
										<p>
											<span>Bank Code: </span> <span> {{$order->store->user->default_payout_preference->bank_location}}</span>
										</p>
									</div>



								@endif
							</div>
						</div>
						@endif
						@if($order->driver && $order->get_driver_payout('amount'))
							@if($order->driver->user->default_payout_preference)
							<div class="row align-items-baseline">
								<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_payout_preference')</label>
								<div class="col-sm-8">
									<p><b> {{$order->driver->user->default_payout_preference->payout_method}}</b></p>
									@if($order->driver->user->default_payout_preference->payout_method!='BankTransfer')
										<div class="form-group">
											<p> {{$order->driver->user->default_payout_preference->paypal_email}}</p>
										</div>
									@else
										<div class="form-group">
											<p>
												<span>Country: </span> <span> {{$order->driver->user->default_payout_preference->get_country->name}}</span>
											</p>
											<p>
												<span>Account Number: </span> <span> {{$order->driver->user->default_payout_preference->account_number}}</span>
											</p>
											<p>
												<span>Account Holder Name: </span> <span> {{$order->driver->user->default_payout_preference->holder_name}}</span>
											</p>
											<p>
												<span>Bank Name: </span> <span> {{$order->driver->user->default_payout_preference->bank_name}}</span>
											</p>
											<p>
												<span>Bank Location: </span> <span> {{$order->driver->user->default_payout_preference->branch_name}}</span>
											</p>
											<p>
												<span>Bank Code: </span> <span> {{$order->driver->user->default_payout_preference->bank_location}}</span>
											</p>
										</div>



									@endif
								</div>
							</div>
							@endif
						@endif
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
					@if($order->delivery_type =='delivery')
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
					@else
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.store_payout_status')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {{$order->get_store_payout('status_text')}} </p>
								</div>
							</div>
						</div>
					@endif
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
					@if($order->get_driver_payout('amount'))
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_payout')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->get_driver_payout('amount')}}</p>
							</div>
						</div>
					</div>
					@if(@$order->penality_details->driver_penality > 0)
					<div class="row align-items-baseline">
						<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_penality')</label>
						<div class="col-sm-8">
							<div class="form-group">
								<p> {{$order->penality_details->driver_penality}}</p>
							</div>
						</div>
					</div>
					@endif
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_payout_status')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {{$order->get_driver_payout('status_text')}}</p>
								</div>
							</div>
						</div>
						<div class="row align-items-baseline">
							<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_notes')</label>
							<div class="col-sm-8">
								<div class="form-group">
									<p> {{$order->driver_notes?$order->driver_notes:'-'}}</p>
								</div>
							</div>
						</div>
						@if($order->get_driver_payout('transaction_id'))
							<div class="row align-items-baseline">
								<label class="col-sm-4 col-form-label">@lang('admin_messages.transaction_id')</label>
								<div class="col-sm-8">
									<div class="form-group">
										<p> {{$order->get_driver_payout('transaction_id')}}</p>
									</div>
								</div>
							</div>
							@endif
							@elseif($order->sub_driver_payout>0)
							<div class="row align-items-baseline">
								<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_payout')</label>
								<div class="col-sm-8">
									<div class="form-group">
										<p> {{$order->sub_driver_payout}}</p>
									</div>
								</div>
							</div>
							<div class="row align-items-baseline">
								<label class="col-sm-4 col-form-label">@lang('admin_messages.driver_payout_status')</label>
								<div class="col-sm-8">
									<div class="form-group">
										<p>@lang('admin_messages.payout_subtracted_owe')</p>
									</div>
								</div>
							</div>
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
			@if($order->get_store_payout('status_text'))		
					@if(($order->status_text=='cancelled'||$order->status_text=='completed') && $order->payout_is_create!=1)
					<div class="order-penalty-wrap mb-3 mb-md-0">
							<div class="row flex-column">
								<div class="d-md-flex">
									<div class="col-md-3 text-md-right">
										<h5 class="text-capitalize mb-3 m-md-0">
										User
										</h5>
									</div>
									@if($order->is_refund_to_user)
									<div class="col-md-3">
										<label class="m-0">
											@lang('admin_messages.refund_to_user')
										</label>
										<div class="form-group m-0">
											<p>
												{!! Form::text('refund_to_user','',['class'=>'form-control'])!!}
											</p>
											<span class="text-danger">
												{{ $errors->first('refund_to_user') }}
											</span>
										</div>
									</div>
									<div class="col-md-4">
										@else
										<div class="col-md-3">
											@endif
											<label class="m-0">
												@lang('admin_messages.penalty_to_user')
											</label>
											<div class="d-flex">
												<div class="form-check p-0 m-0">
													<label class="form-check-label">
														<input type="checkbox" name="is_penalty_to_user" class="form-check-input" />
														<span class="form-check-sign">
															<span class="check"></span>
														</span>
													</label>
												</div>
												<div class="flex-grow-1">
													{!! Form::text('penalty_to_user','',['class'=>'form-control'])!!}
													<span class="text-danger">{{ $errors->first('penalty_to_user') }}</span>
												</div>
											</div>
											<span class="d-block mt-2">
												User Penalty Amount {{ $user_penality }}
											</span>
											<span class="d-block mt-1">
												(@lang('admin_messages.is_penalty_to'))
											</span>
										</div>
									</div>
									<div class="d-md-flex">
										<div class="col-md-3">
										</div>
										<div class="col-md-7 mt-3 mt-md-0">
											<label class="m-0">
												Notes
											</label>
											<textarea name="user_notes" class="form-control"></textarea>
										</div>
									</div>
								</div>
								@if($order->get_store_payout('status')!=1)
								<div class="row flex-column">
									<div class="d-md-flex">
										<div class="col-md-3 text-md-right">
											<h5 class="text-capitalize mb-3 m-md-0">Store</h5>
										</div>
										<div class="col-md-3">
											<label class="m-0">
												@lang('admin_messages.payout_to_store')
											</label>
											<div class="form-group m-0">
												<p> {!! Form::text('payout_to_store',$order->get_store_payout('amount'),['class'=>'form-control'])!!}</p>
												<span class="text-danger">{{ $errors->first('payout_to_store') }}</span>
												@if($order->status_text=='completed')
												@if($order->penality_details)
												<span>Applied Penalty {{currency_symbol().$order->penality_details->previous_store_penality}}</span>
												@endif
												@endif
											</div>
										</div>
										<div class="col-md-4">
											<label class="m-0">
												@lang('admin_messages.penalty_to_store')
											</label>
											<div class="d-flex">
												<div class="form-check p-0 m-0">
													<label class="form-check-label">
														<input type="checkbox" name="is_penalty_to_store" class="form-check-input"/>
														<span class="form-check-sign">
															<span class="check"></span>
														</span>
													</label>
												</div>
												<div class="flex-grow-1">
													{!! Form::text('penalty_to_store','',['class'=>'form-control'])!!}
													<span class="text-danger">{{ $errors->first('penalty_to_store') }}</span>
												</div>
											</div>
											<span class="d-block mt-2">
												Store Penalty Amount {{ $store_penality }}
											</span>
											<span class="d-block mt-1">
												(@lang('admin_messages.is_penalty_to'))
											</span>
										</div>
									</div>
									<div class="d-md-flex">
										<div class="col-md-3">
										</div>
										<div class="col-md-7 mt-3 mt-md-0">
											<label class="m-0">
												Notes
											</label>
											<textarea name="store_notes" class="form-control"></textarea>
										</div>
									</div>
								</div>
								@endif
								@if($order->driver && $order->get_driver_payout('status')!=1)
								<div class="row flex-column">
									<div class="d-md-flex">
										<div class="col-md-3 text-md-right">
											<h5 class="text-capitalize mb-3 m-md-0">
											driver
											</h5>
										</div>
										<div class="col-md-3">
											<label class="m-0">
												@lang('admin_messages.payout_to_driver')
											</label>
											<div class="form-group m-0">
												<p> {!! Form::text('payout_to_driver',$order->get_driver_payout('amount'),['class'=>'form-control'])!!}</p>
												<span class="text-danger">{{ $errors->first('payout_to_driver') }}</span>
												@if($order->status_text=='completed')
												<!-- <span>Applied Owe Amount {{currency_symbol()}}{{$order->owe_amount>0?$order->owe_amount:0}}</span>	 -->
												@endif
											</div>
										</div>
										<div class="col-md-4">
											<label class="m-0">
												@lang('admin_messages.penalty_to_driver')
											</label>
											<div class="d-flex">
												<div class="form-check p-0 m-0">
													<label class="form-check-label">
														<input type="checkbox" name="is_penalty_to_driver" class="form-check-input" />
														<span class="form-check-sign">
															<span class="check"></span>
														</span>
													</label>
												</div>
												<div class="flex-grow-1">
													{!! Form::text('penalty_to_driver','',['class'=>'form-control'])!!}
													<span class="text-danger">
														{{ $errors->first('penalty_to_driver') }}
													</span>
												</div>
											</div>
											<span class="d-block mt-2">
												Driver Owe Amount {{$order->owe_amount}}
											</span>
											<span class="d-block mt-1">
												(@lang('admin_messages.is_penalty_to'))
											</span>
										</div>
									</div>
								</div>
								<div class="d-md-flex">
									<div class="col-md-3">
									</div>
									<div class="col-md-7 mt-3 mt-md-0">
										<label class="m-0">
											Notes
										</label>
										<textarea name="driver_notes" class="form-control"></textarea>
									</div>
								</div>
							</div>
					</div>
				</div>
				@endif
				<input type="hidden" name="order_id" value="{{$order->id}}">
				<div class="form-group text-center m-0 p-0 mt-md-4">
					<button type="submit" class="btn btn-fill btn-rose">
					{{$order->is_payout_create_or_not['button']}}
					</button>
				</div>
				@endif
				@endif
				<div class="card-footer">
					<div class="ml-auto">
						@if($order->status_text!='cancelled' && $order->status_text!='declined' && $order->status_text!='cart'&& $order->status_text!='expired' && $order->status_text!='completed' && $order->status_text!='pending')
						<a  href="javascript:void(0)" class="order_cancel_show btn btn-fill btn-rose btn-wd">
							@lang('admin_messages.cancel_order')
						</a>
						@endif
						@if($order->payout_is_create==1)
						@if($order->get_store_payout('amount') > 0 && $order->get_store_payout('status_text')=='pending')
							@if($order->store->user->payout_id)
							<a  href="{{route('admin.amount_payout',['user_id'=>$order->store->user_id,'order_id'=>$order->id])}}" class="btn btn-fill btn-rose btn-wd">
								@lang('admin_messages.payout_to_store')   ({{currency_symbol().' '.$order->get_store_payout('amount')}})
							</a>
							@else
							<!-- <button type="button" class="btn btn-danger"> Add Store payout to complete this payment </button> -->
							<a href="javascript:{}" id="add_needpay_out_resturant"  class="btn btn-danger">Add Store payout to complete this payment</a>
							@endif
						@endif
						@if($order->get_driver_payout('amount') > 0 && $order->get_driver_payout('status_text')=='pending')
						@if($order->driver->user->payout_id)
						<a href="{{route('admin.amount_payout',['user_id'=>$order->driver->user_id,'order_id'=>$order->id])}}" class="btn btn-fill btn-rose btn-wd">
							@lang('admin_messages.payout_to_driver') ({{currency_symbol().' '.@$order->get_driver_payout('amount')}})
						</a>
						@else

						<a href="javascript:{}" id="add_needpay_out_driver" class="btn btn-danger">Add Driver payout to complete this payment</a>

						@endif
						@endif
						@endif
					</div>
				</div>	
			
			{!! Form::close() !!}
		</div>
	</div>
</div>
</div>
</div>
@isset($order->driver)
<form method="post" action="{{route('admin.need_payout_info')}}" class="text-right" id="add_needpay_out">
	@csrf
	<input type="hidden" name="user_id" id="user_id" value="">
	<input type="hidden" name="type" id="type" value="" >	
	<input type="hidden" name="order_id" value="{{$order->id}}">
</form>
@endisset

@if($order->delivery_type =='takeaway' && $order->store)
<form method="post" action="{{route('admin.need_payout_info')}}" class="text-right" id="add_needpay_out">
	@csrf
	<input type="hidden" name="user_id" id="user_id" value="">
	<input type="hidden" name="type" id="type" value="" >	
	<input type="hidden" name="order_id" value="{{$order->id}}">
</form>
@endisset
<!--Order cancel !-->
<div class="modal fade" id="order_cancel" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">
		<i class="icon icon-close-2"></i>
		</button>
		<h4 class="text-danger modal-title delete_item_msg"></h4>
	</div>
	<form method="post" action="{{route('admin.cancel_order')}}">
		@csrf
		<div class="modal-body">
			<div class="row">
				<label class="col-sm-4 col-form-label mt-2">@lang('admin_messages.cancel_reason')</label>
				<div class="col-sm-8">
					<div class="form-group">
						@if($order->delivery_type == 'delivery')
						{!!Form::select('cancel_reson',$cancel_reason,'',['class'=>'form-control']) !!}
						@else
						{!!Form::select('cancel_reson',$takeaway_cancel_reason,'',['class'=>'form-control']) !!}
						@endif
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-4 col-form-label mt-2">@lang('admin_messages.cancel_message')</label>
				<div class="col-sm-8">
					<div class="form-group">
						<textarea class="form-control" name="cancel_message" placeholder="Cancel message"> </textarea>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="order_id" value="{{$order->id}}">
		<div class="modal-footer text-right">
			<button data-dismiss="modal" type="reset" class="btn">Close</button> &nbsp;&nbsp;
			<button  type="submit" class="btn btn-rose"> @lang('admin_messages.cancel_order')</button>
		</div>
	</form>
</div>
</div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
	$('.order_cancel_show').click(function(){
		$('#order_cancel').modal();
	})

	$('#add_needpay_out_driver').click(function(){
		$('#type').val('driver')	;
		$('#user_id').val('{{$order->driver->user_id ?? 0}}');
		 $('#add_needpay_out').submit();
	});

	$('#add_needpay_out_resturant').click(function(){
		$('#user_id').val('{{$order->store->user_id}}');
		var store = $('#type').val("store");
		// console.log("dfhgf "+store);
		// console.log(JSON.stringify(store));
		$('#add_needpay_out').submit();
	})

	function toggleIcon(e) {
		$('.modifier_text_'+e.target.id).toggleClass('d-none');
        $('.'+e.target.id).toggleClass('fa-arrow-down fa-arrow-up');
    }
	$('.collapse').on('hidden.bs.collapse', toggleIcon);
    $('.collapse').on('shown.bs.collapse', toggleIcon);
</script>
@endpush
