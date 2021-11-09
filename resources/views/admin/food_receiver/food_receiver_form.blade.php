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
                {!! Form::open(['url' => $form_action, 'class' => 'form-horizontal','id'=>'issue_form']) !!}
                  @csrf

                      <div class="row">
                        <label for="input_language" class="col-sm-2 col-form-label">Language<em class="text-danger">*</em></label>
                        <div class="col-sm-6">
                        <div class="form-group">
                          {!! Form::select('language', $language, 'en', ['class' => 'form-control', 'id' => 'input_language', 'disabled' =>'disabled']) !!}
                          </div>
                        </div>
                      </div>
                    <div class="row">
                      <label class="col-sm-2 col-form-label">@lang('admin_messages.name')<span class="required text-danger">*</span></label>
                      <div class="col-sm-10">
                        <div class="form-group">
                        {!! Form::text('name',@$food_receiver->name, ['class' => 'form-control','size'=>'3x3', 'id' => 'input_name',]) !!}
                           <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                      </div>
                    </div>
                    @if(isset($food_receiver->translations)) 
                    <div class="panel" ng-init="translations = {{json_encode(old('translations') ?: $food_receiver->translations)}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}}; result_translations = {{json_encode($food_receiver->translations)}}"> 
                    @else
                     <div class="panel" ng-init="translations = {{json_encode(old('translations') ?: array())}}; removed_translations =  []; errors = {{json_encode($errors->getMessages())}};" ng-cloak>
                    @endif
                      <div class="panel-header">
                        <h4 class="box-title text-center">Translations</h4>
                      </div>
                          <div class="panel-body" ng-init="languages = {{json_encode($languages)}}">
                             <div class="" ng-repeat="translation in translations">
                                <div class="col-sm-12 static_remove">
                                  <button class="btn btn-danger btn-xs" ng-click="translations.splice($index, 1); removed_translations.push(translation.id)">
                                   Remove
                                  </button>
                                </div>
                              <input type="hidden" name="removed_translations" ng-value="removed_translations.toString()">  
                               <div class="row">
                                 <label for="input_language_@{{$index}}" class="col-sm-2 col-form-label">Language<em class="text-danger">*</em></label>
                                  <div class="col-sm-6">
                                    <div class="form-group">
                                       <select name="translations[@{{$index}}][locale]" class="form-control " id="input_language_@{{$index}}" ng-model="translation.locale" >
                                        <option value="" ng-if="translation.locale == ''">Select Language</option>
                                        <option ng-if="!languages.hasOwnProperty(translation.locale) && translation.locale != '';" value="@{{translation.locale}}" >@{{translation.language.name}} </option>
                                         @foreach($languages as $key => $value)
                                            <option value="{{$key}}" ng-if="(('{{$key}}' | checkKeyValueUsedInStack : 'locale': translations) || '{{$key}}' == translation.locale) && '{{$key}}' != 'en'">{{$value}}</option>
                                          @endforeach
                                        </select>
                                         <span class="text-danger ">@{{ errors['translations.'+$index+'.locale'][0] }}</span>
                                      </div>
                                    </div>  
                                 </div>
                                 <div class="row">
                                   <label for="input_name_@{{$index}}" class="col-sm-2 col-form-label">Name<em class="text-danger">*</em></label>
                                    <div class="col-sm-6">
                                      <div class="form-group">
                                         {!! Form::text('translations[@{{$index}}][name]', '@{{translation.name}}', ['class' => 'form-control ', 'id' => 'input_name_@{{$index}}', 'placeholder' => 'Name']) !!}
                                        <span class="text-danger ">@{{ errors['translations.'+$index+'.name'][0] }}</span>
                                      </div>
                                    </div>
                                 </div>
                            <legend ng-if="$index+1 < translations.length"></legend>      

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