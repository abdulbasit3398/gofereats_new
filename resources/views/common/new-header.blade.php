<header  id=header_controller  ng-controller="header_controller">
    <div class="container">
        <nav class="navbar justify-content-between align-items-center">
            <div class="d-flex align-items-center">
            <a class="navbar-brand" href="{{url('/')}}"><img class="logo" src="{{site_setting('1','1')}}"></a>
             @if(Route::currentRouteName() == 'feeds')
                 <div class="cls_headsearch" ng-init="search_key=''">
                    <input ng-model="search_key" autocomplete="off" class="search-input w-100" type="text" placeholder="{{trans('messages.store.search')}}" id="top_category_search" onfocus="this.placeholder = '{{trans('messages.store.search')}}'" />
                     <img class="cls_search" src="{{url('/')}}/images/new/search.svg">
                </div>
            @endif
            </div>
            <input type="hidden" id="orderdata" value="{{json_encode(session('order_data'))}}">
            <nav class="navbar navbar-expand-md px-0">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <img class="menu" src="{{url('/')}}/images/new/menu.svg">
                </button>
               <input type="hidden" value="{{total_count_card()}}" id="count_cart_item" >
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    @if(!Auth::check())
                        <ul class="navbar-nav mr-auto align-items-center">
                             <li class="nav-item">
                                 <a class="cls_btna"  href="{{route('login')}}">{{trans('messages.profile.sign_in')}}</a>
                             </li>
                             <li class="nav-item">
                                 <a href="{{route('signup')}}" class="btn cls_btn my-2 ml-lg-4 ml-md-4  my-sm-0" type="submit">{{trans('messages.profile.register')}}</a>
                             </li>
                        </ul>
                    @endif
                    @if(Auth::check())
                        <ul class="navbar-nav mr-auto align-items-center">
                             <li class="nav-item">
                                 <a class="cls_btna  ml-lg-4 ml-md-4"  href="{{route('profile')}}">{{trans('messages.profile.profile')}}</a>
                             </li>
                             <li class="nav-item">
                                 <a class="cls_btna  ml-lg-4 ml-md-4"  href="{{route('orders')}}">{{trans('messages.profile.orders')}}</a>
                             </li>
                             <li class="nav-item">
                                 <a class="cls_btna  ml-lg-4 ml-md-4"  href="{{route('user_payment')}}">{{trans('messages.profile.payment')}}</a>
                             </li>
                            <li class="nav-item cart d-none" >
                                 <a class="cls_btna  ml-lg-4 ml-md-4"  href="{{route('checkout')}}" id="card_page"><i class="icon icon-shopping-bag-1"></i> Cart <span class='d-none' id="count_card" ng-cloak>{{total_count_card()}}</span></a>
                            </li>
                            <li class="nav-item">
                                 <a class="btn cls_btn cart_value ml-lg-4 ml-md-4"  href="{{route('logout')}}">{{trans('messages.profile.log_out')}}</a>
                             </li>
                        </ul>

                    @endif
               </nav>
        </div>
        <div class="flash-container">
            @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class') }} text-center" role="alert">
              {{ Session::get('message') }}  <a href="#" class="alert-close" data-dismiss="alert">&times;</a>
            </div>
            @endif
            @if(isset($paypal_error))
              <div class="alert alert-danger text-center" style="display: none" id="paypal_error" role="alert">
                 {{$paypal_error}}   <a href="#" class="alert-close" data-dismiss="alert">&times;</a> 
              </div>
            @endif
        </div>
</header>