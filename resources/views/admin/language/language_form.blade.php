@extends('admin/template')
@section('main')
<div class="content">
  <div class="container-fluid">
    <div class="col-md-12">
      <div class="card ">
        <div class="card-header card-header-rose card-header-text">
          <div class="card-text">
            <h4 class="card-title"> {{$form_name}} </h4>
          </div>
        </div>
        <div class="card-body ">
          {!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'language_form']) !!}
          <div class="row">
            <label class="col-sm-2 col-form-label">@lang('admin_messages.name')<span class="required text-danger">*</span></label>
            <div class="col-sm-10">
              <div class="form-group">
                {!! Form::text('name',@$language_select->name, ['class' => 'form-control', 'id' => 'input_name',]) !!}
                <span class="text-danger">{{ $errors->first('name') }}</span>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-sm-2 col-form-label">@lang('admin_messages.value')<span class="required text-danger">*</span></label>
            <div class="col-sm-10">
              <div class="form-group">
                {!! Form::text('value',@$language_select->value, ['class' => 'form-control', 'id' => 'input_value',]) !!}
                <span class="text-danger">{{ $errors->first('value') }}</span>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-sm-2 col-form-label">@lang('admin_messages.status')<span class="required text-danger">*</span></label>
            <div class="col-sm-10">
             <div class="form-group">
                {!! Form::select('status',['Active'=>trans('admin_messages.active'),'Inactive'=>trans('admin_messages.inactive')],@$language_select->status, ['placeholder' => trans('admin_messages.select'),'class'=>'form-control']); !!}
                <span class="text-danger">{{ $errors->first('status') }}</span>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="mr-auto">
              <a class="btn btn-fill btn-default btn-wd" href="{{ route('admin.languages') }}">
                @lang('admin_messages.cancel')
              </a>
            </div>
            <div class="ml-auto">
              <button class="btn btn-fill btn-rose btn-wd" type="submit"  value="site_setting">
              @lang('admin_messages.submit')
              </button>
            </div>
            <div class="clearfix"></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
@endsection