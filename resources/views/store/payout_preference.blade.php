@extends('template')

@section('main')
<main id="site-content" role="main" ng-controller="payout_preferences1" ng-cloak>
	<div class="partners">
		@include ('store.navigation')
		<div class="partner-payments mt-4 mb-5">
			<h1>{{trans('messages.store.payouts')}}</h1>
			<div class="my-4">
				
				<div class="week-activity mt-3 d-md-flex">
					<div class="col-md-4">
						<span class="d-block light-color mb-2">{{trans('messages.store_dashboard.net_earnings')}}</span>
						<h4>{!!$current_week_symbol!!} {{$current_week_profit}}</h4>
					</div>
					<div class="col-md-4">
						<span class="d-block light-color mb-2">{{trans('messages.profile.orders')}}</span>
						<h4>{{$current_week_orders}}</h4>
					</div>
					@if($current_week_orders != 0)
						<div class="col-md-4">	
							<span class="d-block light-color mb-2">{{trans('messages.store.next_payment')}}</span>
							<h4>{{date('d', strtotime('next monday')).' '.trans('messages.driver.'.date('M', strtotime('next monday')))}}</h4>
						</div>
					@endif
				</div>
			</div>
			<div class="payment-history my-5">
				<h5>{{trans('messages.store.payout_history')}}</h5>
				<div class="table-responsive">
					<table>
						<thead>
							<tr>
								<th>{{trans('messages.store.week_of')}}</th>
								<th>{{trans('messages.profile.orders')}}</th>
								<th>{{trans('messages.store.sale')}}</th>
								<th>{{trans('messages.profile_orders.tax')}}</th>
								<th>{{trans('messages.profile_orders.total')}}</th>
								<th>{{ site_setting('site_name') }} {{trans('messages.store.fee')}}</th>
								<th>{{trans('messages.store_dashboard.net_payout')}}</th>
								<th>{{trans('messages.store.payout_status')}}</th>
								<th>{{trans('admin_messages.penalty')}}</th>
								<th>{{trans('messages.store.paid_penalty')}}</th>
								<th>{{trans('messages.profile_orders.status')}}</th>
								<th>{{trans('messages.store.weekly_statement')}}</th>
							</tr>
						</thead>
						<tbody>
							@foreach($weekly_payouts as $key=>$value)
							<tr class="main-list">
								<td><a href="{{url('restaurant/payout_details').'/'.$value['table_week']}}"><span class="theme-color text-nowrap">{{$value['week']}}</span></a></td>
								<td>{{$value['count']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['subtotal']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['tax']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['total_amount']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['gofer_fee']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['total_payout']}}</td>
								<td> {{$value['payout_status']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['penalty']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['paid_penalty']}}</td>
								<td class="status pending"><label>{{$value['status']}}</label></td>
								<td class="text-center">
									<a href="export_data/{{$value['table_week']}}" class="icon icon-download-button theme-color" id="payout_export" data-val="{{$value['table_week']}}"></a>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@if(count($weekly_payouts)==0)
					<div class="p-4 text-center">
						<h4 class="m-0">{{trans('messages.store.no_payouts_available')}} !</h4>
					</div>
					@endif
				</div>
				{{--
					<div class="d-flex align-items-center justify-content-end">
						<span>1 of 1</span>
						<nav aria-label="Page navigation example" class="my-3 ml-3">
							<ul class="pagination">
								<li class="page-item disabled">
									<a class="page-link" href="#" tabindex="-1">{{trans('messages.store.previous')}}</a>
								</li>
								<li class="page-item">
									<a class="page-link" href="#">{{trans('admin_messages.next')}}</a>
								</li>
							</ul>
						</nav>
					</div>
					--}}
				</div>
				<div class="bank-details my-5">
					<h5>
						{{trans('messages.profile.payout_methods')}}  
					</h5>

					@if($payout_preference->count() != 3)
					<div class="col-12 my-3 p-0">
						<a href="javascript:voi(0)" data-toggle="modal" data-target="#choose_payout" class="btn btn-theme modal_popup">{{trans('admin_messages.add')}}</a>
					</div>
					@endif

					@if($payout_preference->count() > 0)
					<div class="table-responsive">
						<table>
							<thead>
								<tr>
									<th>{{trans('messages.store.method')}}</th>
									<th>{{trans('messages.store.details')}}</th>
									<th>{{trans('messages.profile_orders.status')}}</th>
									<th>{{trans('admin_messages.edit')}}</th>
									<th></th>
								</tr>
							</thead>
							<tbody>								@foreach($payout_preference as $payout)
								<tr class="main-list">
									<td>{{$payout->payout_method}}
										@if($payout->default =='yes')
										<span class="pl-1 theme-color"> @lang('messages.store.default') </span>
										@endif
									</td>
									<td>{{$payout->paypal_email}}
										@if($payout->currency_code)
										<span class="d-inline-block">
											({{$payout->currency_code}})
										</span>
										@endif
									</td>
									<td>{{trans('messages.store.ready')}}</td>
									
								<td>	
								@if($payout->default !='yes')						
								<a href="javascript:void(0)" class="payout-tooltip" data-placement="bottom"
									data-html="true" title="
										<a href='payout_delete/{{$payout->id}}'>
											{{ trans('messages.store.remove') }}
										</a>
										
										<a href='payout_default/{{$payout->id}}'>
											{{ trans('messages.store.set_as_default') }}
										</a>
								">
									<i class="icon icon-pencil-edit-button theme-color"></i>
								</a>@endif
								</td>
								</tr>
								
								@endforeach
							</tbody>
						</table>
					</div>
					@endif
				</div>
			</div>
		</div>

		<div class="add-payout-modal modal fade" id="choose_payout" role="dialog" ng-init="payout={{json_encode($payout_types)}}">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						{{trans('messages.store.add_payout_method')}}
						<button type="button" class="close" data-dismiss="modal">
							<i class="icon icon-close-2"></i>
						</button>
					</div>
					<div class="modal-body">
						<p ng-repeat="x in payout"> <input type="radio" name="payout_type" value="@{{x}}"> @{{x}} </p>
						<p class="text-danger" id="payout_type_errors"></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary choose_payout">{{trans('messages.store_dashboard.next')}}</button>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="user_id_data" value="{{$user_id}}">
		<div class="add-payout-modal modal fade" id="account_modal" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						{{trans('messages.store.add_payout_method')}}
						<button type="button" class="close" data-dismiss="modal">
							<i class="icon icon-close-2"></i>
						</button>
					</div>
					<div class="modal-body">
						<div class="flash-container" id="popup1_flash-container"> </div>
						<form class="modal-add-payout-pref" method="post" action="{{'update_payout_preferences/'.$user_id }}" id="payout_preference_submit" accept-charset="UTF-8" enctype="multipart/form-data">
							@csrf

							{!! Form::token() !!}
							<div class="panel-body" ng-init="payout_country={{json_encode(old('payout_country') ?: '')}};payout_currency={{json_encode(old('currency') ?: '')}};country_currency={{json_encode($country_currency)}};mandatory={{ json_encode($mandatory)}};mandatory_field={{ json_encode($mandatory_field)}};old_currency='{{ old('currency') ? json_encode(old('currency')) : '' }}';payout_responce=''">
								<div class="select-cls">
									<label for="payout_info_payout_country">
										{{trans('messages.profile.country')}}
										<span class="required">*</span>
									</label>
									<div class="select">
										{!! Form::select('payout_country', $country_list, '', ['autocomplete' => 'billing country', 'id' => 'payout_info_payout_country','placeholder'=>'Select','ng-model'=>'payout_country','style'=>'min-width:140px;']) !!}
										<span class="text-danger">{{$errors->first('payout_country')}}</span>
									</div>
								</div>

								<div ng-if="mandatory_field[payout_country]['currency']" class="select-cls" id="currency_payout">
									<label for="payout_info_payout_currency">
										@lang('messages.store.currency')
										<span class="required">*</span>
									</label>
									<div   class="select">
										{!! Form::select('currency', $currency,'', ['autocomplete' => 'billing currency', 'id' => 'payout_info_payout_currency','placeholder'=>'Select','style'=>'min-width:140px;']) !!}
										<span class="text-danger">{{$errors->first('currency')}}</span>
									</div>
								</div>
								<div>
									<label class="" for="phone_number">
										{{trans('messages.store.phone_number')}}
										<span style="color:red">*</span></label>
										{!! Form::text('phone_number', '', ['id' => 'phone_number', 'class' => 'form-control']) !!}
									</div>

									<div ng-if="payout_country == 'JP'" class="select-cls row-space-3">
										<label for="user_gender">
											{{trans('messages.store.gender')}}
										</label>
										<div class="select">
											{!! Form::select('gender', ['male' => 'Male', 'female' => 'Female'], 'male', ['id' => 'user_gender', 'placeholder' => 'Gender', 'class' => 'focus','style'=>'min-width:140px;']) !!}
											<span class="text-danger">{{ $errors->first('gender') }}</span>
										</div>
									</div>

									<div ng-class="(payout_country == 'JP'? 'jp_form row':'')" class="clearfix row-space-2">
										<div class="country-info" ng-class="(payout_country == 'JP'? 'col-md-6 col-12':'')">
											<label ng-if="payout_country == 'JP'"><b>{{trans('messages.store.address_kana')}}:</b></label>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.profile.address')}} 1<span style="color:red">*</span></label>
												{!! Form::text('address1', '', ['id' => 'address1', 'class' => 'form-control']) !!}
											</div>

											<div>
												<label ng-if="payout_country == 'JP'" for="payout_info_payout_address2">{{trans('messages.store.town')}}<span style="color:red">*</span></label>
												<label ng-if="payout_country != 'JP'" for="payout_info_payout_address2">{{trans('messages.profile.address')}} 2</label>
												{!! Form::text('address2', '', ['id' => 'address2', 'class' => 'form-control']) !!}
											</div>

											<div>
												<label for="payout_info_payout_city">{{trans('messages.driver.city')}} <span style="color:red">*</span></label>
												{!! Form::text('city', '', ['id' => 'city', 'class' => 'form-control']) !!}
											</div>

											<div>
												<label for="payout_info_payout_state">{{trans('admin_messages.state')}} / {{trans('messages.store.province')}}<span style="color:red">*</span></label>
												<span><i style="font-size:13px;">{{trans('messages.store.state_note')}} (E.g. New York - NY)</i></span>
												{!! Form::text('state', '', ['id' => 'state', 'class' => 'form-control']) !!}
											</div>

											<div>
												<label for="payout_info_payout_zip">{{trans('messages.profile.postal_code')}} <span style="color:red">*</span></label>
												{!! Form::text('postal_code', '', ['id' => 'postal_code', 'class' => 'form-control']) !!}
												<span class="text-danger">{{$errors->first('postal_code')}}</span>
											</div>
										</div>
										<div ng-if="payout_country == 'JP'" class="country-info col-md-6 col-12 mt-3 mt-md-0">
											<label><b>{{trans('messages.store.address_kanji')}}:</b></label>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.profile.address')}} 1<span style="color:red">*</span></label>
												{!! Form::text('kanji_address1', '', ['id' => 'kanji_address1', 'class' => 'form-control',"ng-value"=>'payout_responce.kanji_address1']) !!}
											</div>

											<div>
												<label for="payout_info_payout_address2">{{trans('messages.store.town')}}<span style="color:red">*</span></label>
												{!! Form::text('kanji_address2', '', ['id' => 'kanji_address2', 'class' => 'form-control',"ng-value"=>'payout_responce.kanji_address2']) !!}
											</div>

											<div>
												<label for="payout_info_payout_city">{{trans('messages.driver.city')}} <span style="color:red">*</span></label>
												{!! Form::text('kanji_city', '', ['id' => 'kanji_city', 'class' => 'form-control',"ng-value"=>'payout_responce.kanji_city']) !!}
											</div>

											<div>
												<label for="payout_info_payout_state">{{trans('admin_messages.state')}} / {{trans('messages.store.province')}}<span style="color:red">*</span></label>
												<span><i style="font-size:13px;">{{trans('messages.store.state_note')}} (E.g. New York - NY)</i></span>
												{!! Form::text('kanji_state', '', ['id' => 'kanji_state', 'class' => 'form-control',"ng-value"=>'payout_responce.kanji_state']) !!}
											</div>

											<div>
												<label for="payout_info_payout_zip">{{trans('messages.profile.postal_code')}} <span style="color:red">*</span></label>
												{!! Form::text('kanji_postal_code', '', ['id' => 'kanji_postal_code', 'class' => 'form-control',"ng-value"=>'payout_responce.kanji_postal_code']) !!}
												<span class="text-danger">{{$errors->first('kanji_postal_code')}}</span>
											</div>
										</div>
									</div>

									<!-- Branch code -->
									<!-- Account Number -->

									<div ng-repeat="(field_name,validation) in mandatory_field[payout_country]">
										<div ng-if="field_name!='currency' && field_name!='iban'">
											<label ng-switch="mandatory_field[payout_country]['iban'] && field_name=='account_number'" class="" for="@{{field_name}}" >
												<span ng-switch-when='true'>  {{trans('messages.store.iban_number')}}</span>
												<span ng-switch-default>  @{{ field_name | translations }}</span>
												<span ng-if="validation" style="color:red">*</span>
											</label> 

											{!! Form::text('@{{field_name}}','',['id'=>'@{{field_name}}','class'=>'form-control','data-rule-required'=>'@{{validation}}',"ng-value"=>'payout_responce[field_name]']) !!}
										</div>

									</div>
									<input type="hidden" id="is_iban" name="is_iban" ng-value="mandatory_field[payout_country]['iban']?'Yes':'No'">
									<input type="hidden" id="is_branch_code" name="is_branch_code"  ng-value="mandatory_field[payout_country]['branch_code']? 'Yes':'No'">

									<div id="legal_document" class="legal_document">
										<label class="control-label required-label" >{{trans('admin_messages.document')}} ({{trans('messages.store.jpg_or_png_format')}})<span style="color:red" id="document_smbl">*</span></label>
										<div class="col-12 p-0">
											{!! Form::file('document', ['id' => 'document', 'class' => '' ,"accept"=>".jpg,.jpeg,.png", 'style'=>'display: none']) !!}
											<a id="choose_files" class="choose_file_type">{{trans('messages.profile.choose_file')}}</a>
											<span class="upload_text" id="file_text_1"></span>
											<span class="text-danger">{{$errors->first('document')}}</span>
										</div>
										<span id="doc_valid" style="display: none">{{trans('js_messages.store.field_required')}}</span>
										@if($payout_preference && isset($payout_preference->document_image))
										<img class="mt-2" id="document_src" src="{{$payout_preference->document}}" width="100" height="100">
										@endif
									</div>

									<div class="legal_document">
										<label class="control-label required-label" >{{trans('admin_messages.additional_document')}} ({{trans('messages.store.jpg_or_png_format')}})
											<span style="color:red">*</span></label>
											<div class="col-12 p-0">
												{!! Form::file('additional_document', ['id' => 'additional_document', 'class' => '' ,"accept"=>".jpg,.jpeg,.png", 'style'=>'display: none']) !!}
												<a id="additional_choose_files" class="choose_file_type">{{trans('messages.profile.choose_file')}}</a>
												<span class="upload_text" id="file_text_2"></span>
												<span class="text-danger">{{$errors->first('additional_document')}}</span>
											</div>
											<span id="add_doc_valid" style="display: none">{{trans('js_messages.store.field_required')}}</span>
											@if($payout_preference && isset($payout_preference->additional_document_image))
											<img class="mt-2" id="additional_document_src" src="{{$payout_preference->additional_document}}" width="100" height="100">
											@endif
										</div>

									</div>
									<input type="hidden" name="holder_type" value="individual" id="holder_type">
									<input type="hidden" name="stripe_token" id="stripe_token" >
									<input type="hidden" name="payout_method" value="Stripe">
									<p class="text-danger my-4" id="stripe_errors"></p>
									<div class="panel-footer mt-4">
										<input type="submit" value="{{ trans('messages.driver.submit') }}" class="btn btn-theme">
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!-- Popup for get Stripe datas -->
				<!-- end Popup -->

				<div class="add-payout-modal modal fade" id="Paypal_modal" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								{{trans('messages.store.add_payout_method')}}
								<button type="button" class="close" data-dismiss="modal">
									<i class="icon icon-close-2"></i>
								</button>
							</div>
							<div class="modal-body">
								<form class="modal-add-payout-pref" method="post" action="{{'update_payout_preferences/'.$user_id }}" id="paypal_payout_preference_submit">
									@csrf
									{!! Form::token() !!}
									<div class="panel-body" ng-init="payout_country={{json_encode(old('payout_country') ?: '')}};country_currency={{json_encode($country_currency)}}"> 
										<div class="select-cls">
											<label for="payout_info_payout_country">
												{{trans('messages.profile.country')}}
												<span class="required">*</span>
											</label>
											<div class="select">
												{!! Form::select('payout_country', $country, '', ['autocomplete' => 'billing country', 'id' => 'payout_info_payout_country','placeholder'=>'Select','ng-model'=>'payout_country','style'=>'min-width:140px;']) !!}
												<span class="text-danger">{{$errors->first('payout_country')}}</span>
											</div>
											<div class="country-info">
												<div>
													<label for="payout_info_payout_address2">{{trans('messages.profile.address')}} 1<span style="color:red">*</span></label>
													{!! Form::text('address1', '', ['id' => 'paypal_address1', 'class' => 'form-control']) !!}
												</div>
												<div>
													<label for="payout_info_payout_address2">{{trans('messages.profile.address')}} 2</label>
													{!! Form::text('address2', '', ['id' => 'paypal_address2', 'class' => 'form-control']) !!}
												</div>

												<div>
													<label for="payout_info_payout_city">{{trans('messages.driver.city')}} <span style="color:red">*</span></label>
													{!! Form::text('city', '', ['id' => 'paypal_city', 'class' => 'form-control']) !!}
												</div>

												<div>
													<label for="payout_info_payout_state">{{trans('admin_messages.state')}} / {{trans('messages.store.province')}}<span style="color:red">*</span></label>
													{!! Form::text('state', '', ['id' => 'paypal_state', 'class' => 'form-control']) !!}
												</div>

												<div>
													<label for="payout_info_payout_zip">{{trans('messages.profile.postal_code')}} <span style="color:red">*</span></label>
													{!! Form::text('postal_code', '', ['id' => 'paypal_postal_code', 'class' => 'form-control']) !!}
													<span class="text-danger">{{$errors->first('postal_code')}}</span>
												</div>
												<div>
													<label for="payout_info_payout_address2">
														{{trans('messages.profile.paypal_email')}}<span style="color:red">*</span></label>
													{!! Form::text('paypal_email', '', ['id' => 'paypal_email', 'class' => 'form-control']) !!}
												</div>
												<input type="hidden" name="payout_method" value="Paypal">
												<div class="panel-footer mt-4">
													<input type="submit" value="{{ trans('messages.driver.submit') }}" class="btn btn-theme">
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>	

				<!-- Bank Payout -->

				<div class="add-payout-modal modal fade" id="BankTransfer_modal" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							{{trans('messages.store.add_payout_method')}}
							<button type="button" class="close" data-dismiss="modal">
								<i class="icon icon-close-2"></i>
							</button>
						</div>
						<div class="modal-body">
							<form class="modal-add-payout-pref" method="post" action="{{'update_payout_preferences/'.$user_id }}" id="bank_payout_preference_submit">
								@csrf
								{!! Form::token() !!}
								<div class="panel-body" ng-init="payout_country={{json_encode(old('payout_country') ?: '')}};"> 
									<div class="select-cls">
										<label for="payout_info_payout_country">
											{{trans('messages.profile.country')}}
											<span class="required">*</span>
										</label>
										<div class="select">
											{!! Form::select('payout_country', $country, '', ['autocomplete' => 'billing country', 'id' => 'payout_info_payout_country','placeholder'=>'Select','ng-model'=>'payout_country','style'=>'min-width:140px;']) !!}
											<span class="text-danger">{{$errors->first('payout_country')}}</span>
										</div>
										<div class="country-info">
											<div>
											<label for="payout_info_payout_address2">{{trans('messages.store.account_number')}}<span style="color:red">*</span></label>
												{!! Form::text('account_number', '', ['id' =>'bank_account_number', 'class' => 'form-control']) !!}
											</div>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.store.holder_name')}}<span style="color:red">*</span></label>
												{!! Form::text('account_holder_name', '', ['id' => 'bank_account_holder_name', 'class' => 'form-control']) !!}
											</div>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.store.bank_name')}}<span style="color:red">*</span></label>
												{!! Form::text('bank_name', '', ['id' => 'b_bank_name', 'class' => 'form-control']) !!}
											</div>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.store.bank_location')}}<span style="color:red">*</span></label>
												{!! Form::text('bank_location', '', ['id' => 'b_bank_location', 'class' => 'form-control']) !!}
											</div>
											<div>
												<label for="payout_info_payout_address2">{{trans('messages.store.bank_code')}}<span style="color:red">*</span></label>
												{!! Form::text('bank_code', '', ['id' => 'b_bank_code', 'class' => 'form-control']) !!}
											</div>
										
											<input type="hidden" name="payout_method" value="BankTransfer">
											<div class="panel-footer mt-4">
												<input type="submit" value="{{ trans('messages.driver.submit') }}" class="btn btn-theme">
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

				<input type="hidden" id="blank_address" value="{{trans('messages.account.blank_address')}}">
				<input type="hidden" id="blank_city" value="{{trans('messages.account.blank_city')}}">
				<input type="hidden" id="blank_post" value="{{trans('messages.account.blank_post')}}">
				<input type="hidden" id="blank_country" value="{{trans('messages.account.blank_country')}}">
				<input type="hidden" id="choose_method" value="{{trans('messages.account.choose_method')}}">
				<input type="hidden" id="payout_preference_id" value="{{isset($payout_preference->id)}}">
			</main>
			@stop

			@push('scripts')
			<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

			<script type="text/javascript">
				if($('#payout_info_payout_country').val()!='OT') {
					Stripe.setPublishableKey('{{@$stripe_data}}');
				}

				function stripeResponseHandler(status, response) {
					$('#payout_preference_submit').removeClass('loading');

					if (response.error) {
						$("#stripe_errors").html("");
						if(response.error.message == "Must have at least one letter") {
							$("#stripe_errors").html('Please fill all required fields');
						}

						else {
							$("#stripe_errors").html(response.error.message);
						}
						return false;
					} 

					else {
						$("#stripe_errors").html("");
						var token = response['id'];
						$("#stripe_token").val(token);
						$('#payout_preference_submit').removeClass('loading');
						$("#payout_preference_submit").submit();
						return true;
					}
				}

		var $paypal_payout_valitation = $('#paypal_payout_preference_submit').validate({
			rules: {
				payout_country : { required : true},
				address1 : { required : true},
				city : { required : true},
				state : { required : true},
				postal_code : { required : true}, 
				paypal_email: { required: true, email: true },               
			},
			messages: {
				payout_country : { required : Lang.get('js_messages.store.field_required')},
				address1 : { required : Lang.get('js_messages.store.field_required')},
				city : { required : Lang.get('js_messages.store.field_required')},
				state : { required : Lang.get('js_messages.store.field_required')},
				postal_code : { required : Lang.get('js_messages.store.field_required')},
				paypal_email : { required : Lang.get('js_messages.store.field_required')},
			},
			errorElement: "span",
			errorClass: "text-danger",
			errorPlacement: function( label, element ) {
				if(element.attr( "data-error-placement" ) === "container" ) {
					container = element.attr('data-error-container');
					$(container).append(label);
				} 

				else {
					label.insertAfter( element ); 
				}
			},
		});


		var $bank_payout_valitation = $('#bank_payout_preference_submit').validate({
			rules: {
				payout_country : { required : true},
				account_number : { required : true},
				account_holder_name : { required : true},
				bank_name : { required : true},
				bank_location : { required : true}, 
				bank_code: { required: true },               
			},
			messages: {
				payout_country : { required : Lang.get('js_messages.store.field_required')},
				account_number : { required : Lang.get('js_messages.store.field_required')},
				account_holder_name : { required : Lang.get('js_messages.store.field_required')},
				bank_name : { required : Lang.get('js_messages.store.field_required')},
				bank_location : { required : Lang.get('js_messages.store.field_required')},
				bank_code : { required : Lang.get('js_messages.store.field_required')},
			},
			errorElement: "span",
			errorClass: "text-danger",
			errorPlacement: function( label, element ) {
				if(element.attr( "data-error-placement" ) === "container" ) {
					container = element.attr('data-error-container');
					$(container).append(label);
				} 

				else {
					label.insertAfter( element ); 
				}
			},
		});
		
		

		// Code for the Validator
		var $payout_valitation = $('#payout_preference_submit').validate({
			rules: {
				iban: { custom_required: true },                
				bsb: { custom_required: true },                
				transit_number: { custom_required: true },                
				institution_number: { custom_required: true },                
				account_holder_name: { custom_required: true },                
				currency: { custom_required: true },                
				account_number: { custom_required: true },                
				routing_number: { custom_required: true },                
				sort_code: { custom_required: true },                
				account_owner_name: { custom_required: true },                
				bank_name: { custom_required: true },                
				branch_name: { custom_required: true },                
				branch_code: { custom_required: true },                
				clearing_code: { custom_required: true },                
				ssn_last_4: { custom_required: true },
				payout_country : { required : true},
				currency : { required : true},
				phone_number : { required : true},
				gender : { required : true},
				address1 : { required : true},
				city : { required : true},
				state : { required : true},
				postal_code : { required : true}, 
			},
			messages: {
				payout_country : { required : Lang.get('js_messages.store.field_required')},
				currency : { required : Lang.get('js_messages.store.field_required')},
				phone_number : { required : Lang.get('js_messages.store.field_required')},
				gender : { required : Lang.get('js_messages.store.field_required')},
				address1 : { required : Lang.get('js_messages.store.field_required')},
				city : { required : Lang.get('js_messages.store.field_required')},
				state : { required : Lang.get('js_messages.store.field_required')},
				postal_code : { required : Lang.get('js_messages.store.field_required')},
			},
			errorElement: "span",
			errorClass: "text-danger",
			errorPlacement: function( label, element ) {
				if(element.attr( "data-error-placement" ) === "container" ) {
					container = element.attr('data-error-container');
					$(container).append(label);
				} 

				else {
					label.insertAfter( element ); 
				}
			},
		});

		function validate_document(argument) {

			var id = $('#payout_preference_id').val();
			if(id !=''){
				return true; 
			}
			if($('#file_text_1').is(':empty')){
				$('#doc_valid').show();
				$('#doc_valid').addClass('doc-danger');
			}else{
				$('#doc_valid').hide();
			}

			if($('#file_text_2').is(':empty')){
				$('#add_doc_valid').show();
				$('#add_doc_valid').addClass('doc-danger');
			}else{
				$('#add_doc_valid').hide();
			}

			if($('#file_text_1').is(':empty') || $('#file_text_2').is(':empty')){
				return false;
			}else{
				return true;
			}
		}

		$(document).ready(function () {
			$("#paypal_payout_preference_submit").submit(function (event) {
				var $valid = $('#paypal_payout_preference_submit').valid();
				if(!$valid) {
					$paypal_payout_valitation.focusInvalid();
					return false;
				}
			});
		});

		$(document).ready(function () {
			$("#bank_payout_preference_submit").submit(function (event) {
				var $valid = $('#bank_payout_preference_submit').valid();
				if(!$valid) {
					$bank_payout_valitation.focusInvalid();
					return false;
				}
			});
		});


		$(document).ready(function () {
			$("#payout_preference_submit").submit(function (event) {
				console.log($('#ifsc_code').val());
				var validate = validate_document();
				if(!validate){
					return false;
				}
				if($('#payout_info_payout_country').val()!='OT'){
					stripe_token = $("#stripe_token").val();
					if(stripe_token != ''){
						return true;
					}
				}

				var $valid = $('#payout_preference_submit').valid();
				if(!$valid) {
					$payout_valitation.focusInvalid();
					return false;
				}

				if($('#account_number').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				if($('#holder_name').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				else if($('#address1').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				else if($('#city').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				else if($('#state').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				else if($('#postal_code').val() == '')
				{
					$("#stripe_errors").html('Please fill all required fields');
					return false;
				}
				if($('#payout_info_payout_country').val() == 'JP')
				{
					if($('#bank_name').val() == '')
					{
						$("#stripe_errors").html('Please fill all required fields');
						return false;
					}

					if($('#branch_name').val() == '')
					{
						$("#stripe_errors").html('Please fill all required fields');
						return false;
					}
				}

				is_iban = $('#is_iban').val();
				is_branch_code = $('#is_branch_code').val();

				var bankAccountParams = {
					country: $('#payout_info_payout_country').val(),
					currency: $('[name="currency"]').val(),
					account_number: $('#account_number').val(),
					account_holder_name: $('#account_holder_name').val(),
					account_holder_type: $('#holder_type').val()
				}

				if(is_iban == 'No')
				{
					if(is_branch_code == 'Yes')
					{
						if($('#payout_info_payout_country').val() != 'GB' && $('[name="currency"]').val() != 'EUR')
						{
							if($('#routing_number').val() == '')
							{
								$("#stripe_errors").html('Please fill all required fields');
								return false;
							}
							if($('#branch_code').val() == '')
							{
								$("#stripe_errors").html('Please fill all required fields');
								return false;
							}
							if($('#payout_info_payout_country').val() == 'JP') {
								bankAccountParams.routing_number = $('#bank_code').val()+''+$('#branch_code').val();
							}
							else {
								if($('#payout_info_payout_country').val() == 'IN')
								{
									bankAccountParams.routing_number = $('#ifsc_code').val()+'-'+$('#branch_code').val();
								}else{
									bankAccountParams.routing_number = $('#routing_number').val()+'-'+$('#branch_code').val();
								}
							}
						}
					}
					else
					{

						if($('#payout_info_payout_country').val() != 'GB' && $('[name="currency"]').val() != 'EUR' && $('#payout_info_payout_country').val()!='OT')
						{
							if($('#payout_info_payout_country').val() == 'IN')
							{	
								if($('#ifsc_code').val() == '')
								{
									$("#stripe_errors").html('Please fill all required fields');
									return false;
								}
								bankAccountParams.routing_number = $('#ifsc_code').val();

							}else{
								if($('#routing_number').val() == '')
								{
									$("#stripe_errors").html('Please fill all required fields');
									return false;
								}
								bankAccountParams.routing_number = $('#routing_number').val();
							}
						}
					}
				}
				$('#payout_preference_submit').addClass('loading');
				if($('#payout_info_payout_country').val()!='OT'){
					Stripe.bankAccount.createToken(bankAccountParams, stripeResponseHandler);
					return false;
				}
				$('.icon-close-2').trigger('click');
			});
		});


		$('#choose_files').click(function(){	
			$('#document').change(function(evt) {   		
				var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
				$('#choose_files').css("background-color","#43A422");
				$('#choose_files').css("color","#fff");
				$('#choose_files').text(Lang.get('js_messages.file.file_attached'));
				$('#file_text_1').text(fileName);
				validate_document();
			});
		});

		$('#additional_choose_files').click(function(){	
			$('#additional_document').change(function(evt) {   
				var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
				$('#additional_choose_files').css("background-color","#43A422");
				$('#additional_choose_files').css("color","#fff");
				$('#additional_choose_files').text(Lang.get('js_messages.file.file_attached'));
				$('#file_text_2').text(fileName);
				validate_document();
			});
		});

		$('.choose_payout').click(function(){
			if($("input[name=payout_type]:checked").length == 0) {
				$("#payout_type_errors").html(Lang.get('js_messages.store.please_choose_payment'));
				return false;
			}else{
				var isChecked = $("input[name=payout_type]:checked").val();
				var payout_method = '';
				if(isChecked =="Stripe"){
					payout_method = "account_modal";
				}else if(isChecked == "Paypal"){
					payout_method = "Paypal_modal";
				}else{
					payout_method = "BankTransfer_modal";
				}
				setTimeout(function(){ 					
				$("#"+payout_method).modal('show');
				 }, 600);
				$("#choose_payout").modal('hide');
			}
		});

	</script>

	<script type="text/javascript">
		@if (count($errors) > 0)
		$('.modal_popup').trigger('click');
		@endif
	</script>
	<style type="text/css">
		
		span.doc-danger {
			margin-top: 5px;
			display: block;
			font-size: 12px;
			color: #dc3545 !important;
		}
	</style>
	@endpush
