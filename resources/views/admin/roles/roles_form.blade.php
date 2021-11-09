@extends('admin.template')
@section('main')
@php
	if(old('permission') != null) {
		$old_permissions = old('permission');
	}
@endphp
<div class="content">
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-rose card-header-text">
					<div class="card-text">
						<h4 class="card-title">{{$form_name}}</h4>
					</div>
				</div>
				<div class="card-body">
					{!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'roles_form']) !!}
					<div class="row">
						<label class="col-sm-2 col-form-label"> @lang('admin_messages.name') <span class="required text-danger">*</span> </label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('name',$result->name, ['class' => 'form-control', 'id' => 'input_name']) !!}
								<span class="text-danger">{{ $errors->first('name') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label"> @lang('admin_messages.display_name') <span class="required text-danger">*</span> </label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('display_name',$result->display_name, ['class' => 'form-control', 'id' => 'input_display_name']) !!}
								<span class="text-danger">{{ $errors->first('display_name') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label"> @lang('admin_messages.description') </label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('description',$result->description, ['class' => 'form-control', 'id' => 'input_description']) !!}
								<span class="text-danger">{{ $errors->first('description') }}</span>
							</div>
						</div>
					</div>
					@if(count($permissions))
					<div class="row">
						<label class="col-sm-2 col-form-label"> @lang('admin_messages.permissions') </label>
						<div class="col-sm-10">
							<div class="form-group form-check-group permission row">
								@foreach($permissions as $permission)
								<div class="form-check col-md-6">
									<label class="form-check-label">
										<input type="checkbox" name="permission[]" class="form-check-input" id="permission_{{ $permission->id }}" value="{{$permission->id}}" {{in_array($permission->id,$old_permissions)?"checked":""}}/>
										<span class="form-check-sign">
											<span class="check"></span>
										</span>
										{{ $permission->display_name }}
									</label>
								</div>
								@endforeach
								<span class="text-danger">{{ $errors->first('permission') }}</span>
							</div>
							<span class="cuisine_error"> </span>
						</div>
					</div>
					@endif
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