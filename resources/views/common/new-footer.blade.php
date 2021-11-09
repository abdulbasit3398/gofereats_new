
<footer ng-controller="footer">
    <div class="container"> 
        <div class="ftr_contact">

            <ul>
                @if(site_setting('join_us_facebook'))
                <li><a href="{{site_setting('join_us_facebook')}}"> <i class="icon icon-facebook"></i></a></li>
                @endif
                @if(site_setting('join_us_twitter'))
                <li><a href="{{site_setting('join_us_twitter')}}"> <i class="icon icon-twitter"></i> </a></li>
                @endif
                @if(site_setting('join_us_youtube'))
                <li><a href="{{site_setting('join_us_youtube')}}"> <i class="icon icon-youtube"></i> </a></li>
                @endif
            </ul>
        </div>
        @if(request()->route()->getPrefix()=='')
        <div class="cls_partner">
             <ul>
                <li><a href="{{route('driver.signup')}}"> {{trans('messages.footer.become_a_delivery_partner')}} </a></li>
                <li><a href="{{route('restaurant.signup')}}"> {{trans('messages.footer.become_a_store_partner')}} </a></li>
            </ul>
        </div>
        @endif
        <div class="cls_partner1">
             <ul>
                @foreach($all_static_pages as $page_url)
                    <li><a href="{{route('page',$page_url->url)}}">{{$page_url->name}}</a></li>
                @endforeach
                    <li><a href="{{route('help_page',current_page())}}">{{trans('messages.footer.help')}}</a></li>
            </ul>
        </div>
        <div class="cls_partner2">
             <ul class="cls_partner2ui">
                <li><a href=""><img class="ftrlogo" src="{{ site_setting('1','5') }}"></a></li>
                 <li class="custom_arrow custom_bselet">
                    {!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'cls_lang liselect', 'aria-labelledby' => 'language-selector-label', 'id' => 'language_footer']) !!}

                 </li>
                  @if(get_current_root()!='store')
                 <li class="custom_arrow custom_bselet">
                   
                        <select id="js-currency-select" class="cls_lang liselect" aria-labelledby="'language-selector-label">
                            @foreach($currency_select as $code)
                                <option value="{{$code}}" @if(session('currency') == $code ) selected="selected" @endif >{{$code}}
                                </option>
                            @endforeach
                        </select>
                 </li>
                   @endif

            </ul>
        </div>

    </div>  
</footer>