@extends('admin.template')
@section('main')
<div class="content">
	<div class="container-fluid" ng-controller="driver_management">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-rose card-header-text">
					<div class="card-text">
						<h4 class="card-title">{{$form_name}}</h4>
					</div>
				</div>
				<div class="card-body">
					{!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'add_user_form','files'=>'true']) !!}
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.first_name')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('first_name',@$driver->first_name, ['class' => 'form-control', 'id' => 'input_first_name']) !!}
								<span class="text-danger">{{ $errors->first('first_name') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.last_name')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('last_name',@$driver->last_name, ['class' => 'form-control', 'id' => 'input_last_name']) !!}
								<span class="text-danger">{{ $errors->first('last_name') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.email')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('email',@$driver->email, ['class' => 'form-control', 'id' => 'input_email',]) !!}
								<span class="text-danger">{{ $errors->first('email') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.password')
							@if(@$driver->email=='')
							<span class="required text-danger">*</span>
							@endif
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('password','', ['class' => 'form-control', 'id' => 'input_password',]) !!}
								<span class="text-danger">{{ $errors->first('password') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.date_of_birth')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('date_of_birth',set_date_on_picker(@$driver->date_of_birth), ['class' => 'form-control', 'id' => 'datepickerdob', 'placeholder' => 'DD-MM-YYYY']) !!}
								<span class="text-danger">{{ $errors->first('convert_dob') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.phone_no')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-2">
							<div class="form-group">
								<select id="phone_code_country" name="country_code" class="form-control">
                                @foreach ($country as $key => $country)
                                    <option value="{{ $country->id }}"data-id="{{ $country->phone_code }}"
                                      @if(  old('country_code')  == $country->id) selected="selected" @else {{ $country->id == @$driver->country_id ? 'selected' : '' }} @endif>{{ $country->name }}</option>
                                @endforeach
                            </select>
								<span class="text-danger">{{ $errors->first('country_code') }}</span>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								{!! Form::text('text',@$driver->country_code?'+'.$driver->country_code:'', ['readonly'=>'readonly','class'=>'form-control','id'=>'apply_country_code']); !!}
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								{!! Form::text('mobile_number',@$driver->mobile_number, ['class' => 'form-control', 'id' =>'input_mobile_number','placeholder'=>trans('admin_messages.phone_no')]) !!}
								<span class="text-danger">{{ $errors->first('mobile_number') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.vehicle_type')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::select('vehicle_type',$vehicle_type,@$driver->driver->vehicle_type, ['id'=>'vehicle_type','placeholder' => trans('admin_messages.select'),'class'=>'form-control']); !!}
								<span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.vehicle_name')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('vehicle_name',@$driver->driver->vehicle_name, ['class' => 'form-control', 'id' => 'input_vehicle_name',]) !!}
								<span class="text-danger">{{ $errors->first('vehicle_name') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.vehicle_number')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('vehicle_number',@$driver->driver->vehicle_number, ['class' => 'form-control', 'id' => 'input_vehicle_number',]) !!}
								<span class="text-danger">{{ $errors->first('vehicle_number') }}</span>
							</div>
						</div>
					</div>
					@foreach($driver_document as $key => $document_name)
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.'.$document_name)
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-10">
							<div class="fileinput fileinput-new" data-provides="fileinput">
								<div class="fileinput-new thumbnail">
									@if(@$driver->driver)
										<a target="_blank" href="{{ $driver->driver->get_document_name($key,'image_name') }}" download="{{ $driver->driver->get_document_name($key,'name') }}" class="download-icon">
											<i class="fa fa-download" aria-hidden="true"></i>
										</a>
										@if($driver->driver->get_document_name($key,'file_extension')=='pdf')
											<img src="{{ asset('images/pdf.png') }}" alt="..." style="max-width: 70px;">
										@else
											<img src="{{@$driver->driver->get_document_name($key,'image_name')}}" alt="...">
										@endif
									@else
										<img src="{{$default_img}}" alt="...">
									@endif
								</div>
								<div class="fileinput-preview fileinput-exists thumbnail"></div>
								<div>
									<span class="btn btn-rose btn-round btn-file">
										<span class="fileinput-new">@lang('admin_messages.select_image')</span>
										<span class="fileinput-exists">@lang('admin_messages.change')</span>
										{!! Form::file('document['.$document_name.']',['class' => 'form-control', 'id' => 'input_'.$document_name]) !!}
									</span>
									<a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
										<i class="fa fa-times"></i>
										@lang('admin_messages.remove')
									</a>
								</div>
								<span class="text-danger">{{ $errors->first($document_name) }}</span>
							</div>
						</div>
					</div>
					@endforeach
					<div class="row">
						<label class="col-sm-2 col-form-label">
							@lang('admin_messages.status')
							<span class="required text-danger">*</span>
						</label>
						<div class="col-sm-4">
							<div class="form-group">
								{!! Form::select('status',$driver_status,@$driver->status, ['placeholder' => trans('admin_messages.select'),'class'=>'form-control']); !!}
								<span class="text-danger">{{ $errors->first('status') }}</span>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="ml-auto">
							<button class="btn btn-fill btn-rose btn-wd" type="submit"  value="site_setting">
							@lang('admin_messages.submit')
							</button>
						</div>
						<div class="clearfix"></div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
	
  $('#phone_code_country').change(function() {
    $('#apply_country_code').val('');
    var phone_code = $(this).find('option:selected').data('id');
    if($(this).val())
    $('#apply_country_code').val('+'+phone_code);
  });

$( document ).ready(function() {
    var user = $('#input_email').val();
    var phone_code   ='';
	   if(user == '')
	   {
	     phone_code = $('select option:first-child').attr("data-id");  
	   }
	   else
	   {
	      phone_code = $(this).find('option:selected').data('id');
	      console.log(phone_code);
	   }
    if($('#phone_code_country').val())
    $('#apply_country_code').val('+'+phone_code);
  }); 

</script>

@endpush
