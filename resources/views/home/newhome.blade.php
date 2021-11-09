@extends('new-template')
@section('main')
<main ng-controller="home_page">
    <div class="cls_homepage">
        <img class="banner" src="{{url('/')}}/images/new/bannerright.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="cls_homeleft">
                        <div class="cls_tille">
                            <h1>@lang('messages.home.order_food_love')</h1>
                        </div>
                        <form class="d-flex location_search" name="location_search">
                            <div class="cls_form">
                                <div class="forminput" ng-init="location='{{session('location')}}'">
                                    <input class="custom_input" type="text" name="head_location" placeholder="Enter your Delivery Address" id="head_location_val" ng-model="location" ng-focus="in_autocomplete()" autocomplete="off">
                                    <img class="cls_search" src="{{url('/')}}/images/new/search.svg">
                                    <button class="cls_remove" id="cls_remove"><i class="icon icon-cancel-button"></i></button>
                                    <input type="hidden" name="address" id="user_address" value="{{session('locate')}}">
                                    <div id="google_results" class="google_results"></div>
                                </div>
                                
                                <div class="submit">
                                <button class="cls_submit find_store"> <img class="" src="{{url('/')}}/images/new/leftarrow.png"> </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if(!Auth::check())
                    <div class="cls_formbtm">
                        <a href="{{route('login')}}">{{trans('messages.home_delivery.sign_in_using_mobile_number')}}</a>
                    </div>
                    @endif
                </div>
                <div class="col-lg-5">
                    <div class="cls_leftdiv">
                        <img class="cls_leftimg" src="{{url('/')}}/images/new/bannerright1.png">
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="cls_category">
        <div class="container">
            <div class="row justify-content-center">
                 <div class="col-lg-12">
                    <div class="large row align-items-center">
                        <div class="cls_catelist col-lg-8">
                            <h3 class="text-truncate">{{$service->service_name}}</h3>
                            <h4 class="text-twotruncate">{{$service->service_description}}</h4>
                        </div>
                        <div class="cls_cateimg col-lg-4 text-center">
                           <img class="cls_leftcimg" src="{{$service->service_image}}">
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cls_portal">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="cls_portallist">
                        <img class="cls_leftpimg" src="{{url('/')}}/images/new/ph.png">
                        <h4>{{trans('messages.home_delivery.find_food_local_store')}}</h4>
                    </div>
                </div>
                 <div class="col-lg-4">
                    <div class="cls_portallist">
                        <img class="cls_leftpimg" src="{{url('/')}}/images/new/store.png">
                        <h4>{{trans('messages.home_delivery.find_food_local_store1')}}</h4>
                    </div>
                </div>
                 <div class="col-lg-4">
                    <div class="cls_portallist">
                        <img class="cls_leftpimg" src="{{url('/')}}/images/new/drive.png">
                        <h4>@lang('messages.home.track_your_food')</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>   
</main>
 <div class="cls_appdownload">
        <div class="container">
            <div class="cls_apptext">
                <h4>@lang('messages.home.simple_way_order')</h4>
            </div>
            <div class="col-lg-12">
                <!-- <h5>Download Now</h5> -->

                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-4 col-12 ">
                        <h4>@lang('messages.home.user')</h4>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('user_apple_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/appstore.png"></a>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('user_android_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/gplay.png"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-12 border-right border-left">
                        <h4>@lang('messages.home.store')</h4>
                       <div class="row">
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('store_apple_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/appstore.png"></a>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('store_android_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/gplay.png"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-12 ">
                        <h4>@lang('messages.home.driver')</h4>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('driver_apple_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/appstore.png"></a>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12 p-0">
                                 <a href="{{site_setting('driver_android_link')}}"><img class="cls_leftpimg" src="{{url('/')}}/images/new/gplay.png"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection