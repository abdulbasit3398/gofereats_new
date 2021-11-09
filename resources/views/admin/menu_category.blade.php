@extends('admin/template')
@section('main')
<style type="text/css">
  .import_file {
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
  }
  .error_text-danger{
    color: red;
  }
</style>
<div class="content" ng-controller="menu_editor" ng-cloak ng-init="trans_language='en';locale='en';language_list={{ $lang }}">
  <div class="container-fluid">
    <div class="col-md-12 col-md-12 p-0 px-md-3">
      @if(count($errors) > 0)
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <div class="card">
        <div class="card-header card-header-rose card-header-text">
          <div class="card-text">
            <h4 class="card-title">{{@$form_name}}</h4>
          </div>
          <div class="d-md-flex align-items-start mt-4">
            <div class="select">
                {!! Form::open(['url'=>route('admin.import_menu'), 'method'=>'post', 'files'=>true]) !!}
                {{ Form::hidden('store_id', @$store->id) }}
                <div class="input-group d-block d-md-flex mb-3">
                  {{ Form::file('import_file', array('class'=>'import_file form-control w-100 h-40')) }}
                  <span class="input-group-btn p-0 px-md-3">
                    {{ Form::submit('Import', array('class'=>'btn btn-primary my-3 my-md-0 ml-md-2')) }}
                  </span>
                </div>
                {!! Form::close() !!}
                <a href="{{ route('admin.download_sample') }}" class="theme-color"> <i class="icon icon-download-button"></i>Sample Data</a></br>
              <label>
                  <ul>
                    <li>* File should contain only data and image should be uploaded separately.</li>
                   <li>* Don't change the fields format,it may cause error.</li>
                  </ul>
              </label>
              <br>
              <label>
                  <b> Note: This store currency code id {{$store->currency_code}} </b>
              </label>
            </div>
            <div class="d-flex ml-auto">
            <div class="select">
              <select name="trans_language" class="form-control" id="input_language" ng-model="trans_language">
                @foreach($language as $key => $value)
                <option value="{{$key}}"> {{ $value }} </option>
                @endforeach
              </select>
            </div>
            <button type="button" class="btn btn-success ml-3 my-0" ng-click="changeLocale();"> Change </button>
            </div>
          </div>
        </div>
        <input type="hidden" id="store_id" name="store_id" value="{{@$store->user_id}}" ng-init="store_id={{@$store->user_id}};ori_store_id={{@$store->id}};user_id={{$store->user_id}}">
        <div class="card-body add_loading" ng-init="menu={{ $menu }}; category_index = null; menu_index = null;menu_item_index = null; menu_item_details = {};">
          <div class="menu-container row m-0 mt-4">
            <div class="col-md-6 col-lg-3 d-md-flex align-items-end flex-column p-0">
              <ul class="menu-list" ng-if="menu.length">
                <li ng-repeat="menulist in menu" ng-init="initToggleBar()" ng-class="menu_index == $index ? 'open active' : '' " >
                  <a ng-click= "select_menu($index)" href="javascript:void(0)">
                    <i class="icon icon-angle-arrow-pointing-to-right-1 mr-2" ng-class="menu_index == $index ? 'custom-rotate-down':'' "></i>
                    @{{ menulist.menu}}
                  </a>
                  <div class="tooltip-link">
                    <a href="javascript:void(0)" class="icon icon-question-mark"></a>
                    <div class="tooltip-content">
                      <a href="javascript:void(0)" data-toggle="modal" data-target="#edit_modal" ng-click="menu_time($index,menulist.menu_id,menulist.menu)">
                        <i class="icon icon-pencil-edit-button"></i>
                        Edit
                      </a>
                      <a href="javascript:void(0)" data-toggle="modal" data-target="#delete_modal" class="category_delete" ng-click="set($index,'menu')">
                        <i class="icon icon-rubbish-bin"></i>
                        Delete
                      </a>
                    </div>
                  </div>
                  <div class="sub-menu-list">
                    <ul>
                      <li ng-repeat="menucategory in menulist.menu_category"
                      ng-click= "category($index, $parent.$index)" ng-class="category_index == $index && menu_index == $parent.$index ? 'active' : '' ">
                      <a href="javascript:void(0)"class="text-truncate">
                        @{{ menucategory.menu_category  }}
                        <div class="float-right">
                          <i data-toggle="modal" data-target="#sub_edit_modal" ng-click="edit_category(menucategory.menu_category_id,menucategory.menu_category,menucategory)" class="icon icon-pencil-edit-button"></i>
                          <i class="icon icon-rubbish-bin ml-2" data-toggle="modal" data-target="#delete_modal"  ng-click="set($index,'category');">
                          </i>
                        </div>
                      </a>
                    </li>
                  </ul>
                  <a href="javascript:void(0)" data-target="#add_modal"
                  ng-click="add_category(menulist.menu_id)" data-toggle="modal"
                  class="text-uppercase theme-color" ng-show="locale=='en'">
                  add category
                </a>
              </div>
            </li>
          </ul>
          <div class="w-100 mt-auto pt-4">
            <button type="button" data-target="#edit_modal" data-toggle="modal" class="text-uppercase btn text-center"  ng-click="add_menu_pop()" ng-show="locale=='en'">
              Add Menu
            </button>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 d-md-flex align-items-end flex-column p-0 mt-5 mt-md-0" ng-show="category_index !== null">
          <ul class="menu-list" ng-if="menu[menu_index].menu_category[category_index].menu_item.length">
            <li ng-repeat="menu_item in menu[menu_index].menu_category[category_index].menu_item" ng-class="menu_item_index == $index ? 'active' : '' " ng-click="select_menu_item($index)" ng-hide="new_menu_item_index == $index">
              <a href="javascript:void(0)" class="clearfix" ng-click="set($index,'item');">@{{menu_item.menu_item_name}}
                <i data-toggle="modal" data-target="#delete_modal" ng-click="set($index,'item');" class="icon icon-rubbish-bin ml-2 float-right">
                </i>
              </a>
            </li>
          </ul>
          <div class="w-100 mt-auto pt-4 text-md-right text-lg-left">
            <button type="button" class="btn text-center text-uppercase" ng-click="add_new_item()" ng-show="locale=='en'">
              Add Item
            </button>
          </div>
        </div>
        <div class="item_all_details col-md-12 col-lg-6 d-md-flex align-items-end flex-column p-0 mt-5 mt-lg-0" ng-show="menu_item_index !== null">
          <div class="panel-content w-100">
            <form class="form_valitate" id="menu_item_form" enctype="multipart/form-data">
				   @csrf
              <div>
                <label>
                  Item Name <em class="error_text-danger">*</em>
                </label>
                <input type="text" name="menu_item_name" placeholder="Item Name" ng-model="menu_item_details.menu_item_name">
              </div>
              <div class="item-info border-0 my-3">
                <label>
                  Description <em class="error_text-danger">*</em>
                </label>
                <textarea class="form-control" placeholder="Description" name="menu_item_desc" ng-model="menu_item_details.menu_item_desc"> @{{menu_item_details.menu_item_desc}}</textarea>
              </div>
              <div class="row my-3">
                <div class="col-md-4">
                  <label>Price<em class="error_text-danger">*</em></label>
                  <input type="text" name="menu_item_price" ng-model="menu_item_details.menu_item_price">
                 <!--  <div class="select mt-3">
                    <label>
                      Item Type
                    </label>
                    {!!Form::select('item_type', ['0'=>'Veg','1' =>'Non-veg'], '', ['class' => '','placeholder' =>'select type','ng-model'=>'menu_item_details.menu_item_type'])!!}
                  </div> -->
                </div>
                <div class="col-md-4 my-3 mt-md-0">
                  <label>Tax % <em class="error_text-danger">*</em></label>
                  <input type="text" name="menu_item_tax" ng-model="menu_item_details.menu_item_tax">
                </div>
                  <div class="col-md-4">
                    <label>
                      Item Visibility <em class="error_text-danger">*</em>
                    </label>
                    {!!Form::select('item_status', ['1'=>'Active','0' =>'Inactive'], '', ['class' => '','placeholder' =>'select status','ng-model'=>'menu_item_details.menu_item_status'])!!}
                  </div>
              </div>
              <div class="mt-3">
                <div class="file-input">
                  <label>
                    Item Image
                  </label>
                  <input type="file" name="item_image" ng-model="menu_item_details.item_image"  demo-file-model="myFile"  class="form-control" id ="myFileField">
                </div>
                <span class="rec-info d-block mt-2">
                  (Recommended size: 1350*310)
                </span>
                <div class="mt-3">
                  <img ng-show="menu_item_details.item_image" ng-src="@{{menu_item_details.item_image}}" with="100">
                </div>
              </div>
            </form>
            @if($service_type->has_addon == 'Yes')
            <div class="modifiers mt-4">
              <h4 ng-show="locale=='en'">Add modifiers</h4>
              <div class="d-md-flex" ng-show="locale=='en'">
                <input type="text" id="modifier_input" ng-model="modifier_input" class=" my-0 mr-3" placeholder="Search Modifier" ng-change="resetSelectedModifier();">
                <a href="javascript:void(0)" ng-click="addModifierPopup()" class="btn btn-primary m-0">
                  @lang('messages.store.add')
                </a>
              </div>

            
              <div class="menu-group mt-4">
                <div id="accordion" class="menu-accordion">
                  <div class="card my-1" ng-repeat="modifier in menu_item_details.menu_item_modifier">
                    <div class="card-header" id="heading@{{modifier.id}}">
                      <button data-toggle="collapse" data-target="#collapse@{{modifier.id}}" aria-expanded="false" aria-controls="collapse@{{modifier.id}}">
                        <i class="icon icon-angle-arrow-pointing-to-right-1 theme-color mr-2"></i>
                        @{{ modifier.name }}
                      </button>
                      <div class="menu-option">
                        <i class="icon icon-pencil-edit-button" ng-click="openModifierPopup($index)">
                        </i>
                        <i class="icon icon-rubbish-bin ml-2" ng-click="removeModifier($index)">
                        </i>
                      </div>
                    </div>
                    <div id="collapse@{{modifier.id}}" class="collapse" aria-labelledby="heading@{{modifier.id}}" data-parent="#accordion">
                      <div class="card-body row m-0">
                        <ul class="w-100">
                          <li class="row">
                            <div class="col-6">
                              <h4>Item</h4>
                            </div>
                            <div class="col-6">
                              <h4>Additional price</h4>
                            </div>
                          </li>
                          <li class="row" ng-repeat="modifier_item in modifier.menu_item_modifier_item">
                            <div class="col-6">
                              <span>
                                @{{ modifier_item.name }}
                              </span>
                            </div>
                            <div class="col-6">
                              <span>
                                @{{ modifier_item.price }}
                              </span>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <a href="javascript:void(0)" class="btn btn-primary mt-4 d-block" ng-click="openModifierPopup()" ng-show="locale=='en'">
                  @lang('messages.modifiers.add_modifier_group')
                </a>
                <p class="text-danger mt-2"> @{{ modifier_error }} </p>
              </div>
            </div>
            @endif
          </div>
          <div class="w-100 text-right mt-auto pt-4">
            <button type="button" class="btn btn-rose w-100 text-uppercase" ng-click="update_item()">Submit Changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Add category model !-->
<div class="modal fade" id="add_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
      </div>
      <div class="modal-body item_all_details">
        <form class="form_valitate" id="category_add_form">
          <div class="form-group d-flex menu-name">
            <input class="pl-0 w-100" type="text" placeholder="Category name" name="category_name" ng-model="category_name" data-error-placement = "container" data-error-container= "#category-error-box" maxlength = '150' />
          </div>
          <span id="category-error-box"></span>
        </div>
        <div class="mt-3 pt-4 modal-footer px-0 text-right">
          <button data-dismiss="modal" type="cancel" class="btn">CANCEL</button>
          <button type="submit" class="btn btn-rose mx-2 category" ng-disabled='isDisabled' ng-click="save_category('add')">SUBMIT</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
<!-- End Add category model !-->
<!-- category model !-->
<div class="modal fade" id="sub_edit_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
      </div>
      <div class="modal-body item_all_details" >
        <form class="form_valitate" id = "category_edit_form">
          <div class="form-group d-flex menu-name">
            <input class="pl-0 w-100 flex-grow-1" placeholder="Category name"  type="text" ng-model="category_name" name="category_name" ng-value="" data-error-placement = "container" data-error-container= "#category-edit-error-box" maxlength = '150' />
            <input class="pl-0 w-100 flex-grow-1" type="hidden" name="category_id" />
            <!-- <i class="icon icon-pencil-edit-button"></i> -->
          </div>
          <span id="category-edit-error-box"></span>
          <div class="mt-3 pt-4 modal-footer px-0 text-right">
            <button data-dismiss="modal" type="cancel" class="btn">
              CANCEL
            </button>
            <button type="submit" ng-disabled="isDisabled" class="btn btn-rose ml-2" ng-click="save_category('edit')" >
              SUBMIT
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- End category model !-->
<!-- Menu edit modal !-->
<div class="modal fade" id="edit_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
      </div>
      <div class="modal-body item_all_details">
        <form class="update_menu_time">
          <div class="form-group d-flex menu-name">
            <input class="pl-0 w-100 flex-grow-1" placeholder="Menu name" type="text" name="menu_name" ng-model="menu_name" data-error-placement = "container" data-error-container= ".menu_name_error" maxlength = '150'/>
          </div>
          <span class="menu_name_error d-block mb-3"></span>
          <div class="menu-available"  ng-init="menu_timing = '';day_name ={{ json_encode(day_name()) }}">
            <p>When is this menu available?</p>
            <div class="d-md-flex menu-view select-day" ng-repeat="available in menu_timing" >
              <div class="select">
                <select id="menu-select@{{$index}}" ng-model="available.day" name="menu_timing_day[]">
                  <option value="">Select a day</option>
                  <option value="@{{key}}"  ng-selected="available.day==key" ng-repeat="(key,value) in day_name track by $index" ng-if="(key | checkKeyValueUsedInStack : 'day': menu_timing) || available.day==key">@{{value}}</option>
                </select>
              </div>
              <div class="added-times d-flex ml-3 align-items-start">
                <div class="select-time d-flex">
                  <div class="select">
                    {!! Form::select('menu_timing_start_time[]',time_data('time'),'', ['data-end_time'=>'@{{available.end_time}}','placeholder'=>'select','ng-model'=>'available.start_time', 'id'=>'start-select@{{$index}}']); !!}
                  </div>
                  <span class="m-2">to</span>
                  <div class="select">
                    {!! Form::select('menu_timing_end_time[]',time_data('time'),'', ['placeholder'=>'select','ng-model'=>'available.end_time', 'id'=>'end-select@{{$index}}']); !!}
                  </div>
                  <a href="javascript:void(0)" ng-click="remove_menu_time($index,available.id)" class="icon icon-rubbish-bin d-inline-block m-2 mr-0 text-danger"></a>
                </div>
              </div>
            </div>
            <a href="javascript:void(0)" class="theme-color text-uppercase d-block mt-3" ng-click="add_menu_time()" ng-show="menu_timing.length < 7" style="width: max-content;">
              <i class="icon icon-add mr-3"></i>
              add more
            </a>
          </div>
          <div class="mt-3 pt-4 modal-footer px-0 text-right">
            <button data-dismiss="modal" class="btn">
              CANCEL
            </button>
            <button type="submit" class="btn btn-rose ml-2 menubtnDisbale" ng-click="update_menu_time()"> 
              SUBMIT
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- End menu edit modal !-->
<!--category delete !-->
<div class="modal fade" id="delete_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
        <h3 class="modal-title">Delete this <span>@{{delete_name}}</span>?</h3>
      </div>
      <div class="modal-body">
        <p>Are you sure want to delete this @{{delete_name}}. This action cannot be undone.</p>
        <p class="text-danger delete_item_msg"> </p>
      </div>
      <div class="modal-footer text-right">
        <button type="reset " data-dismiss="modal" class="btn">CANCEL</button>
        <button type="submit" class="btn btn-rose ml-2" data-dismiss="modal" ng-click="remove_item(remove_id,delete_name)">SUBMIT</button>
      </div>
    </div>
  </div>
</div>
<!--category delete !-->
<div class="modal fade" id="delete_error_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
        <h4 class="text-danger modal-title delete_item_msg"></h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer text-right">
        <button data-dismiss="modal" type="reset" class="btn btn btn-default">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
<!-- Add modifier modal !-->
<div class="modal fade add_modifier_modal" id="add_modifier_modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header pt-0">
        <button type="button" class="close" data-dismiss="modal">
          <i class="icon icon-close-2"></i>
        </button>
      </div>
      <div class="modal-body item_all_details">
        <form class="update_menu_modifier" id="update_menu_modifier">
          <div class="form-group menu-name menu_head">
            <p> @lang('messages.modifiers.modifier_menu_name') <em class="error_text-danger"> * </em> </p>
            <input type="text" name="menu_name" class="form-control" placeholder="@lang('messages.modifiers.modifier_menu_name')" ng-model="current_modifier.name" />
          </div>
          <div class="menu-available mt-3">
            <p> @lang('messages.modifiers.how_many_items_can_choose') <em class="error_text-danger"> * </em></p>
            <div class="select-range-container">
              <div class="select-range d-md-flex">
                <div class="select w-50 mr-2">
                  <select name="count_type" ng-model="current_modifier.count_type" class="count_type" ng-change="updateRequiredInfo();">
                    <option value="0"> @lang('messages.modifiers.count') </option>
                    <option value="1"> @lang('messages.modifiers.range') </option>
                  </select>
                </div>
                <div class="added-times d-flex ml-3 align-items-center">
                  <input type="text" class="min_count" id="min_count" name="min_count" ng-model="current_modifier.min_count" ng-show="current_modifier.count_type==1" min="0" data-error-placement = "container" data-error-container= ".select-range-container" ng-change="updateRequiredInfo();" placeholder="Minimum item Count">
                  <span class="d-inline-block mx-2" ng-show="current_modifier.count_type==1"> @lang('messages.modifiers.to') </span>
                  <input type="text" class="max_count" name="max_count" ng-model="current_modifier.max_count" data-error-placement = "container" data-error-container= ".select-range-container" ng-change="updateRequiredInfo();" placeholder="Maxmimum item Count">
                </div>
              </div>
            </div>
            <div class="my-4 required-list">
              <p class="mb-1"> 
                @lang('messages.modifiers.is_required') 
                <em class="error_text-danger">*</em>
              </p>
              <ul class="is_required-container">
                <li>
                  <label>
                    <input type="radio" class="require" name="is_required" value="1" ng-model="current_modifier.is_required" ng-checked="current_modifier.is_required == '1'" data-error-placement = "container" data-error-container= ".is_required-container">
                    @lang('messages.modifiers.required')
                  </label>
                </li>
                <li ng-hide="(current_modifier.max_count == 0 && current_modifier.count_type == 0)">
                  <label>
                    <input type="radio" class="optional" name="is_required" value="0" ng-model="current_modifier.is_required" ng-checked="current_modifier.is_required == '0'" data-error-placement = "container" data-error-container= ".is_required-container">
                    @lang('messages.modifiers.optional')
                  </label>
                </li>
              </ul>
            </div>
            <div class="my-4 multiple-list">
              <ul class="is_required-container">
                <li ng-show="(current_modifier.count_type == 1 || (current_modifier.count_type == 0 && current_modifier.max_count > 1))">
                  <label>
                    <input type="hidden" name="is_multiple" value="0" ng-model="current_modifier.is_multiple" ng-checked="current_modifier.is_multiple == '0'">
                    <input type="checkbox" class="custom-checkbox is_multiple"  data-size="sm" name="is_multiple" ng-true-value="1" ng-false-value="0" value="1" ng-model="current_modifier.is_multiple" ng-checked="current_modifier.is_multiple == '1'">
                    @lang('messages.modifiers.is_multiple')
                  </label>
                </li>
              </ul>
            </div>
            <div class="add-list-wrap">
              <div class="add-list-head row">
                <div class="col-6">
                  <label> @lang('messages.modifiers.item') <em class="error_text-danger">*</em> </label>
                </div>
                <div class="col-6">
                  <label> @lang('messages.modifiers.price') ( @lang('messages.modifiers.optional') ) </label>
                </div>
              </div>
              <div class="add-list-row row" ng-repeat="item_modifier in current_modifier.menu_item_modifier_item">
                <div class="col-6">
                  <input type="text" name="item_modifer_name[]" ng-model="item_modifier.name" />
                </div>
                <div class="col-6 d-flex align-items-center">
                  <input type="text" name="item_modifer_price[]"ng-model="item_modifier.price" />
                  <i class="icon icon-rubbish-bin ml-2" ng-click="removeItemModifierItem($index);" ng-hide="(current_modifier.max_count == 0 && current_modifier.count_type == 0) || current_modifier.menu_item_modifier_item.length == 1"></i>
                </div>
              </div>
            </div>
            <div class="add_item">
              <a href="javascript:void(0)" class="theme-color text-uppercase d-inline-block mt-3" ng-click="addItemModifierItem();" ng-hide="(current_modifier.max_count == 0 && current_modifier.count_type == 0) || locale != 'en'">
                <i class="icon icon-add mr-3"></i>
                @lang('messages.modifiers.add_another_item')
              </a>
            </div>
          </div>
          <div class="mt-3 pt-2 modal-footer px-0 text-right d-block">
            <div class="py-2">
              <p class="text-danger required-error"></p>
              <p class="text-danger" ng-show="min_item_error"> @lang('js_messages.store.add_item_based_on_min_count') </p>
            </div>
            <div>
              <button data-dismiss="modal" class="btn btn-default">
                @lang('messages.store.cancel')
              </button>
              <button type="submit" class="btn btn-primary ml-2" ng-click="updateModifier(modifier_index)">
                @lang('messages.store.submit')
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
<!-- End Add modifier modal !-->
</div>
@endsection
