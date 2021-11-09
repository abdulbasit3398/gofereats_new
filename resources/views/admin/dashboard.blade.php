@extends('admin/template')
@section('main')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{route('admin.order')}}">
                    <div class="card card-stats">
                        <div class="card-header card-header-warning card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">add_shopping_cart
                                </i>
                            </div>
                            <p class="card-category">@lang('admin_messages.total_orders')
                            </p>
                            <h3 class="card-title">{{$total_booking}}
                            </h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{route('admin.view_user')}}">
                    <div class="card card-stats">
                        <div class="card-header card-header-rose card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">group
                                </i>
                            </div>
                            <p class="card-category">@lang('admin_messages.total_users')
                            </p>
                            <h3 class="card-title">{{$total_users}}
                            </h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">

                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{route('admin.view_restaurant')}}">
                    <div class="card card-stats">
                        <div class="card-header card-header-success card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">store
                                </i>
                            </div>
                            <p class="card-category">@lang('admin_messages.total_stores')
                            </p>
                            <h3 class="card-title">{{$total_stores}}
                            </h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">

                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="{{route('admin.view_driver')}}">
                    <div class="card card-stats">
                        <div class="card-header card-header-info card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">local_taxi
                                </i>
                            </div>
                            <p class="card-category">@lang('admin_messages.total_driver')
                            </p>
                            <h3 class="card-title">{{$total_drivers}}
                            </h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">

                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <br>


        <div class="row">
            <div class="col-md-12">
            <center>
                {!! $earning_chart->container()  !!}
            </center>

            </div>






        </div>
    </div>
</div>




@endsection
@push('scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
{!! $earning_chart->script() !!}
@endpush
