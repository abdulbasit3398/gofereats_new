@extends('template2')

@section('main')
<main id="site-content" role="main" ng-controller="stores_detail" ng-init="payment_method={{$default_payment}}">
	<div class="container">
		<div class="profile user-payment py-5">
			<h1 class="text-center">{{trans('messages.profile.payment')}}</h1>
			<div class="d-md-flex">
				<div class="profile-img text-center col-12 col-md-3 col-lg-3">
					<img src="{{$profile_image}}"/>
					<h4>{{$user_details->name}}</h4>
				</div>
				<div class="profile-info col-12 col-md-8 col-lg-8 ml-lg-5 mt-4 offset-md-1 mt-md-0">
					<div class="methods">
						<div class="d-flex w-100 justify-content-between">
							<h3>{{trans('messages.profile.payment_methods')}}</h3>
							<a href="javascript:void(0)" data-toggle="modal" data-target="#payment-modal" class="theme-color method-btn"><i class="icon icon-add"></i></a>
						</div>
						<div class="method-info">
							<p>{{trans('messages.profile.new_payment_profile')}}</p>
						</div>
					</div>
					<div class="added-methods" ng-init="payment_details={{ $payment_details }};payment_method=1">
						<div class="added-info">
							<span><i class="icon icon-credit-card mr-2"></i> @lang('messages.profile.card_number') : xxxxxxxxxxxx @{{ payment_details.last4 }} </span><br>
							<span><i class="icon icon-credit-card mr-2"></i> @lang('messages.profile.card_type') : @{{ payment_details.brand }} </span><br>
						</div>
					</div>
					<p id="payment-error" class="mt-2 payment-error text-danger d-none"></p>
					@if($promo->count() > 0)
					<div class="promo-table">
						<div class="promo-head mt-3">
							<h3>{{trans('messages.profile.promo_details')}}</h3>
						</div>
						<div class="table-responsive">
							<table>
								<thead>
									<tr>
										<th>{{trans('messages.profile.promo_code')}}</th>
										<th>{{trans('messages.profile.amount')}}</th>
										<th>{{trans('messages.profile.percentage')}}</th>
										<th>{{trans('messages.profile.expired_date')}}</th>
										<th>{{trans('messages.store_dashboard.actions')}}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($promo as $promo_detail)
									<tr>
										<td>
											{{$promo_detail->promo_code->code}} 
											@if($promo_detail->promo_default == 1)
											<span class="text-danger">({{trans('messages.store.default')}})</span>
											@endif
										</td>
										<td>
											{{$promo_detail->promo_code->promo_type==0 ? session::get('currency').$promo_detail->promo_code->price:'' }}
										</td>
										<td>
											{{$promo_detail->promo_code->promo_type==1 ? $promo_detail->promo_code->percentage:'' }}
										</td>
										<td style="white-space: nowrap;">
											{{date('d-m-Y',strtotime($promo_detail->promo_code->end_date))}}
										</td>
										<td >
											<a href="javascript:void(0)" data-toggle="tooltip" class="payout-tooltip" data-placement="bottom"data-html="true" title="
											<a href='remove_promo_code_user/{{$promo_detail->promo_code->code}}'>
											{{ trans('messages.store.remove') }}</a>
											@if($promo_detail->promo_default == 0)
											<a href='set_default_promo/{{$promo_detail->promo_code->code}}'>
												{{ trans('messages.store.set_as_default') }}
											</a>
											@endif
											">
											<i class="icon icon-pencil-edit-button theme-color"></i>
										</a>
									</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@endif
					<div class="promotions">
						<form action="{{route('add_promo_code_data',['page'=>'web'])}}" method="POST">
							@csrf
							<div class="d-md-flex align-items-center pt-4 justify-content-between">
								<h3>{{trans('messages.profile.promotions')}}</h3>
								<div class="promo-input mt-3 mt-md-0 d-flex">
									<input type="text"  name="code" placeholder= {{trans('messages.profile.promo_code')}} value="">
									<button type="submit" class="btn btn-secondary">{{trans('messages.profile.apply')}}</button>
								</div>
							</div>
							<span class="text-danger">{{ $errors->first('code') }}</span>
						</form>
					</div>
					<br>
					<hr>

					<br>
					<div class="methods">
						<div class="d-flex w-100 justify-content-between">
							<h3>{{trans('messages.profile.wallet')}}</h3>
							<a href="javascript:void(0)" data-toggle="modal" data-target="#wallet-modal" data-backdrop="static"  class="theme-color method-btn"><i class="icon icon-add"></i></a>
						</div>
					</div>
					<div class="added-methods">
						<div class="added-info">
							<span><i class="icon icon-wallet mr-2"></i> @lang('messages.profile.wallet_amount') : {{$user_details->wallet_amount }} {{session::get('currency')}} </span><br>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<div class="modal fade payment-popup" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content payment-modal_load">
				<div class="modal-header">
					<h5 class="modal-title"> @lang('messages.store.add_payment_method') </h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">
							<i class="icon icon-close-2"></i>
						</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="card-form d-block">
						<form id="card_number_form">
							<div class="form-group card-number">
								<label> @lang('messages.profile.card_number') </label>
					<input type="text" name="card_number" id="card_number" placeholder="{{ trans('messages.profile.card_number') }}"  minlength="16"  maxlength="19" />
							</div>
							<div class="form-group d-block d-md-flex">
								<div class="col-12 col-md-9 p-0">
									<label> @lang('messages.store.expiration_date') </label>
									<div class="date-selection d-flex">
										<div class="select">
											{{ Form::selectRange('expire_month', 1, 12, '',['id'=>'expire_month']) }}
										</div>
										<div class="select">
											{{ Form::selectRange('expire_year', date('Y'), date('Y')+10, '',['id'=>'expire_year']) }}
										</div>
									</div>
								</div>
								<div class="col-12 col-md-3 p-0 mt-3 mt-md-0">
									<label> @lang('messages.store.cvv') </label>
									<input type="text" name="cvv_number" id="cvv_number" />
								</div>
							</div>
					
							<span id="error_add_card" class="text-danger error_add_card_new"></span>
							<button type="submit" class="w-100 btn btn-theme" id="payment_card" ng-click='updateCardDetails();'> @lang('messages.store.add_card') </button>
						</form>
						<div class="text-center mt-2">
							<a href="javascript:void(0)" data-dismiss="modal" class="back-btn theme-color"> @lang('messages.store.go_back') </a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade wallet-popup" id="wallet-modal" tabindex="-1" role="dialog" aria-labelledby="walletModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content payment-modal_load">
				<div class="modal-header">
					<h5 class="modal-title"> @lang('messages.store.choose_payment_option') </h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">
							<i class="icon icon-close-2"></i>
						</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="card-form d-block">
						<form id="card_number_form">
							<div class="form-group card-number">
								<label> @lang('messages.profile.amount') </label>
							<input type="text" style="width: 70%; margin:0 10px;" name="amount" id="amount" placeholder="{{ trans('messages.profile.amount') }}" maxlength="4" /> <span> {{session::get('currency')}} </span>
							</div>
							<select id="payment_method" ng-model="payment_method" ng-change="updatePaymentMethod()" ng-init="updatePaymentMethod()">
								<option  value="1">{{ trans('messages.store.debit_or_credit_card') }}</option>
								<option value="2">Paypal</option>
							</select>
							<span id="error_add_card" class="text-danger error_add_card_new"></span>
							<p id="paypal-button1" class="d-none"></p>
							<p id="payment-error" class="mt-2 payment-error text-danger d-none"></p>
							<input type="hidden" id="paypal_access_token" value="{{$paypal_access_token}}">
							<input type="hidden" id="currency_code" name="currency_code" value="{{$user_details->currency_code}}">
							@if(is_null($payment_details))
								<span class="text-danger before_add_card"> Please add card details </span>
							@endif
							<div class="card_wallet d-none">
								<div class="added-info">
									<span><i class="icon icon-credit-card mr-2"></i> @lang('messages.profile.card_number') : xxxxxxxxxxxx @{{ payment_details.last4 }} </span><br>
									<span><i class="icon icon-credit-card mr-2"></i> @lang('messages.profile.card_type') : @{{ payment_details.brand }} </span><br>
								</div>
								<button type="submit" class="w-100 btn btn-theme" id="card_wallet" ng-click='walletStripeAmount();'> @lang('messages.store.add_wallet') </button>
							</div>

							<p id="paypal-button" class="d-none"></p>
							<p id="payment-error" class="mt-2 payment-error text-danger d-none"></p>
						</form>

						<p id="error_place_wallet" class="mt-2 error_place_wallet" style="display: none;color:red">{{ trans('messages.store.location_is_required') }}</p>
						
						<div class="text-center mt-2">
							<a href="javascript:void(0)" data-dismiss="modal" class="back-btn theme-color"> @lang('messages.store.go_back') </a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection
@push('scripts')
	<script src="https://js.stripe.com/v3/"></script>
@endpush
