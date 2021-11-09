app.controller('category', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
	$scope.remove = false;
	$scope.delivery ='true';
	$scope.takeaway = 'false';
	$scope.delivery_type = '';

	$(document).ready(function() {
		$('.categorylist_slider').owlCarousel('destroy');
		$scope.category();
		$scope.search_result();
	});
	$scope.fullwidth = function(){
        $scope.isActive = true;
    }

    $(window).scroll(function() {
        $scope.isActive = false; 
        $("#feed_location").keypress(function(){
		  $scope.fullwidth();
		});
    });	

	$scope.location_change = function(){
		if($scope.location == '' || $scope.location == undefined)
		{	
	        $('.location_error_msg').removeClass('d-none');
	        setTimeout(function(){
	        	$scope.isActive = false;
	   		},50);

	    }else{
			setTimeout(function(){
				$('.location_error_msg').addClass('d-none');
	        	$scope.isActive = false;
			},50);
	    }
    }
    $scope.selected_location = '';

	var displaySuggestions = function(predictions, status) {
	    if (status != google.maps.places.PlacesServiceStatus.OK) {
	      return;
	    }
	    predictions.unshift({description: 'Use Current location',data_id:''});
	    init_autocomplete(predictions);
	};

	function init_autocomplete(data) {
		document.getElementById('google_results').innerHTML=''
		data.forEach(function(prediction) {
	      var li = document.createElement('li');
	      li.appendChild(document.createTextNode(prediction.description));
	      li.setAttribute('data_id',prediction.place_id);
	      li.setAttribute('class','place_search ');
	      document.getElementById('google_results').appendChild(li);
	    });
	}

	function fetchMapAddress(data) {
		var componentForm = {
			street_number: 'short_name',
			route: 'long_name',
			sublocality_level_1: 'long_name',
			sublocality_level_2: 'long_name',
			sublocality_level_3: 'long_name',
			sublocality: 'long_name',
			locality: 'long_name',
			administrative_area_level_1: 'long_name',
			country: 'short_name',
			postal_code: 'short_name'
		};
		var locate = {'address1':''}; 
		var place = data;
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				var val = place.address_components[i][componentForm[addressType]];
				if (addressType == 'postal_code')
					locate['postal_code'] = val;
				if (addressType == 'locality')
					locate['city'] = val;

				if (addressType == 'sublocality_level_1' && $scope.locality == '') 
					locate['locality'] = val;
				else if (addressType == 'sublocality' && $scope.locality == '') 
					locate['locality'] = val;
				else if (addressType == 'locality' && $scope.locality == '') 
					locate['locality'] = val;
				else if(addressType == 'administrative_area_level_1' && $scope.locality == '') 
					locate['locality'] = val;
				else if(addressType  == 'country' && $scope.locality == '') 
					locate['locality'] = place.address_components[i]['long_name'];

				if(addressType       == 'street_number')
					locate['address1'] = val;
				if(addressType       == 'route')
					locate['address1'] = locate['address1'] +' '+ val;
				if(addressType       == 'country')
					locate['country'] = val;
				if(addressType       == 'administrative_area_level_1')
					locate['state'] = val;
			}
		}
		locate['latitude']  = place.geometry.location.lat();
		locate['longitude'] = place.geometry.location.lng();
		locate['location'] = place.formatted_address
		$('#feed_location').val(place.formatted_address);
		$scope.selected_location = $('#feed_location').val();
		$('#user_address').val(JSON.stringify(locate));
		document.getElementById('google_results').innerHTML=''
		var url_search = getUrls('store_location');
		$http.post(url_search,{
			location: JSON.stringify(locate),
		}).then(function(response){
			$('.cls_nearbyin').addClass('dot-loading');
			$('.categorylist_slider').owlCarousel('destroy');
			$scope.categoryies = response.data.data.categoryies;
			$scope.category();	
			$scope.search_result(response);
		});
		return locate;
	}

	function getLatLong(search_by,place_id) {
		var geocoder = new google.maps.Geocoder();
		var result = "";
		geocoder.geocode( { [search_by]: place_id}, function(results, status) {
		     if (status == google.maps.GeocoderStatus.OK) {
		     	fetchMapAddress(results[0]);
		     }
		});
	}

	function getCurrentLocation() {
	  if (navigator.geolocation) {
		    navigator.geolocation.getCurrentPosition((position) => {
		    	getLatLong('location',{lat:position.coords.latitude,lng:position.coords.longitude});
		    })
	  }
	}	

	$scope.in_autocomplete = function() {
		$('#cls_remove').fadeIn(300);
		$('#google_results').fadeIn(300);
		prediction = [{description: 'Use Current location',data_id:''}];
	    init_autocomplete(prediction);
	}

	$(document).on('click','.place_search',function(){
		place_id = $(this).attr('data_id');
		if(place_id=='' || place_id == "undefined")
			getCurrentLocation();	
		else 
		 	getLatLong('placeId',place_id);
	})

 	var timeout;
  	var delay = 500; 
	$('#cls_remove').fadeOut(300);
		$('#feed_location').keyup(function(e) {
		    if(timeout) 
		    	clearTimeout(timeout);
		    timeout = setTimeout(function() { getPlaceResults($('#feed_location').val()); }, delay);
			   //  e.stopPropagation();
			   if ($('#feed_location').val() == "") {
			  	$('#cls_remove').fadeIn(300);
			 	}
			  	$('#google_results').fadeIn(300);

		});

	  	$("#cls_remove").click(() => {
		  	// $('#user_address').val("");
		  	$('#feed_location').val("");
		  	$('#google_results').fadeOut(300);
		  	$("#cls_remove").fadeOut(300);
	  	});

	  $(document).click((event) => {
		  if (!$(event.target).closest('.custom_input').length && !$(event.target).closest('.google_results').length) {
		    $('#google_results').fadeOut(300);
		    $("#cls_remove").fadeOut(300);
		    $scope.isActive = false;
			$scope.$apply();

		  }        
		});
	function performSearch() {
	    var panelbar, filter, searchRegEx, $span;

	    filter = $("#feed_location").val();
	    if (filter == lastFilter) return;
	    if (filter.length < 3) return;  // only search 3 or more characters in searchTerm
	    lastFilter = filter;
	  }

	function getPlaceResults(input_data){
	 var service = new google.maps.places.AutocompleteService();
	  service.getQueryPredictions({
	    input: input_data,
	        types: ['establishment', 'geocode']
	  }, displaySuggestions);
	}

	$scope.search_result = function(data)
	{
		$('.search_page').addClass('loading');
		var url = getUrls('search_result');
		var request_cat = $('#request_cat').val();
		$http.post(url,{
			postal_code  : $scope.postal_code,
			city         : $scope.city,
			latitude     : $scope.latitude,
			longitude    : $scope.longitude,
			location     : $scope.location,
			category     : $scope.category_id,
			keyword      : request_cat,
			page		 : $scope.page,
			delivery_type :$scope.delivery_type,
		}).then(function(response){  

			$scope.store_data = response.data;
			$scope.search_data_key = response.data.search;
			$scope.page = response.data.current_page;
			$('.search_page').removeClass('loading');
			if(response.data.store==''){
				$('.no_result').css('display','block');
			}
			else{
				$('.no_result').css('display','none');
			}
			// Check Total Page ( Total available store based on pagination)
			if(response.data.total_page > 1){
				if(response.data.current_page + 1 <= response.data.total_page){
					setTimeout(function () {
						$scope.getSearchResult();
					}, 400);
				}
			}
			$('.cls_nearbyin').removeClass('dot-loading');
		});
	}

	$scope.category = function() {
  		setTimeout(function() {
			var $categories = $('.categorylist_slider');	
			$categories.owlCarousel({
				    loop:false,
				    items:5,
				    margin:30,
				    nav:true,
				    rtl:true,
				    navText: [
				        '<i class="icon icon-right-arrow" aria-hidden="true"></i>',
				        '<i class="icon icon-right-arrow" aria-hidden="true"></i>'
				    ],
				    dots:false,
				    responsive:{
				        0:{
				            items:1
				        },
				        600:{
				            items:3
				        },
				        1000:{
				            items:5
				        }
				    }
				})	
		},150);
	};
	
    $scope.closePopup = function(){
        setTimeout(function(){
            $scope.checked = false;
            $scope.$apply();
            console.log($scope.checked)
        },1000);
    }

    $scope.inchange = function(){
        if ($scope.location.length >= 3) {
             $scope.remove = true;
        }
    }
    $scope.removeclick = function(vals){
         $scope.location = null;
    }
    $scope.test = function($event){
        console.log(angular.element($event));
    }

    $scope.delivery_search = function($event){
    	$('.cls_nearbyin').addClass('dot-loading');
    	$scope.delivery_type = 'delivery';
    	$('#delivery_type').val($scope.delivery_type);
    	$scope.delivery ='true';	
    	$scope.takeaway ='false';
    	$scope.search_result();
    	// $('.cls_nearbyin').removeClass('dot-loading');
    };

    $scope.takeaway_search = function($event){
    	$('.cls_nearbyin').addClass('dot-loading');
    	$scope.delivery_type = 'takeaway';
		$('#delivery_type').val($scope.delivery_type);
    	$scope.delivery ='false';	
		$scope.takeaway ='true';
    	$scope.search_result();
    };

    $scope.categoryStore = function($id){
    	$('.cls_nearbyin').addClass('dot-loading');
    	$scope.categorty_id = $id;
    	$scope.page = 0;
    	var url = getUrls('search_result');
    	console.log($scope.categorty_id);
		$http.post(url,{
			postal_code  : $scope.postal_code,
			city         : $scope.city,
			latitude     : $scope.latitude,
			longitude    : $scope.longitude,
			location     : $scope.location,
			category     : $scope.categorty_id,
			page		 : $scope.page + 1,
		}).then(function(response){ 
			//Check Total Page ( Total available store based on pagination)
			$scope.store_data = response.data;
			$scope.search_data_key = response.data.search;
			if(response.data.current_page + 1 <= response.data.total_page){
				setTimeout(function () {
					$scope.categoryStore();
				}, 400);
			}else{
				$scope.page = 1;
				// After complete all pagination load, set page count for one. 
				// Reason : next functionality start with page one.
			}
			$('.cls_nearbyin').removeClass('dot-loading');
		}); 
    };

    $('#top_category_search,#top_category_search_mob').on('keydown', function (e) {
		$service_type = $('#service_type').val() ;
		if(e.keyCode == 13) {
			$('.cls_nearbyin').addClass('dot-loading');
			$scope.search_key = $(this).val();
			console.log($scope.search_key);
			$scope.searchCalll = false;
			$('.search-field .icon-close-2').trigger('click');
			$('#top_category_search,top_category_search_mob').attr("placeholder", Lang.get('js_messages.store.search_for_store_cuisine'));
			var service_type= $('#service_type').val();	
			var url = getUrls('search_data');
			$('.search_page').addClass('loading');
			$http.post(url,{
				keyword : $scope.search_key,
				service_type : service_type,
			}).then(function(response){
				$([document.documentElement, document.body]).animate({
				        scrollTop: $("#storelsit").offset().top - 100
				}, 100);
				$scope.store_data = response.data;
				$scope.search_data_key = response.data.search;
				$('.search_page').removeClass('loading');
				$scope.search_key_search=$scope.search_key;
				if(response.data.store=='')
				{
					$('.no_result').css('display','block');
				}

				else
				{
					$('.no_result').css('display','none');
				}
			});
			if($scope.search_key==''){
				$scope.page = 1;
				$scope.searchCalll = true;
				$scope.search_result();
			}
		}
		$('.cls_nearbyin').removeClass('dot-loading');
	});



}]);
