@extends('admin/template')
@section('main')
<div class="content" ng-controller="service_type">
  <div class="container-fluid">
    <div class="col-md-12">
      <div class="card ">
        <div class="card-header card-header-rose card-header-text">
          <div class="card-text">
            <h4 class="card-title">{{$form_name}}</h4>
          </div>
        </div>
        <div class="card-body">
          {!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'service_form','files'=>'true']) !!}
          @csrf

          <input type="hidden" id="service_id" value="{{$result->id}}">
          <div class="row">
            <label for="input_language" class="col-sm-2 col-form-label">Name
              <em class="text-danger">*</em>
            </label>
            <div class="col-sm-6">
              <div class="form-group">
                {!! Form::text('service_name', $result->service_name, ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Name']) !!}
                <span class="text-danger">{{ $errors->first('service_name') }}</span>
              </div>
            </div>
          </div>  
          <div class="row">
            <label for="input_content" class="col-sm-2 col-form-label">Description
              <em class="text-danger">*</em>
            </label>
            <div class="col-sm-6">
              <div class="form-group">
                {!! Form::text('service_description', $result->service_description, ['class' => 'form-control', 'id' => 'service_description', 'placeholder' => 'Description']) !!}
                <span class="text-danger">{{ $errors->first('service_name') }}</span>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-md-2 col-form-label">Image
              <span class="required text-danger">*</span>
            </label>
            <div class="col-md-4 my-4">
              <div class="fileinput fileinput-new" data-provides="fileinput">
                <div class="fileinput-new thumbnail">
                  @if(isset($result->service_image))
                  <img src="{{$result->service_image}}" alt="... Image">
                  @endif
                </div>
                <div class="fileinput-preview fileinput-exists thumbnail"></div>
                <div class="image_upload">
                  <span class="btn btn-rose btn-round btn-file">
                    <span class="fileinput-new">@lang('admin_messages.select_image')</span>
                    <span class="fileinput-exists">@lang('admin_messages.change')</span>
                    {!! Form::file('service_image',['class' => 'form-control', 'id' => 'service_image','data-error-placement'=>'container','data-error-container'=>'#error-box']) !!}
                  </span>
                  <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> @lang('admin_messages.remove')</a>
                  <span id="error-box"></span>
                </div>
                <span class="text-danger d-block">{{ $errors->first('service_image') }}</span>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-md-2 col-form-label">Search Banner Image <span class="required text-danger">*</span>
            </label>
            <div class="col-md-4 my-4">
              <div class="fileinput fileinput-new" data-provides="fileinput">
                <div class="fileinput-new thumbnail">
                  @if(isset($result->service_type_banner_image))
                  <img src="{{$result->service_type_banner_image}}" alt="... Search Banner Image">
                  @endif
                </div>
                <div class="fileinput-preview fileinput-exists thumbnail"></div>
                <div class="image_upload">
                  <span class="btn btn-rose btn-round btn-file">
                    <span class="fileinput-new">@lang('admin_messages.select_image')</span>
                    <span class="fileinput-exists">@lang('admin_messages.change')</span>
                    {!! Form::file('service_type_banner_image',['class' => 'form-control', 'id' => 'service_type_banner_image','data-error-placement'=>'container','data-error-container'=>'#error-box']) !!}
                  </span>
                  <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> @lang('admin_messages.remove')</a>
                  <span id="error-box"></span>
                </div>
                <span class="text-danger d-block">{{ $errors->first('service_type_banner_image') }}</span>
              </div>
            </div>
          </div>


          <div class="panel" ng-init="translations = {{json_encode(old('translations') ?: $result->translate)}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}};" ng-cloak>

            <div class="panel-body">
              <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">
              <div class="" ng-repeat="translation in translations">
               <div class="col-sm-12 static_remove">
                <button class="btn btn-danger btn-xs" ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                 Remove
               </button>
             </div>

             <input type="hidden" name="translations[@{{$index}}][id]" value="@{{translation.id}}">
             <div class="panel-header">
              <h4 class="box-title text-center">Translations</h4>
            </div>
            <div class="row">
             <label for="input_language_@{{$index}}" class="col-sm-2 col-form-label">
              Language<em class="text-danger">*</em></label>
             <div class="col-sm-6">
              <div class="form-group">
               <select name="translations[@{{$index}}][locale]" class="form-control " id="input_language_@{{$index}}" ng-model="translation.locale" >
                <option value="" ng-if="translation.locale == ''">Select Language</option>
                @foreach($languages as $key => $value)
                <option value="{{$key}}" ng-if="(('{{$key}}' | checkKeyValueUsedInStack : 'locale': translations) || '{{$key}}' == translation.locale) && '{{$key}}' != 'en'">{{$value}}</option>
                @endforeach
              </select>
              <span class="text-danger ">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
            </div>
          </div>  
        </div>

        <div class="row">
          <label for="input_name_@{{$index}}" class="col-sm-2 col-form-label">
            Name<em class="text-danger">*</em>
          </label>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::text('translations[@{{$index}}][name]', '@{{translation.name}}', ['class' => 'form-control', 'id' => 'input_name_@{{$index}}', 'placeholder' => 'Name']) !!}
              <span class="text-danger ">@{{ errors['translations.'+$index+'.name'][0] }}</span>
            </div>
          </div>
        </div>
        <div class="row">
          <label for="input_name_@{{$index}}" class="col-sm-2 col-form-label">
            Description<em class="text-danger">*</em>
          </label>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::text('translations[@{{$index}}][description]', '@{{translation.description}}', ['class' => 'form-control', 'id' => 'input_name_@{{$index}}', 'placeholder' => 'Description']) !!}
              <span class="text-danger ">@{{ errors['translations.'+$index+'.description'][0] }}</span>
            </div>
          </div>
        </div>
        <legend ng-if="$index+1 < translations.length"></legend>
      </div>
    </div>
    <div class="panel-footer">
      <div class="row" ng-show="translations.length <  {{count($languages) - 1}}">
        <div class="col-sm-12">
            <button type="button" class="btn btn-info" ng-click="translations.push({locale:''});" >
            <i class="fa fa-plus"></i> Add Translation
          </button>
        </div>
      </div>
    </div> 
  </div>
  <div class="card-footer">
    <div class="ml-auto">
      <button class="btn btn-fill btn-rose btn-wd" type="submit"  name="submit">
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
@push('scripts')
<script type="text/javascript">
  $("#txtEditor").Editor(); 
  console.log("fdgdfgf"+$('#service_description').val());
  $('.Editor-editor').html($('#service_description').val());
</script>
@endpush