@extends('new-template')

@section('main')
<main ng-controller="category" ng-init="schedule_status= '{{session('schedule_data') ? trans('messages.store.'.session('schedule_data')['status']): trans('messages.store.ASAP')}}';">

    <div class="cls_bannertop" style="background-image: url({{$service_banner_image}});">        <div class="cls_bantext">
        </div>
    </div>
    <input type="hidden" name="service_type" id="service_type" value="{{session('service_type')}}">
     <div class="cls_deliverypickup">
        <div class="container">
            <div class="cls_whole">
                <div class="cls_wholein"  ng-class="{'active': isActive}">
                    <div class="cls_list">
                        <a href="#storelsit" ng-class="{'active': delivery=='true'}" ng-click="delivery_search()">@lang('messages.search.delivery')</a>  <span>@lang('messages.search.or')</span> <a href="#storelsit" ng-class="{'active': takeaway=='true'}" ng-click="takeaway_search()">@lang('messages.search.pickup')</a>
                        <input type="hidden"   value="@{{$delivery_type}}"  id="delivery_type">
                    </div>
                </div>
                <div class="cls_in" ng-class="{'active': isActive}" ng-init="location = '{{session('location')}}'">
                    <input class="custom_input" ng-click=fullwidth() ng-change="inchange()" id ="feed_location" type="text" name="head_location" placeholder="Enter your Delivery Address" ng-model="location" ng-focus="in_autocomplete()"  ng-blur="location_change()">
                    <i class="icon icon-search-3 left"></i>
                    <i class="icon icon-close-2 right" ng-class="{'remove': remove}" ng-click=removeclick()></i>
                     <input type="hidden" name="address" id="user_address">
                      <span class="d-none text-danger location_error_msg">{{ trans('messages.store.enter_your_delivery_address_to_see') }}</span>
                     <div id="google_results" class="google_results"></div>
                </div>
                <div class="cls_in d-block d-lg-none d-md-none" ng-init="search_key=''">
                    <input ng-model="search_key" autocomplete="off" class="search-input custom_input w-100" type="text" placeholder="{{trans('messages.store.search')}}" id="top_category_search" onfocus="this.placeholder = '{{trans('messages.store.search')}}'" />
                     <i class="icon icon-search-3 left"></i>
                </div>
            </div>
            <!-- <button class="res_btn" data-toggle="modal" data-target="#modalasap">@lang('messages.search.delivery_or_pickup')</button> -->
        </div>
    </div>
    <div class="cls_topcategory" ng-init="categoryies = {{json_encode($categories)}}">
        <div class="container">
            <div class="title">
                <h3>@lang('messages.search.top_category')</h3>
            </div>
            <div class="cls_toplist" >
                <div class="categorylist_slider owl-carousel" >
                    <a href="#storelsit" class="item cls_listin" ng-repeat="category in categoryies" ng-click=categoryStore(category.id)>
                        <div class="text-center">
                            <div class="slide-img" >
                                <img src="@{{category.category_image}}" />
                            </div>
                            <div class="slider-info text-truncate">
                                <h1 class="text-truncate">@{{category.name}}</h1>
                            </div> 
                        </div>
                    </a>
                </div>
            </div>
        </div>
     </div>
    
    <div class="cls_nearby"  id="storelsit">
        <div class="container">
            <div class="title">
                <h3>@lang('messages.search.nearby')</h3>
                <div class="cls_form" ng-init="selected_location='{{session('location')}}'">
                    <input class="custom_input" type="text" name="head_location" placeholder="@{{selected_location}}" style="    pointer-events: none;">
                    <img class="cls_search" src="{{url('/')}}/images/new/pin.svg">
                </div>
            </div>
            <div class="cls_nearbyin">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-12" ng-repeat="store in store_data.category">
                        <a href="newdetails/@{{store.store_id}}" class="cls_nearbyimging" data-id="@{{store.store_id}}">
                            <div class="cls_nearbyimg"> 
                                <span class="cls_lable"
                                ng-if="store.status==0 && store.store_closed!=0" style="position: absolute;bottom: 0;border-radius: 50px; right: 0;">
                                {{ trans('messages.store.currently_unavailable') }}
                                </span>
                                <span class="cls_lable" ng-if="store.store_closed ==0" style="position: absolute;bottom: 0;border-radius: 50px; right: 0;">
                                {{trans('messages.store.closed')}}
                                </span>
                                <img class="cls_search" src="@{{store.banner.original}}">
                                <div class="cls_nearbyimgin" ng-if="store.store_offer-0!='0' && store.status!=0 " >
                                    <h3 class="text-truncate">@{{store.store_offer[0].percentage}}%</h3>
                                    <h4 class="text-truncate">@{{store.store_offer[0].description}}</h4>
                                    <p class="text-truncate">@{{store.store_offer[0].title}}</p>
                                </div>
                            </div>
                            <div class="cls_nearbytext">
                                <h2 class="text-truncate">@{{store.name}}</h2>
                                <!-- <p>Free Delviery</p> -->
                                <div class="cls_reviewin" >
                                    <div class="review" ng-show="store.store_rating > 0">
                                        <span ng-show="store.store_rating > 0">@{{store.store_rating}}</span>
                                        <i class="icon icon-star active" ng-class="{'active': store.store_rating >=1}" ></i> 
                                      <!--   <i class="icon icon-star active" ng-class="{'active': store.store_rating >=2}" ></i> 
                                        <i class="icon icon-star active" ng-class="{'active': store.store_rating >=3}" ></i> 
                                        <i class="icon icon-star inactive" ng-class="{'active': store.store_rating >=4}"></i> 
                                        <i class="icon icon-star inactive" ng-class="{'active': store.store_rating >=5}"></i>  -->
                                       <!--  <span>(@{{store.average_rating }})</span> -->
                                    </div>
                                    <span class="cls_span" ng-show=@{{store.delivery_time}}>@{{store.delivery_time}}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="notfound text-center" ng-show="store_data.count == 0"  style="width: 100%;">
                        <h4>@lang('messages.search.no_result_found')</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection