@extends('new-template')

@section('main')
<main ng-controller="newdetails" ng-init="order_data = {{json_encode($order_detail_data)}};store_id={{$store->id}};schedule_status= '{{session('schedule_data') ? trans('messages.store.'.session('schedule_data')['status']):trans('messages.store.ASAP')}}';schedule_time_value={{json_encode(time_data('schedule_time'))}};other_store='{{$other_store}}'">
    <div class="cls_bannertop" style="background-image: url('{{$store->store_image}}');">    </div>
    <input type="hidden" name="check_detail_page" id="check_detail_page" value="1">
    <input type="hidden" id="service_type" value="{{session('service_type')}}">
    <input type="hidden" id="delivery_type" value="{{session('delivery_type')}}">
    <div class="cls_deliverypickup">
        <div class="container">
            <div class="cls_whole">
                <div class="cls_wholein"  ng-class="{'active': isActive}">
                    <div class="cls_formwithselect" style="border-left: unset;margin-left: 0px;">
                         <div class="selectcustom oralselect" id="categories">
                          <select id="category_select" class="liselect ">
                            @foreach($menu_category as $key => $name)
                              <option value="{{$key}}">{{$name}} </option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>
                <div class="cls_in" ng-class="{'active': isActive}" ng-init="location = '{{session('location')}}'">
                     <input ng-model="search_key" autocomplete="off" class="custom_input" type="text" placeholder="{{trans('messages.store.search')}}" id="item_search" onfocus="this.placeholder = '{{trans('messages.store.search')}}'" />

                    <i class="icon icon-search-3 left"></i>

                    <i class="icon icon-close-2 right" ng-class="{'remove': remove}" ng-click=removeclick() ></i>
                    
                </div>
                @if(!Auth::check())
                    <span></span>
                    <div class="cls_items" ng-show="order_data.total_item_count >0" >
                        <span > {{trans('messages.store.items')}} <strong ng-bind="order_data.total_item_count" class="ng-binding"></strong></span> 
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="cls_topcategory mt-5">
        <div class="container">
            <div class="title">
                <h3>{{$store->name}}</h3>
            </div>
            <div class="cls_detailslist">
                <ul>
                    <li style="cursor: pointer;" id='schedule_order' data-toggle="modal" ><i class="icon icon-timer"></i> <span>{{$store->convert_mintime}} - {{$store->convert_maxtime}}{{trans('messages.store.min')}}</span></li>
                    <li><i class="icon icon-placeholder"></i> <span>{{session('location')}}</span></li>
                   <li style="cursor: pointer;" onclick="enableMap()"> <span>More Info</span> <i class="icon icon-down-arrow" ng-class="more ? 'rotate' : 'unrotate' " style="font-size: 12px;vertical-align: middle;"></i></li>
                </ul>
            </div>

            <div class="cls_moredetails" id="more_info" style="display: none">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h4>@lang('messages.search.open_hours')</h4>
                            <!-- <p>Order Delivery until 6:30 PM</p> -->
                        </div>
                        <ul>
                            @foreach($store_time as $time)
                            <li class="today">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <h5>{{ $time->day_name }}</h5>
                                    <p>{{ $time->start_time }} - {{ $time->end_time }}</p>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-lg-7">    
                        <div class="cls_map" id="map">
                           
                        </div>
                    </div>
                </div>

            </div>
        </div>
     </div>
    <div class="cls_slices" ng-init="menu_category={{ json_encode($menu_category) }};menu_item={{ json_encode($menu_item) }};page=2;store_id={{request()->store_id}};total_page={{$total_page}};store_time_data={{ $store_time_data }};" >
        <div class="container">
            <div class="cls_nearbyin">
                <div class="row">
                <!-- Menu Category and Item List Out Here -->
                    <div class="col-lg-8 col-md-8 col-12">
                        <div title="menu-category" ng-repeat="(category_key,category) in menu_item track by $index">

                            <div class="title my-4" id="category_@{{category_key}}">
                                <h3> @{{ category[0].menu_category.name }}
                                 <span ng-if="category[0].menu.menu_closed_status=='Available' && category[0].menu.menu_closed==0" class="cls_lable">
                                        @lang('messages.store.available')
                                    </span>
                                    <span ng-if="category[0].menu.menu_closed_status=='Un Available' && category[0].menu.menu_closed==0 || store_time_data==0" class="cls_lable">
                                         @lang('messages.store.unavailable')
                                    </span>
                                    </h3>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6" ng-repeat="item in category">
                                    <a href="" class="cls_slicesleft pro-item-detail" ng-click="checked = !checked" data-toggle="modal" data-id="@{{item.id}}" data-name="@{{item.name}}" data-price="@{{ (item.offer_price!=0) ? item.offer_price : item.price }}" >
                                        <div class="row">
                                            <div class="col-lg-4 col-4">
                                                 <img src="@{{ item.menu_item_image }}" alt="@{{ item.name }}"/>
                                            </div>
                                            <div class="col-lg-8 col-8">
                                                <div class="cls_sliceslefttxt" >
                                                    <h3 class="text-truncate"> @{{ item.name }} </h3>
                                                    
                                                    <div class="rate">
                                                        <p>
                                                            <span>{!! $currency_code !!}</span>
                                                            <span ng-if="item.offer_price!=0">
                                                                <strike>@{{item.price}}</strike> 
                                                                @{{item.offer_price}}
                                                            </span>
                                                            <span ng-if="item.offer_price=='0'">
                                                                @{{item.price}}
                                                            </span>
                                                            <span>•</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                         <div ng-show="menu_item.length == 0"><h3> {{trans('messages.store.no_result')}} </h3></div>
                    </div>

                    <!-- Cart Details Start Here --> 
                    <div class="col-lg-4 col-md-4 col-12" ng-show="order_data.items.length > 0">
                       <div class="cls_cart">
                            <div class="title">
                            </div>
                            <div class="cartlist" >
                                <div class="cartlistin" ng-repeat="order_row in order_data.items" >
                                    <div class="col-lg-8 col-8" >
                                        <h4 class="text-truncate">@{{ order_row.name }}</h4>
                                        <div class="" ng-repeat="modifier in order_row.modifier" >
                                            @if (Auth::guest()) 
                                            <span class="">@{{ modifier.item_count | number}}  x @{{ modifier.name }} </span>
                                            @else
                                            <span class="">@{{ modifier.count | number}}  x @{{ modifier.name }} </span>
                                            @endif
                                            <span class="d-inline-block text-nowrap ml-1"> ( {!!$store->currency->currency_code!!} @{{modifier.price | number:'2'}} ) </span>
                                        </div>
                                    <span >{!!$store->currency->currency_code!!}  @{{ order_row.item_total | number:'2' }}</span>
                                    <p ng-if="order_row.item_notes" class="text-left">(@{{order_row.item_notes}})</p>
                                    </div>
                                    <div class="col-lg-4 col-4 p-0" ng-show='order_row.item_count > 0'>
                                    <div class="plusmins" >
                                        <button class="minus" ng-click=order_store_changes($index,'remove',order_row.order_item_id) ng-hide="order_row.item_count < 1">-</button>
                                         <input ng-model="order_row.item_count" type="text" id=@{{$index}} value="@{{order_row.item_count}}">
                                        <button class="plus" ng-click=order_store_changes($index,'add',order_row.order_item_id) >+</button>
                                    </div>
                                    </div>
                                </div>
                                <div class="cartlisttotal my-4">
                                    <div class="totle">
                                        <h4>@lang('messages.profile_orders.subtotal')</h4>
                                        <!-- <p>Extra changes may apply</p> -->
                                    </div>
                                    <p id="total_item_price" ng-show='order_data.subtotal !=0'>{!!$store->currency->currency_code!!}  @{{ order_data.subtotal | number : 2}}</p>
                                </div>
                                <input type="hidden" id="order_id" value="@{{order_id}}">
                                <form name="order_checkout">
                                    <button ng-disabled="!order_data" class="btn cls_checkout" id="checkout" type="submit">{{ trans('messages.store.checkout') }}
                                    </button>
                                </form>
                            </div>
                       </div>
                    </div>  
                    <!-- Cart Details End Here -->
                </div>
            </div>
        </div>
    </div>
   
    <div class="modal cls_modal fade bd-example-modal-lg" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true"  >
      <div class="modal-dialog customlarge modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          
          <div class="modal-body p-0">
            <div class="cls_cartpopup">  
                <div class="row">
                    <div class="col-lg-6 pr-0">
                        <img class="cls_search" src="@{{menu_item_add.menu_item_image}}">
                    </div>
                    <div class="col-lg-6" ng-test="@{{menu_item_add}}">

                        <div class="cls_cartpopupin">
                            <button type="button" class="close" ng-click="closePopup()" data-dismiss="modal" aria-label="Close">
                          <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><defs><path id="icon-close_svg__a" d="M0 1.5L1.5 0 8 6.5 14.5 0 16 1.5 9.5 8l6.5 6.5-1.5 1.5L8 9.5 1.5 16 0 14.5 6.5 8z"></path></defs><use xlink:href="#icon-close_svg__a" transform="translate(4 4)"></use></svg>
                        </button>
                            <div class="title pt-5">
                                <h4 class="text-truncate"> @{{menu_item_add.name}}</h4>
                                <p class="text-truncate">@{{menu_item_add.description}}</p>
                            </div>
                              <div ng-repeat="(key,modifier) in menu_item_add.menu_item_modifier" data-id="@{{modifier.id}}">
                                 <div class="d-flex align-items-center flex-wrap justify-content-between cls_head">
                                    <h4 class="text-truncate"><input type="hidden" ng-value="@{{ modifier.id }}" ng-model="modifier_value" > @{{modifier.name}} </h4>
                                    <h5 ng-if="modifier.is_required == '1'"> @lang('messages.modifiers.required') <span ng-if="modifier.is_required != '1' && modifier.min_count == '0'"> @lang('messages.modifiers.upto') </span>
                                <span ng-if="modifier.min_count != '0'"> @{{modifier.min_count}} @lang('messages.modifiers.to') </span>
                                @{{modifier.max_count}}</h5>
                                </div>
                                <div class="menulist" ng-repeat="modifier_item in modifier.menu_item_modifier_item">
                                    <div class="plusmins" ng-if="modifier.is_multiple == '1'">
                                        <label ng-init="modifier_item.item_count = 0;modifier_item.is_selected = false;"> 
                                            <button class="value-changer minus" ng-click="updateModifierItem(modifier_item,'decrease');" ng-hide="modifier_item.item_count < 1">-</button ng-hide="modifier_item.item_count == '0'">
                                           <input type='hidden' ng-model="modifier_item.item_count">
                                             <span class="mx-2" ng-hide="modifier_item.item_count == '0'"> @{{ modifier_item.item_count }} </span>
                                            <button class="value-changer plus" ng-click="updateModifierItem(modifier_item,'increase');" ng-disabled="modifier.isMaxSelected">+</button>
                                        </label>
                                    </div>
                                    <div class="radio"  ng-if="(modifier.is_multiple == '0' && modifier.is_required == '0' && modifier.max_count == '1' && modifier.count_type == '0') || (modifier.is_multiple == '0' && modifier.is_required == '1' && modifier.max_count == '1' && modifier.count_type == '0') || (modifier.is_multiple == '0' && modifier.is_required == '1' && modifier.max_count == '1' && modifier.count_type == '1')">
                                        <div class="form-check form-check-inline">
                                          <input class="form-check-input" type="radio" id="menu_toppin-@{{modifier_item.id}}" class="custom-checkbox toppin_menu" ng-click="updateRadioCount(key,modifier_item.id);" name="menu1" ng-checked="modifier_item.is_selected">
                                        </div>
                                    </div>
                                     <div class="checkbox" ng-if="modifier.is_multiple == '0' && modifier.max_count > '1' && modifier.max_count >= '1'">
                                        <div class="form-check form-check-inline">
                                          <input class="form-check-input" type="checkbox"value="option1" id="menu_toppin-@{{modifier_item.id}}" class="custom-checkbox toppin_menu" ng-model="modifier_item.is_selected" ng-click="updateCount(modifier_item);" ng-disabled="modifier.isMaxSelected && !modifier_item.is_selected">
                                        </div>
                                    </div>
                                     <h4 class="text-truncate">@{{ modifier_item.name }}</h4>

                                    <p ng-show="modifier_item.price > 0 && modifier_item.item_count == '0'"> {!! session('symbol') !!}  @{{modifier_item.price}}</p>
                                    <p ng-show="modifier_item.price > 0 && modifier_item.item_count != '0'"> {!! session('symbol') !!}  @{{modifier_item.price}}</p>

                                </div>
                            </div>
                            <h5 class="mt-5">{{ trans('messages.store.special_instructions') }}</h5>
                            <div class="note">
                                <textarea ng-model="add_notes" placeholder="Add Note"></textarea>
                            </div>
                            @if($store_time_data == 0)
                             <div  disabled="disabled" class="addbutton mt-3">
                                <button class="addcartbtnnn" href class="text-center pl-0 pr-0"> @lang('messages.store.closed') </button> </div>
                            @elseif($store->status==0)
                             <div  disabled="disabled" class="addbutton mt-3">
                                <button class="addcartbtnnn" class="text-center pl-0 pr-0" type="submit"> @lang('messages.store.currently_unavailable') </button> </div>
                            @else
                            <div  disabled="disabled" class="addbutton mt-3 text-center"><button  class="addcartbtnnn" ng-if="menu_item_add.is_visible == 0" class="text-center pl-0 pr-0">{{ trans('messages.store.item_is_sold_out') }}</button></div>
                            <div class="addbutton mt-3"  ng-disabled="cartDisabled" ng-if="menu_item_add.is_visible != 0 && menu_item_add.menu_item_status!=0" id="cart_sumbit" data-val="@{{menu_item_add.is_visible}}">
                                @if($store->status == 1)
                                <button class="addcartbtnnn text-truncate"  ng-click="order_store_session()"> @lang('messages.store.add') <span class="count_item" ng-bind="item_count"> </span>  @lang('messages.store.to_cart') <span class="span_close">(<span>{!!$store->currency->currency_code!!}</span>
                                <span ng-hide="menu_item" class="ml-2" id="menu_item_price"></span>
                                <span> @{{ menu_item_price }} </span> )</span></button>
                                 <div class="plusmins">
                                    <button class="minus value-changer" data-val="remove">-</button>
                                        <span style="font-size: 20px" class="mx-3" ng-bind="item_count"></span>
                                    <button class="plus value-changer" data-val="add">+</button>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>

    <div class="modal cls_asap fade bd-example-modal-md" id="modalasap" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" >
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
          <!-- <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div> -->
          <button type="button" class="close" ng-click="closePopup()" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          <div class="modal-body p-0">
            <div class="cls_asp">  
                <div class="title">
                    <h3>{{trans('messages.home_delivery.select_delivery_time')}}</h3>
                </div>
                <div class="">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#asap"  ng-class="{'Active':schedule_status=='ASAP'}"  role="tab" aria-controls="home" ng-click="asap()" aria-selected="true" >{{trans('messages.store.asap')}}</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" ng-click="schedule()"  ng-class="{'active': schedule_status!='ASAP'}"  role="tab" aria-controls="profile" aria-selected="false" >{{trans('messages.store.schedule_order')}}</a>
                      </li>
                    </ul>
                    <input class="btn btn-primary schedule-btn" type="hidden" ng-model="schedule_status_clone" style="display: none;"></input>
                    <div class="tab-content" id="myTabContent" >
                      <div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab" ng-class="{'show': schedule_status=='ASAP'}">
                        <ul>
                            <li>{{trans('messages.store.asap')}} ({{$store->convert_mintime}} - {{$store->convert_maxtime}}{{trans('messages.store.min')}})</li>
                        </ul>
                      </div>
                      <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab" class="tab-pane fade" role="tabpanel" aria-labelledby="profile-tab" ng-init="schedule_date_value='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time_set='{{session('schedule_data')['time']}}'" >
                             <div class="selectcustom">
                                <select class="liselect" id="schedule_date" ng-model="schedule_date_value">
                                    <option disabled="disabled" value="">{{trans('messages.store_dashboard.select')}}</option>
                                     @foreach(date_data() as $key=>$data)
                                    <option value="{{$key}}" {{ ($key == session('schedule_data')['date']) ? 'selected' : '' }}>{{date('Y', strtotime($data)).', '.trans('messages.driver.'.date('M', strtotime($data))).' '.date('d', strtotime($data))}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="selectcustom">
                                <select class="liselect" id="schedule_time">
                                   <option ng-selected="schedule_time_set==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date_value)">@{{value}}</option>
                                </select>
                            </div>
                           <button class="w-100 btn btn-theme" id="set_time"
                    type="submit">{{trans('messages.store.set_time')}}</button>
                      </div>
                    </div>
                </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>


    <div class="modal fade bd-example-modal-md" id="clear_cart" role="dialog">
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
                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-val="cancel"> @lang('messages.store.cancel') </button>
                    <button type="button" class="btn btn-theme store_popup" data-dismiss="modal" data-val="ok"> @lang('messages.store.new_cart') </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function() {

    let latitude = '{{ $store->user_address->latitude }}'; 
    let longitude = '{{ $store->user_address->longitude }}'; 

    const uluru = { lat: Number(latitude), lng: Number(longitude) };
    // The map, centered at Uluru
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 16,
        center: uluru,
    });
    // The marker, positioned at Uluru
    const marker = new google.maps.Marker({
        position: uluru,
        map: map,
    });

});

function enableMap(){
    var x = document.getElementById("more_info");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}

</script>
@endpush