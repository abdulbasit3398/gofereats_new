@extends('admin/template')
@section('main')
<div class="content">
  <div class="container-fluid">
    <div class="col-md-12">
              <div class="card ">
                <div class="card-header card-header-rose card-header-text">
                  <div class="card-text">
                    <h4 class="card-title">{{$form_name}}</h4>
                  </div>
                </div>
                <div class="card-body ">
                {!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'currency_form']) !!}
                  @csrf
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.name')<span class="required text-danger">*</span></label>
                      <div class="col-sm-10">
                        <div class="form-group">

                          {!! Form::text('name',@$currency_data->name, ['class' => 'form-control', 'id' => 'input_name',]) !!}
                           <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.code')<span class="required text-danger">*</span></label>
                      <div class="col-sm-10">
                        <div class="form-group">
                        {!! Form::text('code',@$currency_data->code, ['class' => 'form-control', 'id' => 'input_code',]) !!}
                           <span class="text-danger">{{ $errors->first('code') }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.symbol')<span class="required text-danger">*</span></label>
                      <div class="col-sm-10">
                        <div class="form-group">
                        {!! Form::text('symbol',@$currency_data->symbol, ['class' => 'form-control', 'id' => 'input_code',]) !!}
                           <span class="text-danger">{{ $errors->first('symbol') }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.rate')<span class="required text-danger">*</span></label>
                      <div class="col-sm-10">
                        <div class="form-group">
                        {!! Form::text('rate',@$currency_data->rate, ['class' => 'form-control', 'id' => 'input_code',]) !!}
                           <span class="text-danger">{{ $errors->first('rate') }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.status')<span class="required text-danger">*</span></label>
                      <div class="col-sm-4">
                        <div class="form-group">
                        {!! Form::select('status',['1'=>trans('admin_messages.active'),'0'=>trans('admin_messages.inactive')],@$currency_data->status, ['placeholder' => trans('admin_messages.select'),'class'=>'form-control']); !!}
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

                  </form>
                </div>
              </div>
            </div>
  </div>
</div>
@endsection