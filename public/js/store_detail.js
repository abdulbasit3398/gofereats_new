 app.controller('stores_detail', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
	$('.detail-popup .icon-close-2').click(function () {
		$scope.item_count = 1;
		$('.detail-popup').removeClass('active');
		$('body').removeClass('non-scroll');
	});

	$('.restuarant-popup .icon-close-2').click(function () {
		$('.restuarant-popup').removeClass('active');
		$('body').removeClass('non-scroll');
	});

	$scope.show_promo = function(){
		$('.add-promo').toggle();
		$('.promo_btn_show').toggle();
	};

	$scope.show_tips = function(){
		$('.add-tips').show();
		$('.tips_btn_show').toggle();
	};

	$('#tips').on('input', function () {
        this.value = this.value.match(/^\d+\.?\d{0,2}/);
    })

	var current_url = window.location.href.split('?')[0];
	var last_part = current_url.substr(current_url.lastIndexOf('/'));
	var last_part1 = current_url.substr(current_url.lastIndexOf('/') + 1);
	if(last_part1 == 'checkout')
	{
		$(document).ready(function(){ 
			$scope.update_delivery(true);
		});
	}

	if($scope.wallet == true)
	{
		$scope.pWallet=1;
	}
	else 
	{
		$scope.pWallet =0;
	}

	$scope.update_delivery = function(first=''){
			var deliverytype = $("#delivery_type_radio").val();
			const $firstCb = $("input[name=delivery_type][value=" + deliverytype + "]").attr('checked', 'checked');
			$scope.delivery_type  = $("input[name='delivery_type']:checked").val(); 
			if(first){
				if($scope.delivery_type == 'delivery')
					$scope.delivery_type_delivery();			
				else
					$scope.delivery_type_takeway();
			}
	}	

	$scope.delivery_type_takeway = function() {
		$('.driver_tips').hide();
			$scope.order_data.tips = 0; 
			var order_id = $('#order_data_id').val();
			var promo_id = $('#promo_code_id').val();
			var delivery_type = 'takeaway';
			$("input#suite").hide();
			$("input#delivery_note").hide();
			$('#calculation_form').addClass('loading');
			var remove_driver_tips = getUrls('remove_driver_tips');
			$http.post(remove_driver_tips,{
				tips : $scope.order_data.tips,
				order_id : order_id,
				delivery_type:delivery_type,
				isWallet:$scope.pWallet,
				promo_id : promo_id,
			}).then(function(response){
			$("#delivery_type_radio").val(response.data.order_detail_data.delivery_type);
			$scope.update_delivery();
			if(response.data.status==0)
			{
				$('.promo_code_error').text(response.data.message);
				return false
			}
			if($scope.payment_method == 2)
			{
				
				var url = getUrls('paypal_currency_conversion');
					$http.post(url).then(function(response) {
					$('.place_order_change').removeClass('loading');
					if(response.data.success=='true') {
						console.log(response.data.amount);
						$scope.amount = response.data.amount;
						$scope.currency = response.data.currency;
						$scope.applyScope();
						var remove_driver_tips = getUrls('remove_driver_tips');
						$('#paypal-button').removeClass('d-none');
						$('#place_order').addClass('d-none');
					}
				});
			}
			$('#calculation_form').removeClass('loading');
			$scope.order_data = response.data.order_detail_data;
			});
	};

	$scope.delivery_type_delivery = function() {
		$('.driver_tips').show();
			var tips = 0;
			var order_id = $('#order_data_id').val();
			var promo_id = $('#promo_code_id').val();
			var delivery_type = 'delivery';
			$("input#suite").show();
			$("input#delivery_note").show();
			$('#calculation_form').addClass('loading');
			var remove_driver_tips = getUrls('remove_driver_tips');
			$http.post(remove_driver_tips,{
				tips : tips,
				order_id : order_id,
				delivery_type:delivery_type,
				isWallet :$scope.pWallet,
				promo_id : promo_id
			}).then(function(response){
			$('.promo_loading').removeClass('loading');
			if(response.data.status==0)
			{
				$('.promo_code_error').text(response.data.message);
				return false
			}
			$('.tips_code_success').removeClass('d-none');
			if($scope.payment_method == 2)
			{
				var url = getUrls('paypal_currency_conversion');
					$http.post(url).then(function(response) {
					$('.place_order_change').removeClass('loading');
					if(response.data.success=='true') {
						console.log(response.data.amount);
						$scope.amount = response.data.amount;
						$scope.currency = response.data.currency;
						$scope.applyScope();
						var remove_driver_tips = getUrls('remove_driver_tips');
						$('#paypal-button').removeClass('d-none');
						$('#place_order').addClass('d-none');
					}
				});
			}
			$('#calculation_form').removeClass('loading');
			$scope.order_data = response.data.order_detail_data;
			$scope.order_data.total_price = response.data.order_detail_data.total_price;	
			});	
	};

	$("input[name='delivery_type']").click(function() {
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val(); 
		if($scope.delivery_type == 'delivery')
		{
			$scope.delivery_type_delivery();			
		}
		else
		{
			$scope.delivery_type_takeway();	
		}
    }); 
	

	$scope.menu_item = '';
	$scope.item_count = 1;
	$scope.index_id='';
	$scope.menu_closed = 0;
	$scope.menu_closed_status =0;
	$(document).on('change','#menu_changes',function() {
		var menu_id = $('#menu_changes').val();
		var url_category = getUrls('category_details');
		$('.detail-products').addClass('loading');
		$http.post(url_category,{
			menu_id : menu_id,
			store_id : $scope.store_id
		}).then(function(response){
			$('.detail-products').removeClass('loading');
			$scope.menu_closed = response.data.current_menu.menu_closed;
			console.log($scope.menu_closed);
			$scope.menu_closed_status = response.data.current_menu.menu_closed_status;
			$scope.menu_category = response.data.menu_category;
			$scope.store_category = response.data.store_menu;
			$scope.applyScope();
			$scope.getCategoryItem();
			setTimeout( () => $("li:first-child .cls_menuaclick").trigger("click"),10);
		});
	});


	$(document).ready(function(){
		$scope.getCategoryItem();
	});

	$scope.getCategoryItem = function()
	{
		$scope.store_category.forEach(async function(category,key){
			if(category.total_item > 1){
				for (var i=2;  i <= category.total_item; i++) {
					await $http.post(getUrl('get_category_item'),
						{ category_id : category.id, page : i}
					).then(function(response){
						if(response.data.menu_item){
							console.log($scope.store_category[key].name);
							$scope.store_category[key].menu_item = [...$scope.store_category[key].menu_item, ...response.data.menu_item];
						}
					});
				}
			}
		});
	};

	$scope.apply_promo = function() {
		$('.promo_code_error').addClass('text-danger');
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val(); 
		var order_id = $('#order_data_id').val();
		var promo_code = $('.promo_code_val').val();
		if(promo_code=='') {
			$('.promo_code_error').removeClass('d-none');
			return false
		}
		$('.promo_code_error').addClass('d-none');
		$('.promo_loading').addClass('loading');
		var add_promo_code_data = getUrls('add_promo_code_order');
		$http.post(add_promo_code_data,{
			code : promo_code,
			store_id : $('#store_id').val(),
			delivery_type:$scope.delivery_type,
			isWallet : $scope.pWallet,
			order_id :order_id
		}).then(function(response){
			$('.promo_loading').removeClass('loading');
			if(response.data.status==0)
			{
				$('.promo_code_error').text(response.data.message);
				return false
			}
			$('.promo_code_success').removeClass('d-none');
			$('.promo_code_success').text(response.data.message).delay(1000).fadeOut();
			$scope.order_data = response.data.order_detail_data;
			$('.promo_remove').removeClass('d-none');
		});
		return false;
	};

	$(document).on('click', '#scheduleasap', function () {
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
				$('#delivery_time').val('');
				$('#order_type').val(0);
				$('#date1').css('display','none');
				$('#time1').css('display','none');
			});
	});

	$('#set_time1').click(function(){
		$('#schedule-modal').modal('hide');
		if($('#count_card').text() > 0)
			$('#schedule_modal1').modal();
		else
			$('.schedule_modal1').trigger("click");
	});



	$('.schedule_modal1,#set_time1').click(function(){
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
				var data2 = $scope.date+' '+$scope.time;
				$('#delivery_time').val(response.data.schedule_data.date_time);
				$('#order_type').val(1);
				$('#possible').css('display','none');
				$('#date1').text($scope.date);
				$('#time1').text($scope.time);
			}
			$('.icon-close-2').trigger('click');
			return  false;

		});
	});


	$scope.apply_tips = function() {
		$('.tips_code_error').addClass('text-danger');	
		$('.tips_code_success').addClass('d-none');	
		var tips = $('.tips_code_val').val();
		var order_id = $('#order_data_id').val();
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val();	
		if(tips =='' || tips <= 0) {
			$('.tips_code_error').removeClass('d-none');
			$('.tips_code_error').text(Lang.get('js_messages.store.please_enter_value_grt_than_0'));
			return false
		}
		$('.tips_code_error').addClass('d-none');
		$('.promo_loading').addClass('loading');
		if($scope.wallet == true)
		{
			$scope.pWallet=1;
		}
		else 
		{
			$scope.pWallet =0;
		}		
		var add_driver_tips = getUrls('add_driver_tips');
		$http.post(add_driver_tips,{
			tips : tips,
			order_id : order_id,
			isWallet : $scope.pWallet,
			delivery_type : $scope.delivery_type ,
		}).then(function(response){
			$('.tips_code_error').addClass('d-none');
			$('.promo_loading').removeClass('loading');
			if(response.data.status == 0)
			{
				$('.tips_code_error').text(response.data.message);
				return false
			}
			$('.tips_code_success').removeClass('d-none');
			$('.tips_code_success').text(response.data.message).delay(1000).fadeOut();
			$scope.order_data = response.data.order_detail_data;
		});
	};



	$scope.remove_promo = function() {
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val();	
		$('.promo_code_error').addClass('text-danger');
		var order_id = $('#order_data_id').val();
		var promo_id = $('#promo_code_id').val();
		if(promo_id=='') {
			$('.promo_code_error').removeClass('d-none');
			return false
		}
		$('.promo_remove').addClass('d-none');
		$('.promo_loading').addClass('loading');
		if($scope.wallet == true)
		{
			$scope.pWallet=1;
		}
		else 
		{
			$scope.pWallet =0;
		}
		var remove_promo_code_data = getUrls('remove_promo_code');
		$http.post(remove_promo_code_data,{
				id : promo_id,
				store_id : $('#store_id').val(),
				delivery_type:$scope.delivery_type,
				isWallet : $scope.pWallet,
				order_id : order_id,
		}).then(function(response){
			 	$('.promo_code_error').removeClass('d-none');
				$('.promo_loading').removeClass('loading');
					if(response.data.status==0)
					{
						$('.promo_code_error').text(response.data.message);
						return false
					}
					$('.promo_code_success').removeClass('d-none');
					$('.promo_code_success').text(response.data.message);
				$scope.order_data = response.data.order_detail_data;
				// $scope.order_data.delivery_fee = 0;
		});
		return false;
	};

	$scope.remove_tips = function() {
		$('#tips').val('') ;
		var order_id = $('#order_data_id').val();
		var tips = 0;
		var url = getUrls('remove_driver_tips');
		if($scope.wallet == true)
		{
			$scope.pWallet=1;
		}
		else 
		{
			$scope.pWallet =0;
		}
		$http.post(url,{
			tips : tips,
			order_id : order_id,
			isWallet : $scope.pWallet ,
		}).then(function(response){
		$('.promo_loading').removeClass('loading');
		if(response.data.status == 0)
		{
			$('.promo_code_error').text(response.data.message);
			return false
		}
		$(".tips_code_success").removeAttr("style");
		$('.tips_code_success').text(response.data.message).delay(1000).fadeOut();
		$scope.order_data = response.data.order_detail_data;
		});
	};

	$(document).on('click', '.pro-item-detail', function () {
		if($('#location_search,#location_search_new').val()=='') {
			$('.location_error_msg').removeClass('d-none');
			return false;
		}
		$('.location_error_msg').addClass('d-none');
		var item_id = $(this).attr('data-id');
		var price1 = $(this).attr('data-price');
		$scope.item_count = 1;
		$('.count_item').text($scope.item_count);
		$('#menu_item_price').text(price1);

		var url_item = getUrls('item_details');
		$http.post(url_item,{
			item_id :  item_id,
		}).then(function(response) {
			$scope.add_notes = '';
			$scope.menu_item = response.data.menu_item;
			setTimeout( () => $scope.updateModifierItems(),10);
			$('body').addClass('non-scroll');
			$('.detail-popup').addClass('active');
		});
	});

	// Common function to check and apply Scope value
    $scope.applyScope = function() {
        if(!$scope.$$phase) {
            $scope.$apply();
        }
    };

	// Check input is valid or not
    $scope.checkInValidInput = function(value) {
        return (value == undefined || value == 0 || value == '');
    };

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

	$scope.updateCount = function(modifier_item) {
		modifier_item.item_count = (modifier_item.is_selected) ? 1 : 0;
		setTimeout( () => $scope.updateModifierItems(),10);
	};

	$scope.updateRadioCount = function(index,modifier_item_id) {
		menu_item_modifier = $scope.menu_item.menu_item_modifier[index];
		$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
			menu_item_modifier_item.item_count = 0;
			menu_item_modifier_item.is_selected = false;
			if(menu_item_modifier_item.id == modifier_item_id) {
				menu_item_modifier_item.item_count = 1;
				menu_item_modifier_item.is_selected = true;
			}
		});

		setTimeout( () => $scope.updateModifierItems(),10);
	};

	$scope.updateModifierItems = function() {
		var cart_disabled = false;
		angular.forEach($scope.menu_item.menu_item_modifier, function(menu_item_modifier,index) {
			var item_count = 0;
			$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
				if(menu_item_modifier.is_multiple != 1) {
					item_count += (menu_item_modifier_item.is_selected) ? 1 : 0;
				}
				else {
					item_count += menu_item_modifier_item.item_count;
				}
				if($scope.menu_item.menu_item_modifier[index].is_selected == false && menu_item_modifier_item.is_selected) {
					$scope.menu_item.menu_item_modifier[index].is_selected = true;
				}
				
				if($scope.menu_item.menu_item_modifier[index].is_multiple == 0 && $scope.menu_item.menu_item_modifier[index].is_required == 1 && $scope.menu_item.menu_item_modifier[index].count_type == 0 && ($scope.menu_item.menu_item_modifier[index].max_count == 0 || ($scope.menu_item.menu_item_modifier[index].max_count == 1 && menu_item_modifier.menu_item_modifier_item.length == 1))) {
					$scope.menu_item.menu_item_modifier[index].is_selected = true;
					$scope.menu_item.menu_item_modifier[index].item_count = 1;
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
		var menu_price = $scope.menu_item.offer_price > 0 ? $scope.menu_item.offer_price : $scope.menu_item.price;
		menu_price = menu_price - 0;
		
		angular.forEach($scope.menu_item.menu_item_modifier, function(menu_item_modifier) {
			$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
				var item_count = 0;
				var count = 0;
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

	$('#checkout').click(function () {
		if($scope.order_data=='') {
			return false;
		}
		else {
			var url = getUrls('checkout');
			window.location.href = url ;
		}
	});

	$scope.order_data =[];
	$scope.add_notes='';

	function check_store() {
		var session_order_data = $scope.order_data;
		var prev_store_id ;

		$.each(session_order_data,function(key,val){
			prev_store_id =val.store_id;
		});

		if(prev_store_id){
			if(prev_store_id!=$scope.store_id){
				$scope.item_counts = $scope.item_count;
				$('.icon-close-2').trigger('click');
				$('.toogle_modal').trigger('click');
				return false;
			}
		}
		return true;
	}

	//remove order
	$scope.remove_sesion_data = function(index) {
		$('#calculation_form').addClass('loading');
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val();	
		var remove_url = getUrls('orders_remove');
		$scope.order_data.items.splice(index, 1);
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

	$scope.$watch('order_data', function() {
		if($scope.other_store == 'no' && $scope.order_data) {
			if($scope.order_data.total_item_count > 0) {
				$('.icon-shopping-bag-1').addClass('active');
			}
			else {
				$('.icon-shopping-bag-1').removeClass('active');
			}
			$('#count_card').text($scope.order_data ? $scope.order_data.total_item_count:'');
		}
	});

	$scope.order_store_changes = function(order_item_id) {
		$('#calculation_form').addClass('loading');
		var change_url = getUrls('orders_change');
		$scope.delivery_type  = $("input[name='delivery_type']:checked").val();	
		$scope.order_data.total_item_count = $('#count_quantity').val();
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
	};

	$('.store_popup').click(function(){
		var url = getUrls('session_clear_data');
		$http.post(url,{}).then(function(response) {
			$scope.order_data ='';
			$scope.other_store = 'no';
			$scope.order_store_session();
		});
	});

	var autocompletes;
	initAutocompletes();

	var input = document.getElementById('confirm_address');
	google.maps.event.trigger(input, 'place_changed');

	function initAutocompletes() {
		if(document.getElementById('confirm_address') == undefined) {
	    	return false;
	    }
		autocompletes = new google.maps.places.Autocomplete(document.getElementById('confirm_address'),{types: ['geocode']});
		autocompletes.addListener('place_changed', fillInAddress1);
	}

	function fillInAddress1() {
		$('#header_location_val').val('');
		fetchMapAddress1(autocompletes.getPlace());
	}

	function fetchMapAddress1(data) {
		var componentForm = {
			street_number: 'short_name',
			route: 'long_name',
			sublocality_level_1: 'long_name',
			sublocality_level_2: 'long_name',
			sublocality_level_3: 'long_name',
			sublocality: 'long_name',
			locality: 'long_name',
			political: 'long_name',
			administrative_area_level_1: 'long_name',
			country: 'short_name',
			postal_code: 'short_name'
		};

		$scope.address = '';
		$scope.postal_code = '';
		$scope.city = '';
		$scope.latitude = '';
		$scope.longitude = '';
		$scope.locality = '';

		var place = data;
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				var val = place.address_components[i][componentForm[addressType]];

				if (addressType == 'postal_code') $scope.postal_code = val;
				if (addressType == 'locality' || 'political') $scope.city = val;

				if (addressType == 'sublocality_level_1' && $scope.locality == '') 
					$scope.locality = val;
				else if (addressType == 'sublocality' && $scope.locality == '') 
					$scope.locality = val;
				else if (addressType == 'locality' && $scope.locality == '') 
					$scope.locality = val;
				else if(addressType == 'administrative_area_level_1' && $scope.locality == '') 
					$scope.locality = val;
				else if(addressType  == 'country' && $scope.locality == '') 
					$scope.locality = place.address_components[i]['long_name'];

				if(addressType       == 'street_number')
					$scope.street_address = val;
				if(addressType       == 'route')
					$scope.street_address = $scope.street_address+' '+val;
				if(addressType       == 'country')
					$scope.country = val;
				if(addressType       == 'administrative_area_level_1')
					$scope.state = val;
			}
		}

		$scope.latitude = place.geometry.location.lat();
		$scope.longitude = place.geometry.location.lng();
		$scope.is_auto_complete = 1;
		$('.checkout-content').addClass('loading');
		var url_search = getUrls('store_location');
		var location_val = $('#header_location_val').val();
		var delivery_type = $("input[name='delivery_type']:checked").val();
		var tips = $('#tips').val();
		console.log("tips"+tips);
		$scope.street_address = ($scope.street_address)?$scope.street_address:$scope.city;
		$http.post(url_search,{
			postal_code: $scope.postal_code,
			city: $scope.city,
			address : $scope.street_address,
			latitude: $scope.latitude,
			longitude: $scope.longitude,
			state : $scope.state,
			country : $scope.country,
			location: location_val,
			locality: $scope.locality,
			order_id: $scope.order_data.order_id,
			delivery_type:delivery_type,
			tips : tips,
		}).then(function(response) {
			$scope.order_data.delivery_fee = response.data.data.delivery_fee;
			var promo_amount = response.data.data.promo_amount;
			$scope.order_data.promo_amount = response.data.data.order_detail_data.promo_amount;
            $scope.order_data.total_price = response.data.data.order_detail_data.total_price;
		});

		$('#error_place_order').hide();

		var url              = getUrls('location_check');

		var restuarant_id    = $('#store_id').val();
		var order_data_id    = $('#order_data_id').val();
		var location         = $('#confirm_address').val();

		$http.post(url,{
			order_id         : order_data_id,
			restuarant_id    : restuarant_id,
			city             : $scope.city,
			address1         : $scope.street_address,
			state            : $scope.state,
			country          : $scope.country,
			postal_code      : $scope.postal_code,
			latitude         : $scope.latitude,
			longitude        : $scope.longitude,
			location         : location,
			locality         : $scope.locality,
			checkout_page    : 'Yes',

		}).then(function(response){
			if(response.data.success=='none'){
				$('.checkout-content').removeClass('loading');
				$('#error_place_order').show();
				$('#place_order').attr('disabled','disabled');
				$('#error_place_order').text(response.data.message);
				return false;
			}
			$('#error_place_order').hide();
			$('.checkout-content').removeClass('loading');
		});
		$('#place_order').removeAttr('disabled');
		$('#order_city').val($scope.city);
		$('#order_street').val($scope.street_address);
		$('#order_state').val($scope.state);
		$('#order_country').val($scope.country);
		$('#order_postal_code').val($scope.postal_code);
		$('#order_latitude').val($scope.latitude);
		$('#order_longitude').val($scope.longitude);

	}



	$('#confirm_address').change(function(){
		if($(this).val()=='') {
			$('#place_order').attr('disabled','disabled');
		}
	});

	$(document).ready(function(){
		if($('#confirm_address').val()==''){
			$('#place_order').attr('disabled','disabled');
			$('#error_place_order').show();
		}
		else{
			$('#place_order').removeAttr('disabled');
			$('#error_place_order').hide();
		}

		$('#confirm_address').keyup(function(){
			if($('#confirm_address').val()==''){
				$('#place_order').attr('disabled','disabled');
				$('#error_place_order').show();
				$('#error_place_order').text(Lang.get('js_messages.store.location_field_is_required'));
			}
		})
	});

	$scope.updateCardDetails = function() {
		$('.payment-modal_load').addClass('loading');
		$('#error_add_card').text('');
		$scope.ajax_loading = true;
		var url = getUrls('card_details');
		var data_params = {
			card_number     : $('#card_number').val(),    
			expire_month    : $('#expire_month').val(),
			expire_year     : $('#expire_year').val(),
			cvv_number      : $('#cvv_number').val(),
		}
		$http.post(url,data_params).then(function(response) {
			$('.payment-modal_load').removeClass('loading');
			console.log(response.data.status_code );
			if(response.data.status_code == '0') {
				$scope.ajax_loading = false;
				$('#error_add_card').text(response.data.status_message);
			}
			if(response.data.status_code == '1') {
				$scope.confirmCardSetup(response.data.intent_client_secret);
				console.log(response.data.intent_client_secret);
				$('.before_add_card').addClass('d-none');
				$('.card_wallet').removeClass('d-none');
			}
		});

	};

    $scope.confirmCardSetup = function (clientSecret) {
		var stripe = Stripe(STRIPE_PUBLISH_KEY);
		stripe.confirmCardSetup(clientSecret).then(function(result) {
			console.log(result)
		    if (result.error) {
		    	$('#error_add_card').text(result.error.message);
		      	// Display error.message in your UI.
		      	// $('#payment-error').text(result.error.message);
				$('#payment-error').removeClass('d-none');
				$('#payment-error').hide().html(result.error.message).fadeIn('slow').delay(5000).hide(1);
		      	$scope.ajax_loading = false;
		      	$scope.applyScope();
		    }
		    else {
		      	// The setup has succeeded. Display a success message.
		    	$scope.confirmCardDetails(result.setupIntent.id);
		    	$('#payment-modal').modal('hide');
		    }
		});
    };

    $scope.completeCardAuthentication = function (clientSecret,data_params) {
    	var stripe = Stripe(STRIPE_PUBLISH_KEY);
        stripe.confirmCardPayment(clientSecret).then(function(result) {
        	$('#wallet-modal div').first().removeClass('loading');

			if (result.error) {
				// Show error in payment form
				$('#payment-error').text(result.error.message);
				$('#payment-error').removeClass('d-none');
				$scope.ajax_loading = false;
				$scope.applyScope();
			}
			else {
				// The card action has been handled & The PaymentIntent can be confirmed again on the server
				data_params['payment_intent_id'] = result.paymentIntent.id;
				$scope.placeOrder(data_params);
			}
        });
    };

    $scope.confirmCardDetails = function(intent_id) {
		var url = getUrls('card_details');
		var data_params = {
			intent_id     : intent_id,
		}

		$http.post(url,data_params).then(function(response) {
			$scope.ajax_loading = false;
			$('.payment-modal_load').removeClass('loading');
			if(response.data.status_code == '0') {
				$('#error_add_card').text(response.data.status_message);
			}

			if(response.data.status_code == '1') {
				$('#payment-modal').modal('hide');
				$scope.confirmCardSetup(response.data.intent_client_secret);
			}

			if(response.data.status_code == '2') {
				$('#payment-modal').modal('hide');
				$scope.payment_details = response.data.payment_details;
				$scope.payment_method = 1;
				$('#payment_detail').show();
			}
		});
    };


	$scope.nonce = '';
	$scope.data_params = [];
	$scope.pay_key = '';
	var braintree_token = $('#paypal_access_token').val();	
	
	if(braintree_token !=undefined){	
		paypal.Button.render({
			  braintree: braintree, 
			  client: {
			    sandbox: braintree_token
			  },
			  env: 'sandbox',
			  commit: true, // This will add the transaction amount to the PayPal button
			  payment: function (data, actions) {
			    return actions.braintree.create({
			    	  flow: 'checkout',
				      amount: $scope.amount,
				      currency:$scope.currency,
				      intent: 'sale'
			    });
			  },
			  onAuthorize: function (payload, actions) {
			  	$scope.pay_key = payload.nonce;
			  	$('#paypal-button').removeClass('d-none');
				$('#place_order').addClass('d-none');
				if(last_part1 == 'checkout')
				{
			  		$('#place_order').trigger('click');
				}
				else
				{
					$scope.walletAmount();
				}	
			  }
		}, '#paypal-button');
	}

	var current_url = window.location.href.split('?')[0];
	var last_part = current_url.substr(current_url.lastIndexOf('/'));
	var last_part1 = current_url.substr(current_url.lastIndexOf('/') + 1);
	
	$scope.updatePaymentMethod = function() {
		$('#payment_detail').css('display','none');
		$('#paypal-button').addClass('d-none');
		$('#place_order').removeClass('d-none');
		$('#place_order').removeAttr('disabled');
		$('#paypal_error').hide();
		if($scope.payment_method==2) {
			if($scope.paypal_error){
				$('#paypal_error').show()
				$('#place_order').attr('disabled', 'disabled');
				return false
			}
			$('.before_add_card').addClass('d-none');
			var confirm_address     = $('#confirm_address').val();
			var order_city          = $('#order_city').val();
			var order_state         = $('#order_state').val();
			$('#card_wallet').addClass('d-none');
			$('.card_wallet').addClass('d-none');
			$('.error_place_wallet').css('display','none');
			if(confirm_address!='' && order_city!='' && order_state!='') {
				$('.place_order_change').addClass('loading');
					if(last_part1 == 'checkout')
					{
						var url = getUrls('paypal_currency_conversion');
						$http.post(url).then(function(response) {
							if(response.data.success=='true') {
								$scope.amount = response.data.amount;
								$scope.currency = response.data.currency;
								$scope.applyScope();
								$('#paypal-button').removeClass('d-none');
								$('#place_order').addClass('d-none');
							}
								$('.place_order_change').removeClass('loading');
						});
					}
					else if(last_part1 == 'user_payment'){
						$("#payment_method").attr('disabled', 'disabled');
						var url = getUrls('add_wallet_amount_paypal');
						$http.post(url,{
							amount     : $('#amount').val(), 
							pay_key		   : $scope.pay_key,
						}).then(function(response) {
							if(response.data.success=='true') {
								$scope.amount = response.data.amount;
								$scope.currency = response.data.currency;
								$scope.key = response.data.braintree_clientToken
								$scope.applyScope();
								$('#paypal-button').removeClass('d-none');
								$('#place_order').addClass('d-none');
								$("#payment_method").removeAttr('disabled');
							}
						});
					}

			} else {
				$scope.payment_method = 0;
				$('#error_place_order').css('display','block');
			}
		}else if($scope.payment_method==1) {
			if(!$scope.payment_details) {
				$('#place_order').attr('disabled','disabled');
			}
			else if(last_part1 == 'user_payment')
			{
				$('.card_wallet').removeClass('d-none');
				$('#card_wallet').removeClass('d-none');
			}
			// $('#paypal-button').addClass('d-none');
			$('#payment_detail').css('display','block');
		}
	}

	$scope.$watch('payment_details', function(value) {
		if(value) {
			$('#place_order').removeAttr('disabled');
		}
	});

	$('input[name="wallet"]').click(function () {
		if($(this).prop("checked") == true){
			$scope.pWallet=1;
		}
		else if($(this).prop("checked") == false)
		{
			$scope.pWallet=0;
		}
		$('.place_order_change').addClass('loading');
		var tips = $('#tips').val();
		var order_id = $('#order_data_id').val();
	  	var url = getUrls ('add_wb_wallet');
	  	var deliverytype = $("#delivery_type_radio").val();
		$http.post(url,{
				isWallet : $scope.pWallet,
				order_id : order_id,
				deliverytype:deliverytype,
				tips : tips,
			}).then(function(response) {
				if(response.data.status == 1)
				{
					$scope.order_data = response.data.order_detail_data;

				}
			$('.place_order_change').removeClass('loading');		
		});
	});

	$('#place_order').click(function() {
		if($scope.wallet == true)
		{
			$scope.pWallet=1;
		}
		else 
		{
			$scope.pWallet =0;
		}
		$('.place_order_change').addClass('loading');
			var confirm_address     = $('#confirm_address').val();
			var order_street        = $('#order_street').val();
			var order_city          = $('#order_city').val();
			var order_state         = $('#order_state').val();
			var order_country       = $('#order_country').val();
			var order_postal_code   = $('#order_postal_code').val();
			var order_latitude      = $('#order_latitude').val();
			var order_longitude     = $('#order_longitude').val();
			var suite               = $('#suite').val();
			var delivery_note       = $('#delivery_note').val();
			var payment_method      = $('#payment_method').val();
			var order_note          = $('#order_note').val();
			var delivery_time       = $('#delivery_time').val();
			var order_type          = $('#order_type').val();
			var delivery_type 		= $("input[name='delivery_type']:checked").val();
			var url 				= getUrls('place_order_details');
			var tips 				= $('#tips').val();
			var isWallet 			= $scope.pWallet;
		if(confirm_address!='' && order_city!='' && order_state!='') {
			$('#error_place_order').css('display','none');
			$http.post(url,{
				confirm_address     : confirm_address, 
				street              : order_street,
				city                : order_city, 
				state               : order_state,
				country             : order_country,
				postal_code         : order_postal_code,
				latitude            : order_latitude,
				longitude           : order_longitude,  
				suite               : suite,
				delivery_note       : delivery_note,
				payment_method      : payment_method,
				order_note          : order_note,
				delivery_type		: delivery_type,
				tips   				: tips,
			}).then(function(response) {
				if(response.data.success=='true') {
					var order_id = response.data.order.id;
					var wallet = $scope.pWallet;
					var data_params = {
						order_id       : order_id,
						isWallet       : wallet,
						payment_method : payment_method,
						delivery_time  : delivery_time,
						order_type     : order_type,
						notes     	   : order_note,
						pay_key		   : $scope.pay_key,
						delivery_type   :delivery_type,
						tips :tips,
					};
					$scope.data_params = data_params;
					$scope.applyScope();
					$scope.placeOrder(data_params);
				}
			});
		} else {
			$('.place_order_change').removeClass('loading');
			$('#error_place_order').css('display','block');
		}
	});

	$scope.placeOrder = function(data_params) {
		var url = getUrls ('place_order');
		$http.post(url,data_params).then(function(response) {
			$('.place_order_change').removeClass('loading');
			$('#payment-error').addClass('d-none');
			$scope.ajax_loading = false;
			if(response.data.status_code == '1') {
				$('#order_id').val(response.data.order_details.id);
				var url = getUrls('order_track');
				window.location.href = url+'?order_id='+response.data.order_details.id;
			}
			else if(response.data.status_code == '2') {
				$scope.ajax_loading = true;
				$scope.completeCardAuthentication(response.data.client_secret,data_params);
			}
			else if(response.data.status_code == '3') {
				$('#payment-error').text(response.data.status_message);
				$('#payment-error').removeClass('d-none');
			}
			else if(response.data.status_code == '0') {
				$('#payment-error').removeClass('d-none');
				$('#payment-error').text(response.data.status_message);
			}
			else {
				$('#error_place_order').text(response.data.status_message);
				$('#error_place_order').show();
			}
		});
	};

	$scope.add_notes='';
	$scope.order_store_session= function() {
		var location_val =$('#location_search_new').val() ;
		if($scope.other_store=='yes'){
			$('#myModal').modal();
			return false;
		}
		$('.detail-popup').addClass('loading');
		$('.cart-scroll').addClass('loading');
		$scope.item_count = $scope.item_count;
		var store_id = $scope.store_id;
		$scope.item_notes = $scope.add_notes;
		var index_id = $(this).attr('data-remove');
		var menu_item_id = $scope.menu_item.id;
		var add_cart = getUrls('add_cart');
		$http.post(add_cart,{
			store_id    : store_id,
			menu_data        : $scope.menu_item,
			item_count       :  $scope.item_count,
			item_notes       : $scope.item_notes,
			item_price       : $scope.price,
			individual_price : $scope.individual_price,
		}).then(function(response) {
			$scope.order_data = response.data.cart_detail;

			$('.detail-popup').removeClass('loading');
			$('.cart-scroll').removeClass('loading');

			$('.detail-popup').removeClass('active');
			$('body').removeClass('non-scroll');
			$('#checkout').removeAttr('disabled');
		});
	};

	$scope.walletAmount = function(){
		var data_params = {
			amount  : $('#amount').val(),
			currency_code : $scope.currency 
		}
		var url = getUrls ('add_wallet_amount');
		var url2= getUrls('user_payment');
		$http.post(url,data_params).then(function(response) {
			window.location.href = url2;
		});
	};	


	$("#amount").keypress(function (e){
	  var charCode = (e.which) ? e.which : e.keyCode;
	  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
	    return false;
	  }
	});

	$scope.walletStripeAmount = function() {
		var data_params = {
			amount  : $('#amount').val(),
			currency_code : $('#currency_code').val()
		}
		var url = getUrls('add_wallet_stripe');
		var url2= getUrls('user_payment');
		$('#card_wallet').prop('disabled', true);
		$('#wallet-modal div').first().addClass('loading');
		$http.post(url,data_params).then(function(response) {
			if(response.data.status_code == '1') {
				$('#wallet-modal div').first().removeClass('loading');
				window.location.href = url2;
			}
			else if(response.data.status_code == '2') {
				$scope.ajax_loading = true;
				$scope.completeCardAuthentication(response.data.client_secret,data_params);
			}
			else {
				$('#wallet-modal div').first().removeClass('loading');
				$('#card_wallet').prop('disabled', false);
				$('#error_place_wallet').show();
				$('#error_place_wallet').text(response.data.status_message);
			}
		});
	};

}]);

app.controller('orders_detail', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
	history.pushState(null, null, location.href);
	window.onpopstate = function() {
		history.go(1);
	};

	$('.invoice-btn').click(function() {
		var order_id = $(this).attr('data-id');
		var url = getUrls('order_invoice');
		$http.post(url,{
			order_id    : order_id,
		}).then(function(response) {
			$scope.order_detail = response.data.order_detail;
			$scope.currency_symbol = response.data.currency_symbol;
		});
	});

	$(document).ready(function() {
		var status = $('#order_status').val();
		var display = (parseInt(status) >= 5) ? 'block' : 'none';
		$('.delivery_data').css('display',display);
	});

	$scope.open_cancel_model = function(id){
		$('#open_cancel_model').modal('show');
		$('#cancel_order_id').val(id);
	};

}]);
