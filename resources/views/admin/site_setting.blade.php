@extends('admin.template')
@section('main')
<div class="content">
	<div class="container-fluid">
	<div class="card">
	<div class="row">
	<div class="col-md-12 ml-auto mr-auto p-3">
	<div class="page-categories">
	{!! Form::open(['url' => route('admin.site_setting'), 'class' =>'form-horizontal','id'=>'site_setting_form','files'=> true]) !!}
	@csrf
	<ul id="site_setting_tabs" class="nav nav-pills nav-pills-warning nav-pills-icons justify-content-center"role="tablist">
		<li class="nav-item">
			<a class="nav-link active" href="#site_setting" data-toggle="tab" role="tablist">
				<i class="material-icons">build</i>@lang('admin_messages.site_setting')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link " href="#site_images" data-toggle="tab" role="tablist">
				<i class="material-icons">build</i> @lang('admin_messages.site_images')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#api_credentials" data-toggle="tab" role="tablist">
				<i class="material-icons">vpn_key</i> @lang('admin_messages.api_credentials')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#payment_gateway" data-toggle="tab" role="tablist">
				<i class="material-icons">attach_money</i> @lang('admin_messages.payment_gateway')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#email_setting" data-toggle="tab" role="tablist">
				<i class="material-icons">email</i> @lang('admin_messages.email_settings')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#fees_manage" data-toggle="tab" role="tablist">
				<i class="material-icons">money_off</i> @lang('admin_messages.fees')
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#join_us" data-toggle="tab" role="tablist">
				<i class="material-icons">devices_other</i> @lang('admin_messages.join_us')
			</a>
		</li>
			<li class="nav-item">
			<a class="nav-link" href="#social_media" data-toggle="tab" role="tablist">
				<i class="material-icons">connect_without_contact</i> @lang('admin_messages.social_media')
			</a>
		</li>
	</ul>

<div class="tab-content tab-space tab-subcategories">
	<div class="tab-pane active" id="site_setting">
	<div class="justify-content-center">
	<div class="card-body">
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.site_name')
				<span class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group has-default">
					{!! Form::text('site_setting[site_name]',site_setting('site_name'),
					['class' => 'form-control', 'id' => 'input_site_name']) !!}
					<span class="text-danger">{{ $errors->first('site_name') }}</span>
				</div>
			</div>
			<!-- <label class="col-md-2 col-form-label site_name_color text-left"> English -->
			<!-- </label> -->
		</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.site_support_phone')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::text('site_setting[site_support_phone]',
							site_setting('site_support_phone'), ['class' => 'form-control', 'id'
							=> 'site_support_phone']) !!}
							<span
							class="text-danger">{{ $errors->first('site_support_phone') }}</span>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.analystics')
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::textarea('site_setting[analystics]',
							site_setting('analystics'), ['class' => 'form-control', 'id' =>
							'analystics']) !!}
							<span class="text-danger">{{ $errors->first('analystics') }}</span>
						</div>
					</div>
				</div>

				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.paypal_currency_code')<span
						class="required text-danger">*</span>
						<span class="d-block" style="font-size: 11px;">
							Note : This currency must be same as your Merchant Account currency
						</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::select('site_setting[paypal_currency_code]',
							$paypal_currency_select, site_setting('paypal_currency_code'),
							['class' => 'form-control', 'id' => 'paypal_currency_code']) !!}
							<span
							class="text-danger">{{ $errors->first('paypal_currency_code') }}
							<span
							class="text-danger">{{ $errors->first('paypal_currency_code') }}</span>
						</div>
					</div>
				</div>

				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.defaulty_curreny_code')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::select('site_setting[default_currency]', $currency_select,
							site_setting('default_currency'), ['class' => 'form-control', 'id'
							=> 'defaulty_curreny_code']) !!}
							<span
							class="text-danger">{{ $errors->first('default_currency') }}</span>
						</div>
					</div>
				</div>

				<div class="row">
					<label class="col-md-4 col-form-label">
						Maintenance Mode
						<span class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							 {!! Form::select('maintenance_mode', ['up' => 'No', 'down' => 'Yes'], $maintenance_mode, ['class' => 'form-control', 'id' => 'input_maintenance_mode']) !!}
							<span
							class="text-danger">{{ $errors->first('maintenance_mode') }}</span>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.store_km')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::text('site_setting[store_km]', site_setting('store_km'),
							['class' => 'form-control', 'id' => 'input_version']) !!}
							<span class="text-danger">{{ $errors->first('store_km') }}</span>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.driver_km')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::text('site_setting[driver_km]', site_setting('driver_km'),
							['class' => 'form-control', 'id' => 'input_version']) !!}
							<span class="text-danger">{{ $errors->first('driver_km') }}</span>
						</div>
					</div>
				</div>
				<div class="row">
											<label class="col-md-4 col-form-label">
												@lang('admin_messages.number_of_delivery')<span class="required text-danger">*</span>
											</label>
											<div class="col-md-5">
												<div class="form-group">
													{!! Form::text('site_setting[number_of_delivery]', site_setting('number_of_delivery'), ['class' => 'form-control', 'id' => 'input_version']) !!}
													<span class="text-danger">{{ $errors->first('number_of_delivery') }}</span>
												</div>
											</div>
										</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.delivery_radius')<span class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="form-group">
							{!! Form::text('site_setting[delivery_radius]', site_setting('delivery_radius'), ['class' => 'form-control', 'id' => 'input_version']) !!}
							<span class="text-danger">{{ $errors->first('delivery_radius') }}</span>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.preperation_time_interval')<span class="required text-danger">*</span></label>
					<div class="col-md-5">
						<div class="form-group">
								{!! Form::text('site_setting[preperation_time_interval]', site_setting('preperation_time_interval'), ['class' => 'form-control', 'id' => 'input_version']) !!}
							<span class="text-danger">{{ $errors->first('preperation_time_interval') }}</span>
						</div>
					</div>
			</div>	
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.multiple_delivery')<span class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					 <div class="checkbox "><label class="switch">
	                  <input type="checkbox" checked id="multiple_delivery">
	                  <span class="slider round"></span>
	                  </label>

	                  </div>
	                  {!! Form::hidden('site_setting[multiple_delivery]', site_setting('multiple_delivery'), ['class' => 'form-control', 'id' => 'multiple_delivery_val']) !!}
					<span class="text-danger">{{ $errors->first('multiple_delivery') }}</span>
				</div>
			</div>
		</div>	
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.otp_verification')
			</label>
			<div class="col-md-5">
				<div class="form-group">
					 <div class="checkbox "><label class="switch">
                      <input type="checkbox" checked id="otp_verification">
                      <span class="slider round"></span>
                      </label>
	                  </div>
	                  {!! Form::hidden('site_setting[otp_verification]', site_setting('otp_verification'), ['class' => 'form-control', 'id' => 'otp_verification_val']) !!}
					<span class="text-danger">{{ $errors->first('otp_verification') }}</span>
				</div>
			</div>
		</div>	
				
		<div class="row">
			<label class="col-md-4 col-form-label">
				Admin Prefix<span class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('site_setting[admin_prefix]',
					site_setting('admin_prefix'), ['class' => 'form-control', 'id' =>
					'input_admin_prefix']) !!}
					<span
					class="text-danger">{{ $errors->first('admin_prefix') }}</span>
				</div>
			</div>
			<label class="col-md-2 col-form-label site_name_color"
			style="text-transform:none;"> Default : admin </label>
		</div>
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.version')<span
				class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('site_setting[version]', site_setting('version'),
					['class' => 'form-control', 'id' => 'input_version']) !!}
					<span class="text-danger">{{ $errors->first('version') }}</span>
				</div>
			</div>
		</div>
		
			</div>
		</div>
	</div>
	<div class="tab-pane " id="site_images">
		<div class="justify-content-center">
			<div class="card-body">
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.site_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('site_logo','1')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[site_logo]',['class' =>
									'form-control', 'id' => 'input_site_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(130x65)</small>
						<span
						class="text-danger d-block">{{ $errors->first('site_logo') }}</span>
					</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.site_favIcon')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('site_favicon','2')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[site_favicon]',['class' =>
									'form-control', 'id' => 'input_site_favicon']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(50x50)</small>
						<span
						class="text-danger d-block">{{ $errors->first('site_favicon') }}</span>
					</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.store_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('store_logo','3')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[store_logo]',['class' =>
									'form-control', 'id' => 'input_store_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(1286x476)</small>
						<span
						class="text-danger d-block">{{ $errors->first('store_logo') }}</span>
					</div>
					</div>
				</div>
				<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_logo')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="fileinput fileinput-new" data-provides="fileinput">
						<div class="fileinput-new thumbnail">
							<img src="{{site_setting('email_logo','4')}}" alt="...">
						</div>
						<div class="fileinput-preview fileinput-exists thumbnail"></div>
						<div>
							<span class="btn btn-rose btn-round btn-file">
								<span
								class="fileinput-new">@lang('admin_messages.select_image')</span>
								<span
								class="fileinput-exists">@lang('admin_messages.change')</span>
								{!! Form::file('site_images[email_logo]',['class' =>
								'form-control', 'id' => 'input_email_logo']) !!}
							</span>
							<a href="#pablo"
							class="btn btn-danger btn-round fileinput-exists"
							data-dismiss="fileinput"><i class="fa fa-times"></i>
						@lang('admin_messages.remove')</a>
					</div>
					<small>@lang('admin_messages.size')(130x65)</small>
					<span
					class="text-danger d-block">{{ $errors->first('email_logo') }}</span>
				</div>
				</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.footer_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('footer_logo','5')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[footer_logo]',['class' =>
									'form-control', 'id' => 'input_footer_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(130x65)</small>
						<span
						class="text-danger d-block">{{ $errors->first('footer_logo') }}</span>
					</div>
				</div>	
				</div>
				<div class="row d-none">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.app_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('app_logo','6')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[app_logo]',['class' =>
									'form-control', 'id' => 'input_app_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(140x140)</small>
						<span
						class="text-danger d-block">{{ $errors->first('app_logo') }}</span>
					</div>
				</div>
				</div>
				<div class="row">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.driver_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('driver_logo','7')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[driver_logo]',['class' =>
									'form-control', 'id' => 'input_driver_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(130x65)</small>
						<span
						class="text-danger d-block">{{ $errors->first('driver_logo') }}</span>
					</div>
				</div>
				</div>
				<div class="row d-none">
					<label class="col-md-4 col-form-label">
						@lang('admin_messages.driver_white_logo')<span
						class="required text-danger">*</span>
					</label>
					<div class="col-md-5">
						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="fileinput-new thumbnail">
								<img src="{{site_setting('driver_white_logo','8')}}" alt="...">
							</div>
							<div class="fileinput-preview fileinput-exists thumbnail"></div>
							<div>
								<span class="btn btn-rose btn-round btn-file">
									<span
									class="fileinput-new">@lang('admin_messages.select_image')</span>
									<span
									class="fileinput-exists">@lang('admin_messages.change')</span>
									{!! Form::file('site_images[driver_white_logo]',['class' =>
									'form-control', 'id' => 'input_driver_white_logo']) !!}
								</span>
								<a href="#pablo"
								class="btn btn-danger btn-round fileinput-exists"
								data-dismiss="fileinput"><i class="fa fa-times"></i>
							@lang('admin_messages.remove')</a>
						</div>
						<small>@lang('admin_messages.size')(130x65)</small>
						<span
						class="text-danger d-block">{{ $errors->first('driver_white_logo') }}</span>
					</div>
				</div>
				</div>
				
			</div>
		</div>
	</div>
<div class="tab-pane" id="api_credentials">
	<div class="justify-content-center">
		<div class="card-body ">
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.google_api_key')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[google_api_key]',
						site_setting('google_api_key'), ['class' => 'form-control', 'id' =>
						'input_google_api_key']) !!}
						<span
						class="text-danger">{{ $errors->first('google_api_key') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.google_server_key')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[google_server_key]',
						site_setting('google_server_key'), ['class' => 'form-control', 'id'
						=> 'input_google_server_key']) !!}
						<span
						class="text-danger">{{ $errors->first('google_server_key') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.fcm_server_key')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[fcm_server_key]',
						site_setting('fcm_server_key'), ['class' => 'form-control', 'id' =>
						'input_fcm_key']) !!}
						<span
						class="text-danger">{{ $errors->first('fcm_server_key') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.fcm_sender_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[fcm_sender_id]',
						site_setting('fcm_sender_id'), ['class' => 'form-control', 'id' =>
						'input_fcm_sender']) !!}
						<span
						class="text-danger">{{ $errors->first('fcm_sender_id') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.twillo_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[twillo_id]',
						site_setting('twillo_id'), ['class' => 'form-control', 'id' =>
						'input_twillo_ids']) !!}
						<span class="text-danger">{{ $errors->first('twillo_id') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.twillo_token')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[twillo_token]',
						site_setting('twillo_token'), ['class' => 'form-control', 'id'
						=> 'input_nexmo_key']) !!}
						<span
						class="text-danger">{{ $errors->first('twillo_token') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.twillo_from_number')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[twillo_from_number]',
						@site_setting('twillo_from_number'), ['class' => 'form-control', 'id'
						=> 'input_twillo_from_number']) !!}
						<span
						class="text-danger">{{ $errors->first('twillo_from_number') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.apple_service_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[apple_service_id]',
						@site_setting('apple_service_id'), ['class' => 'form-control', 'id'
						=> 'input_apple_service_id']) !!}
						<span
						class="text-danger">{{ $errors->first('apple_service_id') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.apple_team_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[apple_team_id]',
						@site_setting('apple_team_id'), ['class' => 'form-control', 'id'
						=> 'input_apple_team_id']) !!}
						<span
						class="text-danger">{{ $errors->first('apple_service_id') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.apple_key_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[apple_key_id]',
						@site_setting('apple_key_id'), ['class' => 'form-control', 'id'
						=> 'input_apple_service_id']) !!}
						<span
						class="text-danger">{{ $errors->first('apple_key_id') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.apple_key_file')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
				<div class="fileinput fileinput-new" data-provides="fileinput">
					<div>
							<span class="btn btn-rose btn-round btn-file">
								<span
								class="fileinput-new">@lang('admin_messages.select_file')</span>
								<span
								class="fileinput-exists">@lang('admin_messages.change')</span>
								{!! Form::file('api_credentials[apple_key_file]',['class' =>
								'form-control', 'id' => 'input_apple_key_id']) !!}
							</span>
							<a href="#pablo"
							class="btn btn-danger btn-round fileinput-exists"
							data-dismiss="fileinput"><i class="fa fa-times"></i>
						@lang('admin_messages.remove')</a>
					</div>
					<span
					class="text-danger d-block">{{ $errors->first('apple_key_file') }}</span>
				</div>
			</div>
		</div>

		<div class="row">
			<label class="col-md-4 col-form-label">
				Firebase Database URL
				<span class="required text-danger">*</span>
			</label>			
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('api_credentials[database_url]',@site_setting('database_url'), ['class' => 'form-control', 'id'=> 'input_database_url']) !!}
					<span class="text-danger">
						{{ $errors->first('database_url') }}</span>
				</div>
			</div>
		</div>

		<div class="row">
			<label class="col-md-4 col-form-label">
				Firebase Service Account File (JSON)
				<span class="required text-danger">*</span>
			</label>			
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::file('api_credentials[service_account]', ['class' => 'firebase form-control', 'id' => 'input_service_account', 'accept' => 'mimes/json']) !!}
					<span class="text-danger">
						{{ $errors->first('service_account') }}</span>
				</div>
			</div>
		</div>
		<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.google_client_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[google_client_id]',
						@site_setting('google_client_id'), ['class' => 'form-control', 'id'
						=> 'input_google_client_id']) !!}
						<span
						class="text-danger">{{ $errors->first('google_client_id') }}</span>
					</div>
				</div>
		</div>
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.google_client_secret')<span
				class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('api_credentials[google_client_secret]',
					@site_setting('google_client_secret'), ['class' => 'form-control', 'id'
					=> 'input_google_client_secret']) !!}
					<span
					class="text-danger">{{ $errors->first('google_client_secret') }}</span>
				</div>
			</div>
		</div>
		<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.facebook_client_id')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('api_credentials[facebook_client_id]',
						@site_setting('facebook_client_id'), ['class' => 'form-control', 'id'
						=> 'input_facebook_client_id']) !!}
						<span
						class="text-danger">{{ $errors->first('facebook_client_id') }}</span>
					</div>
				</div>
		</div>
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.facebook_client_secret')<span
				class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('api_credentials[facebook_client_secret]',
					@site_setting('facebook_client_secret'), ['class' => 'form-control', 'id'
					=> 'input_facebook_client_secret']) !!}
					<span
					class="text-danger">{{ $errors->first('facebook_client_secret') }}</span>
				</div>
			</div>
		</div>

	</div>
</div>
</div>

<div class="tab-pane" id="payment_gateway">
	<div class="justify-content-center">
		<div class="card-body ">

			<div class="row">
				<label class="col-md-4 col-form-label pt-md-3">
					@lang('admin_messages.payment_methods')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						@php $payment_methods = explode(',',
						site_setting('payment_methods')); @endphp
						<ul class="row">
							<li class="form-check my-0">
								<label class="form-check-label">
									<input type="checkbox"
									name="payment_gateway[payment_methods][]"
									class="payment_methods form-check-input"
									value="Stripe"
									{{ in_array('Stripe',$payment_methods) || (is_array(old('payment_gateway.payment_methods')) && in_array('Stripe',old('payment_gateway.payment_methods'))) ? 'checked' : '' }} />
									<span class="form-check-sign">
										<span class="check"></span>
									</span>
									Stripe
								</label>
							</li>
							<li class="form-check my-0">
								<label class="form-check-label">
									<input type="checkbox"
									name="payment_gateway[payment_methods][]"
									class="payment_methods form-check-input"
									value="Paypal"
									{{ in_array('Paypal',$payment_methods) || (is_array(old('payment_gateway.payment_methods')) && in_array('Paypal',old('payment_gateway.payment_methods'))) ? 'checked' : '' }} />
									<span class="form-check-sign">
										<span class="check"></span>
									</span>
									Paypal
								</label>
							</li>
							<li class="form-check my-0">
								<label class="form-check-label">
									<input type="checkbox"
									name="payment_gateway[payment_methods][]"
									class="payment_methods form-check-input"
									value="Cash"
									{{ in_array('Cash',$payment_methods) || (is_array(old('payment_gateway.payment_methods')) && in_array('Cash',old('payment_gateway.payment_methods'))) ? 'checked' : '' }} />
									<span class="form-check-sign">
										<span class="check"></span>
									</span>
									Cash
								</label>
							</li>
							<li class="form-check wallet my-0">
								<label class="form-check-label">
									<input type="checkbox"
									name="payment_gateway[payment_methods][]"
									class="payment_methods form-check-input"
									value="Wallet"
									{{ in_array('Wallet',$payment_methods) || (is_array(old('payment_gateway.payment_methods')) && in_array('Wallet',old('payment_gateway.payment_methods'))) ? 'checked' : '' }}  id="wallet"/>
									<span class="form-check-sign">
										<span class="check"></span>
									</span>
									wallet
								</label>
							</li>
						</ul>
					</div>
					<span class="text-danger">{{ $errors->first('payment_methods') }}</span>
				</div>
			</div>

			<div class="row paypal">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.paypal_access_token')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[paypal_access_token]',
						site_setting('paypal_access_token'), ['class' => 'form-control',
						'id' => 'input_paypal_access_token']) !!}
						<span
						class="text-danger">{{ $errors->first('paypal_access_token') }}</span>
					</div>
				</div>
			</div>

			<div class="row paypal">
				<label class="col-md-4 col-form-label">
					Paypal Client<span class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[paypal_client]',
						site_setting('paypal_client'), ['class' => 'form-control', 'id' =>
						'paypal_client']) !!}
						<span
						class="text-danger">{{ $errors->first('paypal_client') }}</span>
					</div>
				</div>
			</div>

			<div class="row paypal">
				<label class="col-md-4 col-form-label">
					Paypal Secret<span class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[paypal_secret]',
						site_setting('paypal_secret'), ['class' => 'form-control', 'id' =>
						'paypal_secret']) !!}
						<span
						class="text-danger">{{ $errors->first('paypal_secret') }}</span>
					</div>
				</div>
			</div>

			<div class="row paypal">
				<label class="col-md-4 col-form-label">
					Paypal Mode<span class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::select('payment_gateway[paypal_mode]',
						['sandbox'=>'Sandbox','live'=>'Live'], site_setting('paypal_mode'),
						['class'=>'form-control paypal_mode']); !!}
						<span class="text-danger">{{ $errors->first('paypal_mode') }}</span>
					</div>
				</div>
			</div>

			<div class="row stripe">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.stripe_publish_key')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[stripe_publish_key]',
						site_setting('stripe_publish_key'), ['class' => 'form-control', 'id'
						=> 'input_paypal_user_name']) !!}
						<span
						class="text-danger">{{ $errors->first('stripe_publish_key') }}</span>
					</div>
				</div>
			</div>

			<div class="row stripe">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.stripe_secret_key')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[stripe_secret_key]',
						site_setting('stripe_secret_key'), ['class' => 'form-control', 'id'
						=> 'input_paypal_password']) !!}
						<span
						class="text-danger">{{ $errors->first('stripe_secret_key') }}</span>
					</div>
				</div>
			</div>
			<div class="row stripe">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.stripe_api_version')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('payment_gateway[stripe_api_version]',
						site_setting('stripe_api_version'), ['class' => 'form-control', 'id'
						=> 'input_stripe_api_version']) !!}
						<span
						class="text-danger">{{ $errors->first('stripe_api_version') }}</span>
					</div>
				</div>
			</div>
				
			<div class="row">
				<label class="col-md-4 col-form-label pt-md-3">
					@lang('admin_messages.payout_methods')<span
					class="required text-danger">*</span>
				</label>
				
				<div class="col-md-5">
					<div class="form-group">
						<ul class="row">
							@foreach($payout_methods as $payout_key => $payout_value) 
							<li class="form-check my-0">
								<label class="form-check-label">
									{!! Form::checkbox('payment_gateway[payout_methods][]',$payout_value,in_array($payout_value,explode(",",site_setting('payout_methods'))), ['class'=>'form-check-input','data-error-placement'=>"container" ,'data-error-container'=>".category_error"]); !!}
									<span class="form-check-sign">
										<span class="check"></span>
									</span>
									{{$payout_value}}
								</label>
							</li>
							@endforeach
						</ul>
					</div>
					<span class="text-danger">{{ $errors->first('payout_methods') }}</span>
				</div>
				
			</div>

		</div>
	</div>
</div>
<div class="tab-pane" id="email_setting">
	<div class="justify-content-center">
		<div class="card-body ">
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_driver')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_driver]',
						site_setting('email_driver'), ['class' => 'form-control', 'id' =>
						'input_email_driver']) !!}
						<span
						class="text-danger">{{ $errors->first('email_driver') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_host')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_host]',
						site_setting('email_host'), ['class' => 'form-control', 'id' =>
						'input_email_host']) !!}
						<span class="text-danger">{{ $errors->first('email_host') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_port')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_port]',
						site_setting('email_port'), ['class' => 'form-control', 'id' =>
						'input_email_port']) !!}
						<span class="text-danger">{{ $errors->first('email_port') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_from_address')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_from_address]',
						site_setting('email_from_address'), ['class' => 'form-control', 'id'
						=> 'input_email_from_address']) !!}
						<span
						class="text-danger">{{ $errors->first('email_from_address') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_from_name')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_from_name]',
						site_setting('email_from_name'), ['class' => 'form-control', 'id' =>
						'input_email_from_name']) !!}
						<span
						class="text-danger">{{ $errors->first('email_from_name') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.email_encryption')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('email_setting[email_encryption]',
						site_setting('email_encryption'), ['class' => 'form-control', 'id'
						=> 'input_email_encryption']) !!}
						<span
						class="text-danger">{{ $errors->first('email_encryption') }}</span>
					</div>
				</div>
			</div>
			<div class="row input_email_user_name"
			{{site_setting('email_driver')=='smtp' ? '':"style=display:none"}}>
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.email_user_name')<span
				class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::text('email_setting[email_user_name]',
					site_setting('email_user_name'), ['class' => 'form-control', 'id' =>
					'input_email_user_name']) !!}
					<span
					class="text-danger">{{ $errors->first('email_user_name') }}</span>
				</div>
			</div>
		</div>
		<div class="row input_email_password"
		{{site_setting('email_driver')=='smtp' ? '':"style=display:none"}}>
		<label class="col-md-4 col-form-label">
			@lang('admin_messages.email_password')<span
			class="required text-danger">*</span>
		</label>
		<div class="col-md-5">
			<div class="form-group">
				{!! Form::text('email_setting[email_password]',
				site_setting('email_password'), ['class' => 'form-control', 'id' =>
				'input_email_password']) !!}
				<span
				class="text-danger">{{ $errors->first('email_password') }}</span>
			</div>
		</div>
	</div>
	<div class="row input_email_domain"
	{{site_setting('email_driver')=='mailgun' ? '':"style=display:none"}}>
	<label class="col-md-4 col-form-label">
		@lang('admin_messages.email_domin')<span
		class="required text-danger">*</span>
	</label>
	<div class="col-md-5">
		<div class="form-group">
			{!! Form::text('email_setting[email_domain]',
			site_setting('email_domain'), ['class' => 'form-control', 'id' =>
			'input_email_domain']) !!}
			<span
			class="text-danger">{{ $errors->first('email_domain') }}</span>
		</div>
	</div>
</div>
<div class="row input_eamil_secret"
{{site_setting('email_driver')=='mailgun' ? '':"style=display:none"}}>
<label class="col-md-4 col-form-label">
	@lang('admin_messages.email_secret')<span
	class="required text-danger">*</span>
</label>
<div class="col-md-5">
	<div class="form-group">
		{!! Form::text('email_setting[email_secret]',
		site_setting('email_secret'), ['class' => 'form-control', 'id' =>
		'input_email_secret']) !!}
		<span
		class="text-danger">{{ $errors->first('email_secret') }}</span>
	</div>
</div>
</div>
</div>
</div>
</div>
<div class="tab-pane" id="fees_manage">
	<div class="card-body">
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.delivery_fee_type')<span
				class="required text-danger">*</span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					{!! Form::select('fees_manage[delivery_fee_type]',
					['0'=>trans('admin_messages.flat_fee'),'1'=>trans('admin_messages.distance_fee')],
					site_setting('delivery_fee_type'), ['class'=>'form-control
					delivery_fee_type']); !!}
					<span
					class="text-danger">{{ $errors->first('delivery_fee_type') }}</span>
				</div>
			</div>
		</div>
		<div
		class="row select_delivery_flat {{site_setting('delivery_fee_type')==0 ? '':'d-none'}}">
		<label class="col-md-4 col-form-label">
			@lang('admin_messages.delivery_fee')<span
			class="required text-danger">*</span>
		</label>
		<div class="col-md-5">
			<div class="form-group">
				{!! Form::text('fees_manage[delivery_fee]',
				site_setting('delivery_fee'), ['class' => 'form-control', 'id' =>
				'input_delivery_fee']) !!}
				<span class="text-danger">{{ $errors->first('delivery_fee') }}</span>
			</div>
		</div>
	</div>
	<div
	class=" select_delivery_percentage {{site_setting('delivery_fee_type')==1 ? '':'d-none'}}">
	<div class="row d-md-flex">
		<label class="col-md-4 col-form-label">
			@lang('admin_messages.pickup_fare')<span
			class="required text-danger">*</span>
		</label>
		<div class="col-md-5">
			<div class="form-group">
				<div class="d-flex align-items-end">
					{!! Form::text('fees_manage[pickup_fare]',
					site_setting('pickup_fare'), ['class' => 'form-control', 'id' =>
					'pickup_fare']) !!}
				</div>
				<span class="text-danger">{{ $errors->first('pickup_fare') }}</span>
			</div>
		</div>
	</div>
	<div class="row d-md-flex">
		<label class="col-md-4 col-form-label">
			@lang('admin_messages.drop_fare')<span
			class="required text-danger">*</span>
		</label>
		<div class="col-md-5">
			<div class="form-group">
				<div class="d-flex align-items-end">
					{!! Form::text('fees_manage[drop_fare]',
					site_setting('drop_fare'), ['class' => 'form-control', 'id' =>
					'drop_fare']) !!}
				</div>
				<span class="text-danger">{{ $errors->first('drop_fare') }}</span>
			</div>
		</div>
	</div>
	<div class="row d-md-flex">
		<label class="col-md-4 col-form-label">
			@lang('admin_messages.distance_fare')<span
			class="required text-danger">*</span>
		</label>
		<div class="col-md-5">
			<div class="form-group">
				<div class="d-flex align-items-end">
					{!! Form::text('fees_manage[distance_fare]',
					site_setting('distance_fare'), ['class' => 'form-control', 'id'
					=> 'distance_fare']) !!}
				</div>
				<span
				class="text-danger">{{ $errors->first('distance_fare') }}</span>
			</div>
		</div>
	</div>
</div>
<div class="d-md-flex">
	<label class="col-md-4 col-form-label">
		@lang('admin_messages.booking_fee')<span
		class="required text-danger">*</span>
	</label>
	<div class="col-md-5">
		<div class="form-group">
			<div class="d-flex align-items-end">
				{!! Form::text('fees_manage[booking_fee]',
				site_setting('booking_fee'), ['class' => 'form-control', 'id' =>
				'booking_fee']) !!}
				<div class="input-group-addon">%</div>
			</div>
			<span class="text-danger">{{ $errors->first('booking_fee') }}</span>
		</div>
	</div>
</div>
<div class="d-md-flex">
	<label class="col-md-4 col-form-label">
		@lang('admin_messages.store_commision_fee')<span
		class="required text-danger">*</span>
	</label>
	<div class="col-md-5">
		<div class="form-group">
			<div class="d-flex align-items-end">
				{!! Form::text('fees_manage[store_commision_fee]',
				site_setting('store_commision_fee'), ['class' => 'form-control',
				'id' => 'store_commision_fee']) !!}
				<div class="input-group-addon">%</div>
			</div>
			<span
			class="text-danger">{{ $errors->first('store_commision_fee') }}</span>
		</div>
	</div>
</div>
<div class="d-md-flex">
	<label class="col-md-4 col-form-label">
		@lang('admin_messages.driver_commision_fee')<span
		class="required text-danger">*</span>
	</label>
	<div class="col-md-5">
		<div class="form-group">
			<div class="d-flex align-items-end">
				{!! Form::text('fees_manage[driver_commision_fee]',
				site_setting('driver_commision_fee'), ['class' => 'form-control',
				'id' => 'driver_commision_fee']) !!}
				<div class="input-group-addon">%</div>
			</div>
			<span
			class="text-danger">{{ $errors->first('driver_commision_fee') }}</span>
		</div>
	</div>
</div>
</div>
</div>
<div class="tab-pane" id="join_us">
	<div class="justify-content-center">
		<div class="card-body ">
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.faceBook_link')
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[join_us_facebook]',
						site_setting('join_us_facebook'), ['class' => 'form-control', 'id'
						=> 'input_join_us_facebook']) !!}
						<span
						class="text-danger">{{ $errors->first('join_us_facebook') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.twitter_link')
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[join_us_twitter]',
						site_setting('join_us_twitter'), ['class' => 'form-control', 'id' =>
						'input_join_us_twitter']) !!}
						<span
						class="text-danger">{{ $errors->first('join_us_twitter') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.youtube_link')
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[join_us_youtube]',
						site_setting('join_us_youtube'), ['class' => 'form-control', 'id' =>
						'input_join_us_youtube']) !!}
						<span
						class="text-danger">{{ $errors->first('join_us_youtube') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.user_apple_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[user_apple_link]',
						site_setting('user_apple_link'), ['class' => 'form-control', 'id' =>
						'input_user_apple_link']) !!}
						<span
						class="text-danger">{{ $errors->first('user_apple_link') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.store_apple_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[store_apple_link]',
						site_setting('store_apple_link'), ['class' => 'form-control', 'id'
						=> 'input_store_apple_link']) !!}
						<span
						class="text-danger">{{ $errors->first('store_apple_link') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.driver_apple_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[driver_apple_link]',
						site_setting('driver_apple_link'), ['class' => 'form-control', 'id'
						=> 'input_driver_apple_link']) !!}
						<span
						class="text-danger">{{ $errors->first('driver_apple_link') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.user_android_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[user_android_link]',
						site_setting('user_android_link'), ['class' => 'form-control', 'id'
						=> 'input_user_android_link']) !!}
						<span
						class="text-danger">{{ $errors->first('user_android_link') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.store_android_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[store_android_link]',
						site_setting('store_android_link'), ['class' => 'form-control', 'id'
						=> 'input_store_android_link']) !!}
						<span
						class="text-danger">{{ $errors->first('store_android_link') }}</span>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 col-form-label">
					@lang('admin_messages.driver_android_link')<span
					class="required text-danger">*</span>
				</label>
				<div class="col-md-5">
					<div class="form-group">
						{!! Form::text('join_us[driver_android_link]',
						site_setting('driver_android_link'), ['class' => 'form-control',
						'id' => 'input_driver_android_link']) !!}
						<span
						class="text-danger">{{ $errors->first('driver_android_link') }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="tab-pane" id="social_media">
	<div class="justify-content-center">
		<div class="card-body ">
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.connect_with_facebook')<span class="required text-danger"></span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					 <div class="checkbox "><label class="switch">
                      <input type="checkbox" checked id="facebook_login">
                      <span class="slider round"></span>
                      </label>
	                  </div>
	                  {!! Form::hidden('social_media[facebook_login]', site_setting('facebook_login'), ['class' => 'form-control', 'id' => 'facebook_login_val']) !!}
					<span class="text-danger">{{ $errors->first('facebook_login') }}</span>
				</div>
			</div>
		</div>	
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.continue_with_google')<span class="required text-danger"></span>
			</label>
			<div class="col-md-5">
				<div class="form-group">
					 <div class="checkbox "><label class="switch">
                      <input type="checkbox" checked id="google_login">
                      <span class="slider round"></span>
                      </label>
	                  </div>
	                  {!! Form::hidden('social_media[google_login]', site_setting('google_login'), ['class' => 'form-control', 'id' => 'google_login_val']) !!}
					<span class="text-danger">{{ $errors->first('google_login') }}</span>
				</div>
			</div>
		</div>	
		<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.continue_with_apple')
			</label>
			<div class="col-md-5">
				<div class="form-group">
					 <div class="checkbox "><label class="switch">
                      <input type="checkbox" checked id="apple_login">
                      <span class="slider round"></span>
                      </label>
	                  </div>
	                  {!! Form::hidden('social_media[apple_login]', site_setting('apple_login'), ['class' => 'form-control', 'id' => 'apple_login_val']) !!}
	                  <small class="text-danger d-block">(If Admin toggles this button, iPhone & android users can use the Apple Id for the signup/Login Process)</small>
					<span class="text-danger">{{ $errors->first('apple_login') }}</span>
				</div>
			</div>
		</div>	
	<!-- 	<div class="row">
			<label class="col-md-4 col-form-label">
				@lang('admin_messages.notes')<span class="required text-danger"></span>
			</label>
			<div class="col-md-5">
				<span class="required "> If your are using iphone You should use apple login</span>
			</div>
		</div> -->
		</div>
	</div>
</div>
</div>
<div class="ml-auto col-md-8">
	<input type="hidden" class="btn btn-finish-submit btn-fill btn-rose btn-wd" type="submit"
	6		name="submit" value="site_setting">
	<button class="btn btn-finish-submit btn-fill btn-rose btn-wd" type="submit" name="submit"
	value="site_setting">
	@lang('admin_messages.submit')
</button>
</div>
<div class="clearfix"></div>
</form>
</div>
</div>
</div>
</div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
	$(document).ready(function () {

		$(".payment_methods").each(function () {
			ShowHide(this);
			if ($('.delivery_fee_type').val() == '1') {
				$('.select_delivery_flat').addClass("d-none");
				$('.select_delivery_percentage').removeClass("d-none");
			} else {
				$('.select_delivery_flat').removeClass("d-none");
				$('.select_delivery_percentage').addClass("d-none");
			}
		});

		$(".payment_methods").click(function () {
			ShowHide(this);
		});

		function ShowHide(elem) {
			if (elem.value == 'Stripe') {
				if ($(elem).is(':checked') == true) {
					$('.stripe').css('display', 'flex');
				} else {
					$('.stripe').css('display', 'none');
				}
			} else if (elem.value == 'Paypal') {
				if ($(elem).is(':checked') == true) {
					$('.paypal').css('display', 'flex');
				} else {
					$('.paypal').css('display', 'none');
				}
			}
		}

				var activeTab = '#{!!$tab!!}';

				if (activeTab) {
					$('[href="' + activeTab + '"]').tab('show');
					tab_id = activeTab.substring(1, activeTab.length);
					$('.btn-finish-submit').attr('value', tab_id);
				}
				$('.nav-link').click(function () {
					var tab_id = $(this).attr('href');
					tab_id = tab_id.substring(1, tab_id.length);
					$('.btn-finish-submit').attr('value', tab_id);
				})
				if ($('#input_email_driver').val() == 'mailgun') {
					$('.input_email_user_name').hide();
					$('.input_email_password').hide();
					$('.input_email_domain').show();
					$('.input_eamil_secret').show();
				}
				if ($('#input_email_driver').val() == 'smtp') {
					$('.input_email_user_name').show();
					$('.input_email_password').show();
					$('.input_email_domain').hide();
					$('.input_eamil_secret').hide();
				}
			});
	$('#input_email_driver').keyup(function () {
		if ($(this).val() == 'mailgun') {
			$('.input_email_user_name').hide();
			$('.input_email_password').hide();
			$('.input_email_domain').show();
			$('.input_eamil_secret').show();
		}
		if ($(this).val() == 'smtp') {
			$('.input_email_user_name').show();
			$('.input_email_password').show();
			$('.input_email_domain').hide();
			$('.input_eamil_secret').hide();
		}
	});
	$('.delivery_fee_type').change(function () {
		if ($(this).val() == '1') {
			$('.select_delivery_flat').addClass("d-none");
			$('.select_delivery_percentage').removeClass("d-none");
		} else {
			$('.select_delivery_flat').removeClass("d-none");
			$('.select_delivery_percentage').addClass("d-none");
		}
	});
	$('#multiple_delivery').change(function() {
		if($(this).is(":checked") == true)
			$('#multiple_delivery_val').val('Yes');
		else
			$('#multiple_delivery_val').val('No');
	});	
		if($('#multiple_delivery_val').val()=='Yes'){
			$('#multiple_delivery').prop('checked',true);
			$('#multiple_delivery_val').val('Yes');
		}
		else{
			$('#multiple_delivery').prop('checked',false);
			$('#multiple_delivery_val').val('No');
		}
	
$('#otp_verification').change(function() {
	if($(this).is(":checked") == true)
		$('#otp_verification_val').val('Yes');
	else
		$('#otp_verification_val').val('No');	
});			
if($('#otp_verification_val').val()=='Yes'){
			$('#otp_verification').prop('checked',true);
			$('#otp_verification_val').val('Yes');
		}
		else{
			$('#otp_verification').prop('checked',false);
			$('#otp_verification_val').val('No');
		}	
$('#facebook_login').change(function() {
		if($(this).is(":checked") == true)
			$('#facebook_login_val').val('Yes');
		else
			$('#facebook_login_val').val('No');
	});	
	if($('#facebook_login_val').val()=='Yes'){
		$('#facebook_login').prop('checked',true);
		$('#facebook_login_val').val('Yes');
	}
	else{
		$('#facebook_login').prop('checked',false);
		$('#facebook_login_val').val('No');
	}

$('#apple_login').change(function() {
		if($(this).is(":checked") == true)
			$('#apple_login_val').val('Yes');
		else
			$('#apple_login_val').val('No');
	});	
	if($('#apple_login_val').val()=='Yes'){
		$('#apple_login').prop('checked',true);
		$('#apple_login_val').val('Yes');
	}
	else{
		$('#apple_login').prop('checked',false);
		$('#apple_login_val').val('No');
	}

$('#google_login').change(function() {
		if($(this).is(":checked") == true)
			$('#google_login_val').val('Yes');
		else
			$('#google_login_val').val('No');
	});	

if($('#google_login_val').val()=='Yes'){
	$('#google_login').prop('checked',true);
	$('#google_login_val').val('Yes');
}
else{
	$('#google_login').prop('checked',false);
		$('#google_login_val').val('No');
}

$('#force_update').change(function() {
	if($(this).is(":checked") == true)
	{
		$('#force_update_val').val('Yes');
	}
	else
	{
		$('#force_update_val').val('No');	

	}
});			
		if($('#force_update_val').val()=='Yes'){
			$('#force_update').prop('checked',true);
			$('#force_update_val').val('Yes');
		}
		else{
			$('#force_update').prop('checked',false);
			$('#force_update_val').val('No');
		}	
</script>
<style type="text/css">
	.firebase {
		opacity: 1 !important;
	    position: static !important;
	    border: none
	}
</style>
@endpush