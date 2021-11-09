@extends('template2')
@section('main')
<main id="site-content" role="main" ng-controller="stores_detail" ng-init="order_data = {{json_encode($order_detail_data)}};store_id={{$store->id}};other_store='{{$other_store}}'">
	<div class="detail-banner" style="background-image: url('{{$store->store_image}}');">
		<div class="container">
			<div class="banner-content product">
				<div class="product-info">
					<input type="hidden" name="check_detail_page" id="check_detail_page" value="1">
					<input type="hidden" id="session_order_data" value="{{json_encode(session('order_data'))}}">
					<h2>
						<a href="#">
							@if($store)
								<span>{{$store->name}}</span>

								@if($store->user_address && $store->user_address->city !='' && $store->user_address->city !=null)
								-  {{$store->user_address->city}}
								@endif
								<p style="font-size:14px;">
								@lang('messages.modifiers.delivery_type'):@lang('messages.modifiers.'.$store->delivery_type)</p>
							@endif
						</a>
					</h2>
					@if(isset($store_cuisine))
						<div class="pro-category">
							<p class="text-truncate">
								@for($i=0;$i<$store->price_rating;$i++)
									{!! session('symbol') ? session('symbol'):default_currency_symbol() !!}
								@endfor
							</p>
							@foreach($store_cuisine as $row)
								<p class="text-truncate">
									<span>•</span>
									{{$row->cuisine_name}}
								</p>
							@endforeach
						</div>
					@endif
					@if(isset($store))
						<div class="product-rating">
							@if($store->review->store_rating_count)
								<span>
									<i class="icon icon-star mr-1"></i>
									{{$store->review->store_rating}} <span>({{$store->review->store_rating_count}})</span>
								</span>
							@endif
							@if($store->status==0)
								<span>{{ trans('messages.store.currently_unavailable') }} </span>
							@elseif(isset($store->store_time->closed)!=0)
								<span>{{ $store->convert_mintime }} – {{ $store->convert_maxtime }} <span>{{trans('messages.store.min')}}</span></span>
							@else
								<span>{{ $store->store_next_opening }} </span>
							@endif
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

	<div class="detail-menu" ng-init="menu_category={{json_encode($menu_category)}}" ng-cloak>
		<div class="container">
			<div class="d-block d-md-flex align-items-center clearfix my-4 my-md-0">
				@if($store_menu->count())
				<div class="select mb-3 mb-md-0 py-3">
					<select id="menu_changes">
						@foreach($store_menu as $menu)
							<option value="{{$menu->id}}">{{$menu->name}}</option>
						@endforeach
					</select>
				</div>
				@endif

				<div class="menu-list">
					<ul class="text-truncate">
						<li ng-repeat="list_of_menu in menu_category" ng-if="$index<7"   id="menu_Category">
							<a href="#@{{list_of_menu.id}}" class="cls_menuaclick" id="menuItem@{{list_of_menu.id}}">
								@{{list_of_menu.name}}
							</a>
						</li>
					</ul>
				</div>

				<div class="more-list ml-auto" ng-show="menu_category.length > 7">
					<a href="#" class="more-btn text-truncate text-right">{{ trans('messages.store.more') }}</a>
					<ul class="more-option">
						<li  ng-repeat="list_of_menu in menu_category" ng-if="$index>6">
							<a href="#@{{list_of_menu.id}}">
								@{{list_of_menu.name}}
							</a>
						</li>
					</ul>
				</div>

			</div>
		</div>
	</div>
	<div class="detail-content">
		
			@if($store_menu->count())
			<div class="clearfix">
				<div class="col-12 col-md-3 col-lg-3 pl-0 pr-0 pr-md-4 float-left">
					<div class="cls_menu-wrapper">
					  <div class="menu-header">
					    <p>&#9776; Categories</p>
					  </div>
					  <div class="menu-list">
					    <ul class="parent-menu">
					      <li class="has-sub">
					        <a href="#">One</a>
					        <ul class="sub-menu">
					          <li class="has-sub">
					            <a href="#">Sub One</a>
					              <ul class="sub-menu">
					          <li><a href="#">Sub One</a></li>
					          <li><a href="#">Sub Two</a></li>
					          <li><a href="#">Sub Three</a></li>
					          <li><a href="#">Sub Four</a></li>
					        </ul>
					            </li>
					          <li><a href="#">Sub Two</a></li>
					          <li><a href="#">Sub Three</a></li>
					          <li><a href="#">Sub Four</a></li>
					        </ul>
					      </li>
					      <li class="has-sub">
					        <a href="#">Two</a>
					        <ul class="sub-menu">
					          <li><a href="#">Sub One</a></li>
					          <li><a href="#">Sub Two</a></li>
					          <li><a href="#">Sub Three</a></li>
					          <li><a href="#">Sub Four</a></li>
					        </ul>
					      </li>
					    </ul>
					  </div>
					</div>
				</div>
				<div class="detail-products col-12 col-md-5 col-lg-5 pl-0 pr-0 pr-md-4 float-left">
						@forelse($store_menu as $menu_category1)
							@forelse($menu_category1->menu_category as $category_row)
								@if($category_row->menu_item->count() > 0)
									<div class="popular mb-4 mb-md-5" id="{{$category_row->id}}">
										<h1>{{$category_row->name}}</h1>
										<div class="pro-row clearfix">
											@forelse($category_row->menu_item as $menu_row)
												@if($menu_row->status==1)
													<div class="pro-item d-flex" data-id="{{$menu_row->id}}" data-name="{{$menu_row->name}}" data-price="{{ ($menu_row->offer_price!=0) ? $menu_row->offer_price : $menu_row->price }}">
														@if($store_time_data==0)
															<label class="sold-out"> @lang('messages.store.closed') </label>
														@elseif($menu_row->is_visible==0)
															<label class="sold-out"> @lang('messages.store.sold_out') </label>
														@elseif($menu_row->menu->menu_closed==0)
															<label class="sold-out">
																@if($menu_row->menu->menu_closed_status=='Available')
																	@lang('admin_messages.available')
																@endif
																@if($menu_row->menu->menu_closed_status=='Un Available')
																	@lang('messages.store.unavailable')
																@endif
																@if($menu_row->menu->menu_closed_status=='Closed')
																	@lang('messages.store.closed')
																@endif
															</label>
														@endif
														<div class="pro-info p-3">
															<h2 class="text-truncate">{{$menu_row->name}}</h2>
															<p class="text-truncate">{{$menu_row->description}}</p>
															<p>
																<span>{!! $store->currency->currency_code !!}</span>
																@if($menu_row->offer_price!=0)
																	<strike>{{$menu_row->price}}</strike> {{$menu_row->offer_price}}
																@else
																	{{$menu_row->price}}
																@endif
																<span>•</span>
																<!-- @if($menu_row->type!==NULL)
																<span class="pro-category theme-color d-inline-block text-nowrap">
																	<i class="icon icon-tree-leaf"></i>
																	@if($menu_row->type=='0')
																		@lang('messages.store.vegetarian')
																	@else
																		@lang('messages.store.non_vegetarian')
																	@endif
																</span>
																@endif -->
															</p>
														</div>
														<div class="pro-img" style="background-image: url('{{$menu_row->menu_item_image}}');">
														</div>
													</div>
												@endif
											@empty
											@endforelse
											{{--end of menu item--}}
										</div>
									</div>
								@endif
							@empty
							@endforelse
						@empty
						@endforelse
						{{--end of menu item list--}}
				</div>
				<div class="checkout mb-5 position-sticky col-12 col-md-4 col-lg-4 p-0 float-right"  id="calculation_form" ng-class="!order_data ? 'disabled':''">
					<form name="order_checkout">
						<button ng-disabled="!order_data" class="btn btn-theme w-100" id="checkout" type="submit">{{ trans('admin_messages.checkout') }}
						</button>
					</form>
					<input type="hidden" id="order_id" value="@{{order_id}}">
					<div class="cart-scroll">
						<div class="checkout-item d-flex align-items-start" ng-repeat="order_row in order_data.items">
							<div class="checkout-select col-3">
								<div class="select">
									<select id='count_quantity1' ng-model="order_row.item_count" data-price='@{{ (menu_item.offer_price!=0) ? menu_item.offer_price : menu_item_price}}' ng-change="order_store_changes(order_row.order_item_id)">
										<option value="" disabled></option>
										@for($i=1;$i<=20;$i++)
											<option value="{{$i}}">{{$i}}</option>
										@endfor
									</select>
								</div>
							</div>
							<div class="checkout-name col-9 pl-md-0">
								<h4 class="d-md-flex justify-content-between">
									<span class="col-md-7 p-0">
										@{{ order_row.name }}
										<div class="modifier-info d-flex" ng-repeat="modifier in order_row.modifier" ng-test="@{{order_row.modifier}}">
											@if (Auth::guest())	
											<span class="">@{{ modifier.item_count | number}}  x @{{ modifier.name }} </span>
											@else
											<span class="">@{{ modifier.count | number}}  x @{{ modifier.name }} </span>
											@endif
											<span class="d-inline-block text-nowrap ml-1"> ( {!!$store->currency->currency_code!!} @{{modifier.price}} ) </span>
										</div>
									</span>
									<span class="col-md-5 d-inline-block text-md-right p-0">
										<span>{!!$store->currency->currency_code!!}</span>
										<span class="d-inline-block">
											@{{ order_row.item_total | number:'2' }}
										</span>
									</span>
								</h4>
								<small ng-if="order_row.item_notes">
									(@{{order_row.item_notes}})<br>
								</small>
								<a class="theme-color" data-remove="@{{$index}}" href="" id="remove_order" ng-click="remove_sesion_data($index)">
									@lang('admin_messages.remove')
								</a>
							</div>
						</div>
					</div>
					<div ng-show="order_data.total_item_count>0" id="subtotal5" >
						<div class="checkout-total d-flex align-items-center" ng-init="total_count_order = {{count(session('order_data') ?? [])-1}}">
							<div class="col-7">
								<h3> @lang('messages.profile_orders.subtotal') 
									<span id="total_item_count" class="d-inline-block">
										(@{{ order_data.total_item_count }} @lang('messages.store.items'))
									</span>
								</h3>
							</div>
							<div class="col-5 text-right">
								<h3><span>{!! $store->currency->currency_code !!}</span>
									<span id="total_item_price">@{{ order_data.subtotal | number : 2}}</span>
								</h3>
							</div>
						</div>
					</div>
					<div class="checkout-info text-center" ng-if="!order_data.items.length">
						<p> @lang('messages.store.add_items_to_your_cart_and_they_appear') </p>
					</div>
				</div>
			</div>
			@else
				<h3 class="py-5 text-center">No Products Available  </h3>
			@endif
		
	</div>

	<!-- Modal -->
	<a href="#" data-toggle="modal" data-target="#myModal" class="toogle_modal" style="display:none"></a>
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title"> @lang('messages.store.start_new_cart') </h3>
				</div>
				<div class="modal-body detail_off">
					<p> @lang('messages.store.your_cart_already_contains') <span> {{ $other_store_detail->name ?? '' }} </span> - <span> {{isset($other_store_detail->user_address->city)? $other_store_detail->user_address->city:''}}</span>. @lang('messages.store.would_you_like_to_clear_cart') {{ $store->name }} - {{isset($store->user_address->city)? $store->user_address->city:''}} @lang('messages.store.instead') </p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal" data-val="cancel"> @lang('admin_messages.cancel') </button>
					<button type="button" class="btn btn-theme store_popup" data-dismiss="modal" data-val="ok"> @lang('messages.store.new_cart') </button>
				</div>
			</div>
		</div>
	</div>
	<div class="detail-popup" ng-cloak>
		<div class="detail-pop-in mx-auto">
			<div class="pop-img" style="background-image: url('@{{menu_item.menu_item_image}}');">
				<i class="icon icon-close-2"></i>
			</div>
			<div class="pro-content">
				<h1> @{{menu_item.name}} </h1>
				<p> @{{menu_item.description}} </p>
				<input type="hidden" id="menu_item_id" value="@{{menu_item.id}}">
				<div class="special-inst mt-3">
					<div class="addons-wrap main_addon" ng-repeat="(key,modifier) in menu_item.menu_item_modifier" data-id="@{{modifier.id}}">
						<div class="addon-name main_addon_detail">
							<h4>
								<input type="hidden" ng-value="@{{ modifier.id }}" ng-model="modifier_value" >
								@{{modifier.name}} 
							</h4>
							<span ng-if="modifier.is_required == '1'">
								@lang('messages.modifiers.required')
							</span>

							<span ng-if="modifier.max_count > 1">
								( @lang('messages.modifiers.choose')
								<span ng-if="modifier.is_required != '1' && modifier.min_count == '0'"> @lang('messages.modifiers.upto') </span>
								<span ng-if="modifier.min_count != '0'"> @{{modifier.min_count}} @lang('messages.modifiers.to') </span>
								@{{modifier.max_count}} )
							</span>
						</div>
						<ul>
							<div class="sub_addon">
								<li ng-repeat="modifier_item in modifier.menu_item_modifier_item">
									<div class="d-flex align-items-center justify-content-between menu_select">
										<div>
										<label ng-init="modifier_item.item_count = 0;modifier_item.is_selected = false;">
											<div class="quantity order-2 order-md-1 mr-md-2 d-flex align-items-center" ng-if="modifier.is_multiple == '1'">
												<button class="value-changer" ng-click="updateModifierItem(modifier_item,'decrease');" ng-hide="modifier_item.item_count < 1">
													<i class="icon icon-remove"></i>
												</button ng-hide="modifier_item.item_count == '0'">
												<input type='hidden' ng-model="modifier_item.item_count">
												<span class="mx-2" ng-hide="modifier_item.item_count == '0'"> @{{ modifier_item.item_count }} </span>
												<button class="value-changer" ng-click="updateModifierItem(modifier_item,'increase');" ng-disabled="modifier.isMaxSelected">
													<i class="icon icon-add"></i>
												</button>
											</div>
											<div ng-if="modifier.is_multiple == '0' && modifier.max_count > '1' && modifier.max_count >= '1'">
												<input type="checkbox" id="menu_toppin-@{{modifier_item.id}}" class="custom-checkbox toppin_menu" ng-model="modifier_item.is_selected" ng-click="updateCount(modifier_item);" ng-disabled="modifier.isMaxSelected && !modifier_item.is_selected">
											</div>

											<div ng-if="(modifier.is_multiple == '0' && modifier.is_required == '0' && modifier.max_count == '1' && modifier.count_type == '0') || (modifier.is_multiple == '0' && modifier.is_required == '1' && modifier.max_count == '1' && modifier.count_type == '0') || (modifier.is_multiple == '0' && modifier.is_required == '1' && modifier.max_count == '1' && modifier.count_type == '1')">
												<input type="radio" id="menu_toppin-@{{modifier_item.id}}" class="custom-checkbox toppin_menu" ng-click="updateRadioCount(key,modifier_item.id);" name="menu1" ng-checked="modifier_item.is_selected">
											</div>
											
											<div ng-if="modifier.is_multiple == '0' && modifier.is_required == '1' && modifier.max_count == 0">
												<input type="radio" id="menu_toppin-@{{modifier_item.id}}" class="custom-checkbox toppin_menu" checked>
											</div>
										</label>
											<span  ng-show="modifier_item.item_count == '0'"> @{{ modifier_item.name }} </span>

											<span ng-show="modifier_item.item_count != '0'"> @{{ modifier_item.item_count }} x @{{ modifier_item.name }}</span>
										</div>
										<span ng-show="modifier_item.price > 0 && modifier_item.item_count == '0'">
											+{!! session('symbol') !!} 
											@{{modifier_item.price}}
										</span>

										<span ng-show="modifier_item.price > 0 && modifier_item.item_count != '0'">
											+{!! session('symbol') !!} 
											@{{modifier_item.price * modifier_item.item_count | number : 2}}
										</span> 

									</div>
								</li>
							</div>
						</ul>
					</div>
					<h4>{{ trans('messages.store.special_instructions') }}</h4>
					<input class="p-2 w-100" type="text" ng-model="add_notes" placeholder="{{ trans('messages.store.add_note_extra_sauce_no_onions') }}"/>
				</div>
				<div class="pro-cart d-block d-md-flex align-items-center">
					<div class="quantity d-flex align-items-center col-12 col-md-5 mb-3 mb-md-0 justify-content-center justify-content-md-start">
						<button class="value-changer" data-val="remove">
							<i class="icon icon-remove"></i>
						</button>
						<span class="mx-3" ng-bind="item_count"></span>
						<button class="value-changer" data-val="add">
							<i class="icon icon-add"></i>
						</button>
					</div>

					<span style="display:none;">
						@{{ (menu_item.offer_price>0) ? menu_item.offer_price : menu_item.price}}
					</span>

					<div class="cart-btn col-12 col-md-7" ng-init="individual_price = individual_price">
						@if($store_time_data == 0)
							<button class="btn btn-theme w-100 disabled" type="submit"> @lang('messages.store.closed') </button>
						@elseif($store->status==0)
							<button class="btn btn-theme w-100 disabled" type="submit"> @lang('messages.store.currently_unavailable') </button>
						@else
							<button ng-if="menu_item.is_visible == 0" disabled="disabled" class="btn btn-theme w-100">{{ trans('messages.store.item_is_sold_out') }}</button>
							<button class="btn btn-theme w-100" ng-disabled="cartDisabled" ng-if="menu_item.is_visible != 0 && menu_item.menu_item_status!=0" type="submit" id="cart_sumbit" ng-click="order_store_session()" data-val="@{{menu_item.is_visible}}">
								@lang('admin_messages.add')
								<span class="count_item" ng-bind="item_count"> </span>
								<span> @lang('messages.store.to_cart') </span>
								<span class="span_close">(<span>{!!$store->currency->currency_code!!}</span>
								<span ng-hide="menu_item" class="ml-2" id="menu_item_price"></span>
								<span> @{{ menu_item_price }} </span> )</span>
							</button>
							<button class="btn btn-theme w-100" disabled="disabled" ng-if=" menu_item.is_visible != 0 && menu_item.menu_item_status==0" > @lang('messages.store.item_is') <span ng-if="menu_item.menu_closed_status=='Available'"> @lang('admin_messages.available') </span><span ng-if="menu_item.menu_closed_status=='Un Available'"> @lang('messages.store.unavailable')</span><span ng-if="menu_item.menu_closed_status=='Closed'"> @lang('messages.store.closed') </span></button>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection
@push('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		function category_menu() {
			var a = $('header').outerHeight();
			var b = $('.detail-menu').outerHeight();
			var menu_top = $('.detail-banner').position().top + $('.detail-banner').outerHeight();
			if ($(window).scrollTop() >= (menu_top - a)) {
				$('.detail-menu').css({"top":a + "px"});
				$('.detail-menu').addClass('active');
				$('.detail-content').css({"margin-top":b + "px"});
				$('.checkout').css({"top":a + b + 20 + "px"});
				$('header').addClass('no-shadow');
			} else {
				$('.detail-menu').css({"top":"inherit"});
				$('.detail-menu').removeClass('active');
				$('.detail-content').css({"margin-top":"0px"});
				$('header').removeClass('no-shadow');
			}
		}
		category_menu();
		$(window).scroll(function() {
			category_menu();
		});
	});

	$(document).on('click','.menu-list li a',function(e) {
		e.preventDefault();
		var target = $(this).attr("href");
		var top = $(target).offset().top - ($('header').outerHeight() + $('.detail-menu').outerHeight() + 10);
		$('html, body').stop().animate({
			scrollTop: top
		}, 600, function() {
		});
	});
</script>
@endpush
