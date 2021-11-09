@extends('admin.template')
@section('main')
<div class="content" ng-controller="support">
  <div class="container-fluid">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-rose card-header-text">
          <div class="card-text">
            <h4 class="card-title">Add Support</h4>
          </div>
        </div>
        <div class="card-body">
          {!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'add_support_form','files'=>'true','enctype'=>'multipart/form-data']) !!}
             <div class="row">
                <label class="col-sm-2 col-form-label"> Name <span class="required text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="form-group">
                      {!! Form::text('name', '', ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Name']) !!}
                      <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                  </div>
              </div>
              <div class="row">
                <label class="col-sm-2 col-form-label"> Link <span class="required text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="form-group">
                       {!! Form::text('link', '', ['class' => 'form-control', 'id' => 'input_link', 'placeholder' => 'Link']) !!}
                         <small class="text-danger d-block">Note* : If the link is a contact number, Please fill it with country code</small>
                        <span class="text-danger">{{ $errors->first('link') }}</span>
                    </div>
                  </div>
              </div>
               <div class="row">
            <label class="col-md-2 col-form-label">
              @lang('admin_messages.image')<span class="required text-danger">*</span>
            </label>
            <div class="col-md-4">
              <div class="fileinput fileinput-new" data-provides="fileinput">
                <div class="fileinput-new thumbnail">
                  @if(isset($result->support_image))
                  <img src="{{$result->service_image}}" alt="...">
                  @endif
                </div>
                <div class="fileinput-preview fileinput-exists thumbnail"></div>
                <div class="image_upload">
                  <span class="btn btn-rose btn-round btn-file">
                    <span class="fileinput-new">@lang('admin_messages.select_image')</span>
                    <span class="fileinput-exists">@lang('admin_messages.change')</span>
                    {!! Form::file('support_image',['class' => 'form-control', 'id' => 'support_image','data-error-placement'=>'container','data-error-container'=>'#error-box']) !!}
                  </span>
                  <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> @lang('admin_messages.remove')</a>
                  <span id="error-box"></span>
                </div>
                <span class="text-danger d-block">{{ $errors->first('support_image') }}</span>
              </div>
            </div>
          </div>
              <div class="row">
                <label class="col-sm-2 col-form-label"> Status <span class="required text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="form-group">
                      {!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), '', ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
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