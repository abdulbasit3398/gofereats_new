@extends('template')
@section('main')
<main id="site-content" role="main" ng-controller="menu_editor">
	<div class="partners">
		@if(count($errors) > 0)
      <div class="alert-danger p-3">
        <ul>
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
		@include ('store.navigation')
		<div class="menu-editor mt-md-4 mb-5" >
			<h1> @lang('messages.store.menu_editor') </h1>
			
			{!! Form::open(['url'=>route('restaurant.import_menu'), 'method'=>'post', 'files'=>true]) !!}
			{{ Form::hidden('store_id', @$store->id) }}
			<div class="input-group d-block d-md-flex my-4">
				{{ Form::file('import_file', array('class'=>'form-control w-100 h-41 rounded')) }}
				<span class="input-group-btn p-0 px-md-3">
	             {{ Form::submit(trans('messages.store.import'), array('class'=>'btn btn-primary my-3 my-md-0 ml-md-2')) }}
	            </span>
        	</div>
            {!! Form::close() !!}
            <a href="{{ route('restaurant.download_sample') }}" class="theme-color"><i class="icon icon-download-button mr-2"></i> @lang('messages.store.sample_data')</a></br>
            <label class="my-2">
                  <ul>
                    <li>*{{trans('messages.store.file_uploaded_separately')}}
                     </li>
                    <li>* {{ trans('messages.store.dont_change_field_format_cause_error') }} </li>
                  </ul>
              </label>
			<div class="mt-4 mb-5 panel-content add_loading" ng-init="trans_language='en';locale='en';language_list={{ $lang }}">
				<div class="d-md-flex align-items-center justify-content-between">
					<h2>
					@lang('messages.store.craft_your_menu')
					<p class="small" ng-bind-html="current_language"></p>
					</h2>
					<div>
						<h4> @lang('messages.add_translation') <i class="icon-question-mark" data-toggle="tooltip" ata-placement="top" title="@lang('messages.modifiers.add_translation_desc')"></i> </h4>
						<div class="d-flex">
							<div class="select">
								<select name="trans_language" class="form-control" id="input_language" ng-model="trans_language">
									@foreach($language as $key => $value)
									<option value="{{$key}}"> {{ $value }} </option>
									@endforeach
								</select>
							</div>
							<button type="button" class="btn btn-success ml-2" ng-click="changeLocale();"> @lang('messages.modifiers.change') </button>
						</div>
					</div>
				</div>
				<div class="menu-container row m-0 mt-4" ng-init="menu={{ $menu }}; category_index = null; menu_index = null;menu_item_index = null; menu_item_details = {};">
					<div class="col-md-6 col-lg-3 d-md-flex align-items-end flex-column p-0">
						<ul class="menu-list">
							<li ng-repeat="menulist in menu" ng-init="initToggleBar()" ng-class="menu_index == $index ? 'open active' : '' " >
								<a href="javascript:void(0)" ng-click= "select_menu($index)">
									<i class="icon icon-angle-arrow-pointing-to-right-1 mr-2" ng-class="menu_index == $index ? 'custom-rotate-down' : '' "></i>
									@{{ menulist.menu}}
								</a>
								<div id="tooltip_id-@{{ menulist.id}}" class="tooltip-link">
									<a href="javascript:void(0)" class="icon icon-tool-menu"></a>
									<div class="tooltip-content">
										<a href="javascript:void(0)" data-toggle="modal" data-target="#edit_menu_modal" ng-click="menu_time($index,menulist.menu_id,menulist.menu)" class="clearfix">
											<i class="icon icon-pencil-edit-button"></i>
											{{ trans('admin_messages.edit') }}
										</a>
										<a href="javascript:void(0)" data-toggle="modal" data-target="#delete_modal" class="category_delete" ng-click="set($index,'menu')">
											<i class="icon icon-rubbish-bin"></i>
											{{ trans('admin_messages.delete') }}
										</a>
									</div>
								</div>
								<div class="sub-menu-list">
									<ul>
										<li ng-repeat="menucategory in menulist.menu_category"
											ng-click= "category($index, $parent.$index)" ng-class="category_index == $index && menu_index == $parent.$index ? 'active' : '' ">
											<a href="javascript:void(0)" class="clearfix">
												@{{ menucategory.menu_category }}
												<div class="float-right">
													<i data-toggle="modal" data-target="#sub_edit_modal" ng-click="edit_category(menucategory.menu_category_id,menucategory.menu_category,menucategory)" class="icon icon-pencil-edit-button">
													</i>
													<i class="icon icon-rubbish-bin ml-2" data-toggle="modal" data-target="#delete_modal" ng-click="set($index,'category');">
													</i>
												</div>
											</a>
										</li>
									</ul>
									<a href="javascript:void(0)" data-target="#add_category_modal"
										ng-click="add_category(menulist.menu_id)" data-toggle="modal" class="text-uppercase theme-color" ng-show="locale=='en'">
										{{ trans('admin_messages.add_category') }}
									</a>
								</div>
							</li>
						</ul>
						<div class="w-100 mt-auto pt-4">
							<button type="button" data-target="#edit_menu_modal" data-toggle="modal" class="theme-color text-uppercase bg-white text-center w-100" ng-click="add_menu_pop()" ng-show="locale=='en'">
							{{ trans('messages.store_dashboard.add_menu') }}
							</button>
						</div>
					</div>
					<div class="col-md-6 col-lg-3 d-md-flex align-items-end flex-column p-0 mt-5 mt-md-0" ng-show="category_index !== null">
						<ul class="menu-list" ng-if="menu[menu_index].menu_category[category_index].menu_item.length > 0">
							<li ng-repeat="menu_item in menu[menu_index].menu_category[category_index].menu_item" ng-class="menu_item_index == $index ? 'active' : '' " ng-click="select_menu_item($index)" ng-hide="new_menu_item_index == $index">
								<a href="javascript:void(0)" class="clearfix" ng-click="set($index,'item');">@{{menu_item.menu_item_name}}
									<i data-toggle="modal" data-target="#delete_modal" ng-click="set($index,'item');" class="icon icon-rubbish-bin ml-2 float-right">
									</i>
								</a>
							</li>
						</ul>
						<div class="w-100 mt-auto pt-4 text-md-right text-lg-left">
							<button type="button" class="theme-color bg-white text-center text-uppercase w-100" ng-click="add_new_item()" ng-show="locale=='en'">
							@lang('admin_messages.add_item')
							</button>
						</div>
					</div>
					<div class="item_all_details col-md-12 col-lg-6 d-md-flex align-items-end flex-column p-0 mt-5 mt-lg-0" ng-show="menu_item_index !== null">
						<div class="panel-content w-100">
							<form id="item_form" class="form_valitate" enctype="multipart/form-data">
								@csrf
								<label>
									@lang('admin_messages.item_name')
									<span class="required" aria-required="true">*</span>
								</label>
								<input autocomplete="off" type="text" name="menu_item_name" ng-model="menu_item_details.menu_item_name">
								<div class="item-info border-0 mt-3">
									<label> @lang('messages.store.item_description') 
									<span class="required" aria-required="true">*</span></label>
									<textarea name="menu_item_desc" ng-model="menu_item_details.menu_item_desc"> @{{menu_item_details.menu_item_desc}}</textarea>
								</div>
								<div class="row my-3">
									<div class="col-md-4">
										<label> @lang('admin_messages.price') <span class="required" aria-required="true">*</span></label>
										<input autocomplete="off" type="text" name="menu_item_price" ng-model="menu_item_details.menu_item_price">
									</div>
									<div class="col-md-4 my-3 mt-md-0">
										<label> @lang('messages.profile_orders.tax') % <span class="required" aria-required="true">*</span></label>
										<input autocomplete="off" type="text" name="menu_item_tax" ng-model="menu_item_details.menu_item_tax" value="0" placeholder="@lang('messages.profile.percentage')">
									</div>
									<div class="col-md-4">
										<label>
											@lang('messages.store.item_visibility')
											<span class="required" aria-required="true">*</span>
										</label>
										{!!Form::select('item_status', ['1'=>__('admin_messages.active'),'0' =>__('admin_messages.inactive')], '', ['class' => '','placeholder' =>__('messages.store_dashboard.select_status'),'ng-model'=>'menu_item_details.menu_item_status'])!!}
									</div>
								</div>
								<!-- <div class="row my-3">
									<div class="col-md-6">
										<label>
											@lang('messages.store.item_type')
										</label>
										{!!Form::select('item_type', ['0'=>__('messages.store_dashboard.veg'),'1' =>__('messages.store_dashboard.non_veg')], '', ['class' => '','placeholder' => __('messages.store_dashboard.select_type'),'ng-model'=>'menu_item_details.menu_item_type'])!!}
									</div>
								</div> -->
								<div class="row mt-3">
									<div class="col-md-6">
										<label> @lang('messages.store.item_image')
											<span class="rec-info d-block">
												(@lang('messages.store.recommended') @lang('admin_messages.size'): 1350*310)
											</span>
										</label>
										<div class="file-input menu_image">
											<input autocomplete="off" type="file" name="item_image" ng-model="menu_item_details.item_image" demo-file-model="myFile" class="form-control" id ="myFileField" style="visibility:hidden;">
											<a class="choose_file_type banner_choose" id="chooses_file"><span id="banner_name"> @lang('messages.profile.choose_file') </span></a>
											<span class="upload_text" id="file_text"></span>
										</div>
									</div>
									<div class="col-md-6 mt-2 mt-md-0">
										<div class="">
											<img class="img-fluid" ng-show="menu_item_details.item_image && menu_item_details.item_image.length!=null " ng-src="@{{menu_item_details.item_image}}">
										</div>
									</div>
								</div>
							</form>
							@if($service_type->has_addon == 'Yes')
							<div class="modifiers mt-4">
								<h4 ng-show="locale=='en'"> @lang('messages.modifiers.add_modifier') </h4>
								<div class="d-md-flex" ng-show="locale=='en'">
									<input type="text" id="modifier_input" ng-model="modifier_input" class="mr-3" placeholder="@lang('messages.modifiers.search_modifier')" ng-change="resetSelectedModifier();">
									<a href="javascript:void(0)" ng-click="addModifierPopup()" class="btn btn-theme mt-2 mt-md-0">
										@lang('messages.store.add')
									</a>
								</div>
								<div class="menu-group mt-4">
									<div id="accordion" class="menu-accordion">
										<div class="card mb-2" ng-repeat="modifier in menu_item_details.menu_item_modifier">
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
											
											<div id="collapse@{{ modifier.id }}" class="collapse" aria-labelledby="heading@{{modifier.id}}" data-parent="#accordion">
												<div class="card-body row m-0">
													<ul class="w-100">
														<li class="row">
															<div class="col-6">
																<h4>@lang('messages.store.items')</h4>
															</div>
															<div class="col-6">
																<h4>@lang('messages.store.addtional_price')</h4>
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
									<a href="javascript:void(0)" class="theme-color mt-4 d-block modify" ng-click="openModifierPopup()" ng-show="locale=='en'">
										@lang('messages.modifiers.add_modifier_group')
									</a>
									<p class="text-danger mt-2"> @{{ modifier_error }} </p>
								</div>
							</div>
							@endif
						</div>
						<div class="w-100 text-right mt-auto pt-4">
							<button type="button" class="btn btn-theme w-100 text-uppercase" ng-click="update_item()">
							{{ trans('admin_messages.submit_changes') }}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Add category model !-->
	<div class="modal fade" id="add_category_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
					<i class="icon icon-close-2"></i>
					</button>
				</div>
				<div class="modal-body item_all_details">
					<form class="form_valitate" id="category_add_form">
						<div class="form-group d-flex menu-name menu_head">
							<input autocomplete="off" class="pl-0" placeholder="@lang('messages.store.category_name')" type="text" ng-model="category_name"  name="category_name" data-error-placement = "container" data-error-container= "#category-error-box" maxlength = '150' />
						</div>
						<span id="category-error-box"></span>
						<div class="mt-3 pt-4 modal-footer px-0 border-0 text-right">
							<button data-dismiss="modal" type="cancel" class="btn btn-primary theme-color">
							@lang('messages.store.cancel')
							</button>
							<button type="submit" class="btn btn-theme ml-2" ng-click="save_category('add')">
							@lang('messages.store.submit')
							</button>
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
				<div class="modal-body item_all_details">
					<form class="form_valitate" id = "category_edit_form">
						<div class="form-group d-flex menu-name menu_head">
							<input autocomplete="off" class="pl-0" placeholder="{{ trans('messages.store.category_name') }}" type="text" ng-model="category_name" name="category_name" ng-value="" data-error-placement = "container" data-error-container= "#category-edit-error-box" maxlength = '150' />
						</div>
						<span id="category-edit-error-box"></span>
						<div class="mt-3 pt-4 modal-footer px-0 text-right">
							<button data-dismiss="modal" type="cancel" class="btn btn-primary theme-color">
							{{ trans('messages.store.cancel') }}
							</button>
							<button type="submit" class="btn btn-theme ml-2" ng-click="save_category('edit')">
							{{ trans('messages.store.submit') }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- End category model !-->
	<!-- Menu edit modal !-->
	<div class="modal fade edit_menu_modal" id="edit_menu_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
					<i class="icon icon-close-2"></i>
					</button>
				</div>
				<div class="modal-body item_all_details">
					<form class="update_menu_time ">
						<div class="form-group d-flex menu-name menu_head">
							<input autocomplete="off" class="pl-0" placeholder="{{ trans('messages.store.menu_name') }}" type="text" name="menu_name" ng-model="menu_name" data-error-placement = "container" data-error-container= ".menu_name_error" />
						</div>
						<span class="menu_name_error d-block mb-3"></span>
						<div class="menu-available" ng-init="menu_timing = '';day_name ={{ json_encode(day_name()) }}">
							<p>{{ trans('messages.store.when_is_this_menu_available') }}</p>
							<div class="d-md-flex menu-view select-day" ng-repeat="available in menu_timing">
								<div class="select">
									<select id="day-@{{$index}}" ng-model="available.day" name="menu_timing_day[]">
										<option value="">
											{{ trans('messages.store_dashboard.select_a_day') }}
										</option>
										  <option value="@{{key}}"  ng-selected="available.day==key" ng-repeat="(key,value) in day_name track by $index" ng-if="(key | checkKeyValueUsedInStore : 'day': menu_timing) || available.day==key">@{{value}}</option>
									</select>
								</div>
								<div class="added-times ml-3 align-items-start">
									<div class="select-time d-flex">
										<div class="select">
											{!! Form::select('menu_timing_start_time[]',time_data('time'),'', ['placeholder'=>trans('admin_messages.select'),'data-end_time'=>'@{{available.end_time}}','ng-model'=>'available.start_time','id'=>'start-@{{$index}}']); !!}
										</div>
										<span class="m-2">{{ trans('messages.store.to') }}</span>
										<div class="select">
											{!! Form::select('menu_timing_end_time[]',time_data('time'),'', ['placeholder'=>trans('admin_messages.select'),'ng-model'=>'available.end_time','id'=>'end-@{{$index}}']); !!}
										</div>
										<a href="javascript:void(0)" ng-click="remove_menu_time($index,available.id)" class="icon icon-rubbish-bin d-inline-block m-2 mr-0 text-danger"></a>
									</div>
								</div>
							</div>
							<a href="javascript:void(0)" class="theme-color text-uppercase d-inline-block mt-3" ng-click="add_menu_time()" ng-show="menu_timing.length < 7">
								<i class="icon icon-add mr-3"></i>
								{{ trans('messages.store.add_more') }}
							</a>
						</div>
						<div class="mt-3 pt-4 modal-footer px-0 text-right">
							<button data-dismiss="modal" class="btn btn-primary theme-color">
							{{ trans('messages.store.cancel') }}
							</button>
							<button type="submit" class="btn btn-theme ml-2" ng-click="update_menu_time()">
							{{ trans('messages.store.submit') }}
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
					<h3 class="modal-title">
					@lang('messages.store.delete_this')
					<span ng-if="delete_name=='menu'"> @lang('messages.store.menu') </span>
					<span ng-if="delete_name=='category'"> @lang('messages.store.category') </span>
					<span ng-if="delete_name=='item'"> @lang('messages.store.item') </span>
					<span ng-if="delete_name=='modifier'"> @lang('messages.modifiers.modifier') </span>
					@lang('messages.store.ques_mark')
					</h3>
				</div>
				<div class="modal-body">
					<p>
						@lang('messages.store.are_you_sure_to_delete_this')
						<span ng-if="delete_name=='menu'"> @lang('messages.store.menu') </span>
						<span ng-if="delete_name=='category'"> @lang('messages.store.category') </span>
						<span ng-if="delete_name=='item'"> @lang('messages.store.item') </span>.
						<span ng-if="delete_name=='modifier'"> @lang('messages.modifiers.modifier') </span>
						@lang('messages.store.this_action_cannot_undone')
					</p>
					<p class="text-danger delete_item_msg"></p>
				</div>
				<div class="modal-footer text-right">
					<button type="reset" data-dismiss="modal" class="btn btn-primary theme-color"> @lang('messages.store.cancel') </button>
					<button type="submit" class="btn btn-theme ml-2" data-dismiss="modal" ng-click="remove_item(remove_id,delete_name)">
					@lang('messages.store.submit')
					</button>
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
					<h4 class="h5 text-danger modal-title delete_item_msg"></h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer text-right">
					<button data-dismiss="modal" type="reset" class="btn btn-primary">@lang('messages.close')</button>
				</div>
			</div>
		</div>
	</div>
	<!-- Add modifier modal !-->
	<div class="modal fade add_modifier_modal" id="add_modifier_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
					<i class="icon icon-close-2"></i>
					</button>
				</div>
				<div class="modal-body item_all_details">
					<form class="update_menu_modifier" id="update_menu_modifier">
						<div class="form-group menu-name menu_head">
							<p> @lang('messages.modifiers.modifier_menu_name') <em class="text-danger"> * </em> </p>
							<input type="text" name="menu_name" class="form-control" placeholder="@lang('messages.modifiers.modifier_menu_name')" ng-model="current_modifier.name" />
						</div>
						<div class="menu-available mt-3">
							<p> @lang('messages.modifiers.how_many_items_can_choose') <em class="text-danger"> * </em> </p>
							<div class="select-range-container">
								<div class="select-range d-md-flex">
									<div class="select w-50 mr-2">
										<select name="count_type" ng-model="current_modifier.count_type" class="count_type" ng-change="updateRequiredInfo();">
											<option value="0"> @lang('messages.modifiers.count') </option>
											<option value="1"> @lang('messages.modifiers.range') </option>
										</select>
									</div>
									<div class="added-times d-flex ml-3 align-items-center">
										<input type="text" class="min_count" id="min_count" name="min_count" ng-model="current_modifier.min_count" ng-show="current_modifier.count_type==1" min="0" data-error-placement = "container" data-error-container= ".select-range-container" ng-change="updateRequiredInfo();" placeholder="@lang('messages.modifiers.minimum_item_count')">
										<span class="d-inline-block mx-2" ng-show="current_modifier.count_type==1"> @lang('messages.modifiers.to') </span>
										<input type="text" class="max_count" name="max_count" ng-model="current_modifier.max_count" data-error-placement = "container" data-error-container= ".select-range-container" ng-change="updateRequiredInfo();" placeholder="@lang('messages.modifiers.maximum_item_count')">
									</div>
								</div>
							</div>
							<div class="my-4 required-list">
								<p class="mb-1"> @lang('messages.modifiers.is_required') <em class="text-danger"> * </em> </p>
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
											<input type="hidden" name="is_multiple" value="0" ng-model="current_modifier.is_multiple"
											ng-checked="current_modifier.is_multiple == '0'">
											<input type="checkbox" class="custom-checkbox is_multiple"  data-size="sm" name="is_multiple" ng-true-value="1" ng-false-value="0" value="1" ng-model="current_modifier.is_multiple" ng-checked="current_modifier.is_multiple == '1'">
											@lang('messages.modifiers.is_multiple')
										</label>
									</li>
								</ul>
							</div>
							<div class="add-list-wrap">
								<div class="add-list-head row">
									<div class="col-6">
										<label> @lang('messages.modifiers.item') <em class="text-danger">*</em> </label>
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
								<span class="text-danger required-error" style="display: none"></span>
								<p class="small text-danger" ng-show="min_item_error"> @lang('js_messages.store.add_item_based_on_min_count') </p>
							</div>
							<div>
								<button data-dismiss="modal" class="btn btn-primary theme-color">
								@lang('messages.store.cancel')
								</button>
								<button type="submit" class="btn btn-theme ml-2" ng-click="updateModifier(modifier_index)">
								@lang('messages.store.submit')
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- End Add modifier modal !-->
</main>
@stop
@push('scripts')
<script type="text/javascript">
	$('#chooses_file').click(function(){
		$('#myFileField').trigger('click');
		$('#myFileField').change(function(evt) {
			var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
			$('#chooses_file').css("background-color","#43A422");
			$('#chooses_file').css("color","#fff");
			$('#banner_name').text(Lang.get('js_messages.file.file_attached'));
			$('#file_text').text(fileName);
			$('span.upload_text').attr('title',fileName)
		});
	});
</script>
@endpush
