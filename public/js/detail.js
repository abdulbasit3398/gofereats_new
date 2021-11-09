app.controller('newdetails', ['$scope', '$http', '$timeout','$q', function ($scope, $http, $timeout,$q) {
     
   $(document).on("click", "#schedule_order", function() {
      if($scope.schedule_status == 'ASAP')
      {
          $('#profile').removeClass('d-none');
          $('#profile').removeClass('active');
          $('#profile').removeClass('show');
          $('#home').addClass('active'); 

      }else if($scope.schedule_status == 'Schedule')
      {
        $('#profile').addClass('show'); 
        $('#profile').addClass('active'); 
        $('#profile').removeClass('d-none');
        $('#home').removeClass('active');
      }
      $('#modalasap').modal();
  }); 

    $scope.updateCount = function(modifier_item,index) {
      console.log(modifier_item.is_selected);
      modifier_item.item_count = (modifier_item.is_selected) ? 1 : 0;
      console.log(modifier_item);
      setTimeout( () => $scope.updateModifierItems(),10);
    };

    $scope.asap = function($event){
          $('#profile').addClass('d-none');
          var url = getUrls('schedule_store');
          date ='';
          time = '';
          $http.post(url,{
            status   : 'ASAP',
            date     : date,
            time     : time,
          }).then(function(response){
            $scope.asap_schedule_status = response;
            $('#home').addClass('active');
            $('#schedule_date').removeClass('active');
            location.reload();
          });
      }

      $scope.schedule = function($event){
        $('#profile').removeClass('d-none');
        $('#home').removeClass('active');
        var url = getUrls('schedule_store');
        date ='';
        time = '';
        $http.post(url,{
          status   : 'ASAP',
          date     : date,
          time     : time,
        }).then(function(response){
          $scope.schedule_data = response.data.schedule_data;
          $scope.status = response.data.schedule_data.status;
          $('#delivery_time').val(response.data.schedule_data.date_time);
          $('#order_type').val(0);
          if(response.data.schedule_data.date_time)
            $('#order_type').val(1);
          $('#date1').css('display','none');
          $('#time1').css('display','none');
        });
      }  
        $scope.order_data =[];
        $scope.add_notes='';
        $scope.remove = false;
        $scope.category_changed = false;
        $scope.before_page = 0;
        // $scope.checked = false;
        // $scope.more = false;

    $scope.closePopup = function(){
        setTimeout(function(){
            $scope.checked = false;
            $scope.$apply();
        },1000);
    }

    $scope.fullwidth = function(){
        $scope.isActive = true;
    }

    $scope.inchange = function(){
        if ($scope.getvalue.length >= 3) {
             $scope.remove = true;
        }
    }

    $scope.removeclick = function(vals){
         $scope.getvalue = null;
    }
       
    $(window).scroll(function() {
          var body = document.body,html = document.documentElement;
          var height = Math.max( body.scrollHeight, body.offsetHeight, 
                     html.clientHeight, html.scrollHeight, html.offsetHeight );
          if($(this).scrollTop() + $(this).innerHeight() + 800 >= height) {
              $scope.menuItem($scope.page);
          }
    });

    var isSending = false;
    $('#item_search').on('keydown', function (e) {
          var page=0;
          if(e.keyCode == 13) {
            $scope.menu_item = [];
            $scope.menuItem();
          }
    });

    $scope.menuItem = function(page=1) {
      if(isSending) 
        return false;
      category_id = $scope.category_changed ? $scope.category_id:'';
      search_key = $scope.search_key ? $scope.search_key : '';
      isSending = true;
      url = getUrls('get_menu_items');
      $http.post(url,{
        page : page,
        store_id : $scope.store_id,
        category_id : category_id,
        key : search_key
      }).then(function(response){
        $('.cls_slices').addClass('dot-loading');
        isSending = false;
        $scope.page = page + 1;
        var bar = new Promise((resolve, reject) => {
            if($scope.menu_item.length!=0){
              angular.forEach(response.data.data, function (value, key) {
                  if (key in $scope.menu_item) {
                      angular.forEach(value, function (item, keys) { 
                        if (!(keys in $scope.menu_item[key])) {
                          $scope.menu_item[key].push(item); 
                        }
                      });  
                  }
                  else{
                   
                    angular.extend($scope.menu_item,{ [key]: response.data.data[key] });
                  }
                    resolve();
                });
            }
            else
              $scope.menu_item = response.data.data;
          });
          bar.then(() => {
              if($scope.category_changed && page == 1){
                $('html, body').animate({ scrollTop: $('#category_'+category_id).offset().top-150 }, 500);
              } 
            if($scope.category_changed && $scope.page > response.data.total_page){
              $scope.page = $scope.before_page+1
              $scope.category_changed = false;
            }
          });
        $('.cls_slices').removeClass('dot-loading');  
      });
    } 

    $(document).on('click', '.pro-item-detail', function () {
      $scope.menu_item_price = 0;
      var item_id = $(this).attr('data-id');
      var price1 = $(this).attr('data-price');
      $scope.menu_item_price = price1;
      $scope.item_count = 1;
      $('.count_item').text($scope.item_count);
      var url_item = getUrls('item_details');
      $http.post(url_item,{
        item_id :  item_id,
      }).then(function(response) {
        $scope.add_notes = '';
        $scope.menu_item_add = response.data.menu_item;
        setTimeout( () => $scope.updateModifierItems(),10);
        $('body').addClass('non-scroll');
         $('#exampleModalCenter').modal('show');
        });
     });

    $scope.updateRadioCount = function(index,modifier_item_id) {
      menu_item_modifier = $scope.menu_item_add.menu_item_modifier[index];
      $.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
        menu_item_modifier_item.item_count = 0;
        menu_item_modifier_item.is_selected = false;
        if(menu_item_modifier_item.id == modifier_item_id){
          menu_item_modifier_item.item_count = 1;
          menu_item_modifier_item.is_selected = true;
        }
    });
    setTimeout( () => $scope.updateModifierItems(),10);
  };



    $scope.updateModifierItems = function() {
        var cart_disabled = false;
        angular.forEach($scope.menu_item_add.menu_item_modifier, function(menu_item_modifier,index) {
            var item_count = 0;
            $.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
                if(menu_item_modifier.is_multiple != 1) {
                    item_count += (menu_item_modifier_item.is_selected) ? 1 : 0;
                }
                else {
                    item_count += menu_item_modifier_item.item_count;
                }
                if($scope.menu_item_add.menu_item_modifier[index].is_selected == false && menu_item_modifier_item.is_selected) {
                    $scope.menu_item_add.menu_item_modifier[index].is_selected = true;
                }
                if($scope.menu_item_add.menu_item_modifier[index].is_multiple == 0 && $scope.menu_item_add.menu_item_modifier[index].is_required == 1  && $scope.menu_item_add.menu_item_modifier[index].count_type == 0 && ($scope.menu_item_add.menu_item_modifier[index].max_count == 0)){
                    $scope.menu_item_add.menu_item_modifier[index].is_selected = true;
                    $scope.menu_item_add.menu_item_modifier[index].item_count = 1;
                    menu_item_modifier_item.is_selected = true;
                    menu_item_modifier_item.item_count = 1;
                    item_count = 1;
                }
            });

            menu_item_modifier.isMaxSelected = false;
            if(menu_item_modifier.max_count == item_count) {
                menu_item_modifier.isMaxSelected = true;
                $.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
                    menu_item_modifier_item.isDisabled = true;
                });
            }
            if(menu_item_modifier.is_required == 1) {
                if(menu_item_modifier.count_type == 0 && item_count < menu_item_modifier.max_count) {
                    cart_disabled = true;
                }
                if(menu_item_modifier.count_type == 1) {
                    if(item_count < menu_item_modifier.min_count) {
                        cart_disabled = true;
                    }
                }
            }
        });
        $scope.cartDisabled = cart_disabled;
        $scope.updateCartPrice();
        $scope.applyScope();
    };

    $scope.updateCartPrice = function() {
        var modifer_price = 0;
        var menu_price = $scope.menu_item_add.offer_price > 0 ? $scope.menu_item_add.offer_price : $scope.menu_item_add.price;
        menu_price = menu_price - 0;
        angular.forEach($scope.menu_item_add.menu_item_modifier, function(menu_item_modifier) {
            $.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
                var item_count = 0;
                if(menu_item_modifier.is_multiple != 1) {

                    item_count += (menu_item_modifier_item.is_selected) ? 1 : 0;
                }
                else {
                    item_count += menu_item_modifier_item.item_count
                }
                count = (item_count * $scope.item_count) ; 
                modifer_price += (count * menu_item_modifier_item.price);
            });
        });
        var menu_item_price = ($scope.item_count * menu_price ) + modifer_price;
        $scope.menu_item_price = menu_item_price.toFixed(2);
        $('#menu_item_price').text($scope.menu_item_price);
    };

    $scope.updateItemCount = function(index,action=true) {
      //true = add value false = remove
      // var modifiers_itemcount = $scope.order_data.items[index].modifier.item_count;
      var original_itemcount = $scope.order_data.items[index].item_count ;
      let item_count = $scope.order_data.items[index].item_count+(action ? 1:-1);
      $scope.order_data.items[index].item_count = item_count;
      if(item_count<1)
        $scope.order_data.items.splice(index, 1);
    };


   $scope.applyScope = function() {
        if(!$scope.$$phase) {
            $scope.$apply();
        }
    }; 

    $('.store_popup').click(function(){
      var url = getUrls('session_clear_data');
      $http.post(url,{}).then(function(response) {
        $scope.order_data ='';
        $scope.other_store = 'no';
        $scope.order_store_session();
      });
    });


    $scope.order_store_session= function() {
      var location_val =$('#feed_location').val() ;
      if($scope.other_store == 'yes'){
          // $scope.remove_sesion_data(); 
          $('#clear_cart').modal();
          return false;
      }
      $scope.item_count = $scope.item_count;
      var store_id = $scope.store_id;
      $scope.item_notes = $scope.add_notes;
      var index_id = $(this).attr('data-remove');
      var menu_item_id = $scope.menu_item_add.id; 
      var add_cart = getUrls('add_cart');
      $http.post(add_cart,{
        store_id         : store_id,
        menu_data        : $scope.menu_item_add,
        item_count       : $scope.item_count,
        item_notes       : $scope.item_notes,
        item_price       : $scope.price,
        individual_price : $scope.individual_price,
      }).then(function(response) {
        if(response.data.status_code==0){
              window.location.href = getUrls('feeds');
        }else{
          $scope.order_data = response.data.cart_detail;
          $scope.cart_count = response.data.cart_detail.total_item_count;
          $('#count_card').text($scope.order_data ?  $scope.cart_count :'');
          $('.cart').removeClass('d-none');
          $('#count_card').removeClass('d-none');
          console.log( $scope.cart_count );
          $scope.applyScope();
          $('#exampleModalCenter').modal('hide');
        }
      });
    };  

    $scope.remove_sesion_data = function(index) {
      $('#calculation_form').addClass('loading');
      var remove_url = getUrls('orders_remove');
      // $scope.order_data.items.splice(index, 1);
      var data = $scope.order_data;
      $http.post(remove_url,{
        order_data    : data,
        delivery_type:$scope.delivery_type,
      }).then(function(response){
        $scope.order_data = response.data.order_data;
        $('#calculation_form').removeClass('loading');
          if($scope.order_data==''){
            $('#count_card').text('');
            $('.icon-shopping-bag-1').removeClass('active');
            $('#checkout').attr('disabled','disabled');
            if($('#check_detail_page').val()!=1){
              var url = getUrls('feeds');
              window.location.href = url ;
            }
          }
        $('#calculation_form').removeClass('loading');
      });
    };

    $('#checkout').click(function () {
      if($scope.order_data=='') {
        return false;
      }
      else {
        var url = getUrls('checkout');
        window.location.href = url ;
      }
  });

  $(document).on('click','.value-changer',function() {
    if($(this).attr('data-val')=='add') {
      if($scope.item_count < 20) {
        $scope.item_count++;
      }
    }

    if($(this).attr('data-val')=='remove') {
      if($scope.item_count > 1) {
        $scope.item_count--;
      }
    }
    $scope.updateCartPrice();
    $scope.applyScope();
  });    



  $scope.updateModifierItem = function(modifier_item,type) {
    if(modifier_item.item_count <= 0 && type == 'decrease') {
      return false;
    }
    if(type == 'decrease') {
      modifier_item.item_count--;
    }
    else {
      modifier_item.item_count++;
    }
    modifier_item.is_selected = (modifier_item.item_count > 0);
    setTimeout( () => $scope.updateModifierItems(),10);
  };

  $(document).on('click','.value-changer_checkout',function() {
    if($(this).attr('data-val')=='add') {
      if($scope.item_count < 20) {
        $scope.item_count++;
        var change_url = getUrls('orders_change');
        $http.post(change_url,{
        order_data    : $scope.order_data,
        order_item_id : order_item_id,
        delivery_type : $scope.delivery_type,
        isWallet : $scope.pWallet,
      }).then(function(response){
        $scope.order_data = response.data.order_data;
        $('#calculation_form').removeClass('loading');
        $('#calculation_form').removeClass('loading');
      });
      }
    }
    if($(this).attr('data-val')=='remove') {
      if($scope.item_count > 1) {
        $scope.item_count--;
      }
    }
  });    

  $scope.order_store_changes = async function(index,valu,order_item_id) {
    $('.cartlist').addClass('dot-loading');
    var total_item_count = $scope.order_data.length ? $scope.order_data.items[index].item_count:0;
    await $scope.updateItemCount(index,valu=='add');
      var change_url = getUrls('orders_change');
      $http.post(change_url,{
        order_data    : $scope.order_data,
        order_item_id : order_item_id,
        delivery_type : $scope.delivery_type,
        isWallet : $scope.pWallet,
      }).then(function(response){
    $('.cartlist').removeClass('dot-loading');
        $scope.order_data = response.data.order_data;
        $scope.cart_count = response.data.order_data.total_item_count;
        $('#count_card').text( $scope.cart_count );
      });
   
    if($scope.order_data)
     $('#count_card').text($scope.order_data ?  $scope.cart_count :'');
    else{
      $('.cart').addClass('d-none');
    }
  };

 $('#category_select').change(function(){
    $scope.category_changed = true
    $scope.category_id = $(this).val();
    $scope.before_page = $scope.page;
    $scope.page = 1;
    $scope.menuItem();
  });

  $('#set_time').on('click', function() {
      var status = 'Schedule';
      var date = $('#schedule_date').val();
      var time = $('#schedule_time').val();   
      var url = getUrls('schedule_store');
      $http.post(url,{
        status   : status,
        date     : date,
        time     : time,
      }).then(function(response){
        if(response.data.schedule_data.status=='Schedule'){
          $scope.status = response.data.schedule_data.status;
          $scope.date= response.data.schedule_data.date;
          $scope.time= response.data.schedule_data.time;
          $('#delivery_time').val(response.data.schedule_data.date_time);
          $('#order_type').val(0);
          if(response.data.schedule_data.date_time)
            $('#order_type').val(1);
          var data2 = $scope.date+' '+$scope.time;
          $('#possible').css('display','none');
          $('#date1').text($scope.date);
          $('#time1').text($scope.time);
        }
        location.reload();
      });
  });
    
  app.filter('checkTimeInDay', ["$filter", function($filter) {
    return function(time, date) {
      var date1 = new Date();
      var current_date = $filter('date')(date1, "yyyy-MM-dd");
      date1.setHours(date1.getHours() + 1);
      var current_time = $filter('date')(date1, "HH:mm:ss");
      if(current_date == date) {
        if(time > current_time) {
          return true;
        }
        return false;
      }
      return true;
    };
}])

  
}]);
