@extends('admin.template')
@section('main')
<div class="content">
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="card" ng-controller="send_message">
				{!! Form::open(['url' => route('admin.send_message'), 'class' => 'form-horizontal','id'=>'send_message-form']) !!}
				<div class="card-header card-header-rose card-header-text">
					<div class="card-text">
						<h4 class="card-title"> Send Message To Users </h4>
					</div>
				</div>
				<div class="card-body" ng-init="users_list = {{ json_encode($users_list) }};target='to_all';message_type='email';last_page={{ $last_page}}">

					<div class="row">
						<label class="col-sm-2 col-form-label pt-2"> Message Type <em class="text-danger">*</em></label>
						<div class="col-sm-10">
							<div class="form-group">
								<input type="radio" id="type_email" name="message_type" value="email" ng-model="message_type">
								<label class="user_label" for="type_email"> Email </label>
								<input type="radio" id="type_push_notification" name="message_type" value="push_notification" class="ml-2" ng-model="message_type">
								<label class="user_label" for="type_push_notification"> Push Notification </label>
								<input type="radio" name="message_type" value="sms" id="type_sms" class="ml-2" ng-model="message_type">
								<label class="user_label" for="type_sms"> SMS </label>
							</div>
							<span class="text-danger"> {{ $errors->first('message_type') }} </span>
						</div>
					</div>

					<div class="row">
						<label class="col-sm-2 col-form-label pt-2"> To <em class="text-danger">*</em></label>
						<div class="col-sm-10">
							<div class="form-group" ng-init="target='to_all'">
								<input type="radio" name="to" id="to_all_user" value="to_all" ng-model="target">
								<label class="user_label" for="to_all_user">All</label>
								<input type="radio" name="to" id="to_type" value="to_type" class="ml-2" ng-model="target">
								<label class="user_label" for="to_type">Specific Type</label>
								<input type="radio" name="to" id="to_specific_user" value="to_specific" class="ml-2" ng-model="target">
								<label class="user_label" for="to_specific_user">Specific Users</label>
							</div>
							<span class="text-danger"> {{ $errors->first('to') }} </span>
						</div>
					</div>

					<div class="row" ng-show="target == 'to_type'">
						<label class="col-sm-2 col-form-label pt-2"> User Type <em class="text-danger">*</em></label>
						<div class="col-sm-10" ng-init="user_type='0'">
							<select name="user_type" ng-model="user_type" class="selectpicker form-group w-100 mt-0" data-style="btn btn-round">
								<option value="0"> User </option>
								<option value="2"> Driver </option>
								<option value="1"> Store </option>
							</select>
							<span class="text-danger"> {{ $errors->first('user_type') }} </span>
						</div>
					</div>

					<div class="row" ng-show="target == 'to_specific'">
						<label class="col-sm-2 col-form-label pt-2"> Users <em class="text-danger">*</em></label>
						<div class="col-sm-10">
							<select name="email[]" class="selectpicker form-group w-100 mt-0" data-style="btn btn-round" data-live-search="true" title="select" multiple>
								<option data-tokens="@{{ user.email }} @{{ user.phone_number }}" value="@{{ user.id }}" ng-repeat="user in users_list"> @{{ user.name }} - @{{ user.type_text }} </option>
							</select>
							<span class="text-danger"> {{ $errors->first('email') }} </span>
						</div>
					</div>

					<div class="row" ng-show="message_type == 'email' ||message_type == 'sms' ">
						<label class="col-sm-2 col-form-label pt-2"> Subject <em class="text-danger">*</em></label>
						<div class="col-sm-10">
							{!! Form::text('subject','',['class' => 'form-control','placeholder' => 'Subject']) !!}
							<span class="text-danger"> {{ $errors->first('subject') }} </span>
						</div>
					</div>

					<div class="row" ng-show="message_type == 'email'">
						<label class="col-sm-2 col-form-label pt-2"> Message (Salutation will be automatically added)<em class="text-danger">*</em> </label>
						<div class="col-sm-10">
							<textarea id="txtEditor" name="txtEditor"></textarea>
							<textarea name="message" id="message" hidden="true"></textarea>
							<span class="text-danger"> {{ $errors->first('message') }} </span>
						</div>
					</div>

					<div class="row" ng-show="message_type != 'email'">
						<label class="col-sm-2 col-form-label pt-2"> Message <em class="text-danger">*</em> </label>
						<div class="col-sm-10">
							<textarea class="form-control" name="push_message" id="push_message"></textarea>
							<span class="text-danger"> {{ $errors->first('push_message') }} </span>
						</div>
					</div>

				</div>
				<div class="card-footer">
					<div class="ml-auto">
						<button name="submit" type="submit" class="btn btn-fill btn-rose btn-wd">
							@lang('admin_messages.submit')
						</button>
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
	$("#txtEditor").Editor(); 
</script>
@endpush
