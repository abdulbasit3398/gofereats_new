<body class="" ng-app="App">
    <div class="wrapper">
        <div class="sidebar" data-color="rose" data-background-color="black" data-image="{{asset('admin_assets/img/sidebar-1.jpg')}}">
            <div class="logo">
                <a href="{{route('admin.dashboard')}}" class="simple-text logo-normal" title="{{site_setting('site_name')}}">
                   <img src="{{site_setting('1','1')}}">
               </a>
           </div>
           <div class="sidebar-wrapper">
            <div class="user">
                <div class="photo">
                    <img src="{{asset('admin_assets/img/faces/avatar.jpg')}}" />
                </div>
                <div class="user-info">
                    <a  href="{{route('admin.update_admin',['id' => auth()->guard('admin')->user()->id])}}" class="username">
                        <span>
                            {{auth()->guard('admin')->user()->username}}
                        </span>
                    </a>
                    <div class="collapse d-none" id="collapseExample">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="sidebar-mini"> MP
                                    </span>
                                    <span class="sidebar-normal"> My Profile
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="sidebar-mini"> EP
                                    </span>
                                    <span class="sidebar-normal"> Edit Profile
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="sidebar-mini"> S
                                    </span>
                                    <span class="sidebar-normal"> Settings
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav">
                @foreach(side_navigation() as $navigation_value)
                @if($navigation_value['has_permission'])
                    <li class="nav-item {{($navigation_value['active']) ? 'active' : ''}}">
                        <a class="nav-link" href="{{$navigation_value['route']}}">
                            <i class="material-icons">{{$navigation_value['icon']}}
                            </i>
                            <p> 
                            {{$navigation_value['name']}}
                            </p>
                        </a>
                    </li>
                @endif
                @endforeach
                 <li class="nav-item cls_dropcaret" id="dropdown">
                        <a data-toggle="collapse" class="nav-link" href="#dropdown-lvl1">
                            <i class="fa fa-language"></i> Content Management
                          <span class="caret"></span>
                             </a>
                        <!-- Dropdown level 1 -->
                        <div id="dropdown-lvl1" class="panel-collapse collapse">
                            <div class="panel-body">
                                <ul class="nav navbar-nav">

                                     <!-- <li class="nav-item"><a href="{{ url('admin/lang')}}" class="nav-link"> <i class="material-icons">web</i>Web</a></li> -->
                                    
                                      <li class="nav-item" id="dropdown">
                                        <a data-toggle="collapse" class="nav-link"  href="#dropdown-lvl2">
                                            <i class="material-icons">web</i>  Web  <span class="caret"></span>
                                        </a>
                                        <div id="dropdown-lvl2" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <ul class="nav navbar-nav">
                                                    <li><a href="{{ url('admin/lang') }}" class="nav-link"><i class="material-icons">language</i> Web Contents</a></li>
                                                    <li><a href="{{ url('admin/validation_message') }}" class="nav-link"><i class="material-icons">check</i> Validation Message</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="nav-item" id="dropdown">
                                        <a data-toggle="collapse" class="nav-link"  href="#dropdown-lvl3">
                                            <i class="fa fa-mobile"></i>  Mobile  <span class="caret"></span>
                                        </a>
                                        <div id="dropdown-lvl3" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <ul class="nav navbar-nav">
                                                      <li><a href="{{ url('admin/api_user_lang') }}" class="nav-link"><i class="material-icons">account_circle</i> User</a></li>
                                                    <li><a href="{{ url('admin/api_restaurant_lang') }}" class="nav-link"><i class="material-icons">store</i> Restaurant</a></li>
                                                    <li><a href="{{ url('admin/api_driver_lang') }}" class="nav-link"><i class="material-icons">drive_eta</i> Driver</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
            </ul>
        </div>
    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent  navbar-absolute fixed-top">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <div class="navbar-minimize">
                        <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                            <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert
                            </i>
                            <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list
                            </i>
                        </button>
                    </div>
                    <a class="navbar-brand" href="#pablo">{{@$form_name}}
                    </a>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="sr-only">Toggle navigation
                    </span>
                    <span class="navbar-toggler-icon icon-bar">
                    </span>
                    <span class="navbar-toggler-icon icon-bar">
                    </span>
                    <span class="navbar-toggler-icon icon-bar">
                    </span>
                </button>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item d-none">
                            <a class="nav-link" href="#pablo">
                                <i class="material-icons">notifications
                                </i>
                                <span class="notification">5
                                </span>
                                <p>
                                    <span class="d-lg-none d-md-block">Account
                                    </span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#pablo" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons">person
                                </i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                <a class="dropdown-item" href="{{route('admin.logout')}}">
                                Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flash-container">
                @if(Session::has('message'))
                <div class="alert {{ Session::get('alert-class') }} text-center" role="alert">
                    <a href="#" class="alert-close" data-dismiss="alert">&times;</a> {{ Session::get('message') }}
                </div>
                @endif
            </div>
        </nav>
        <!-- End Navbar -->

