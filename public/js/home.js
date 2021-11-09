app.controller('home_page', ['$scope', '$http','$rootScope','$timeout', function ($scope, $http,$rootScope,$timeout) {
	$scope.locate=[];
	var service_type= 1;	
	$rootScope.service_type = service_type;
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
		$scope.locate = locate;
		$('#head_location_val').val(place.formatted_address);
		$('#user_address').val(JSON.stringify(locate));
		document.getElementById('google_results').innerHTML=''

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
	  $('#head_location_val').keyup(function(e) {
	    if(timeout) 
	    	clearTimeout(timeout);
	    timeout = setTimeout(function() { getPlaceResults($('#head_location_val').val()); }, delay);
		   //  e.stopPropagation();
		   if ($('#head_location_val').val() == "") {
		  	$('#cls_remove').fadeIn(300);
		  }
		  	$('#google_results').fadeIn(300);

		  	if ($('#head_location_val').val() == "") {
			  	$('#cls_remove').fadeOut(300);
			  	$('#google_results').fadeOut(300);
		  	}
	  });

	  $("#cls_remove").click(() => {
	  	// $('#user_address').val("");
	  	$('#head_location_val').val("");
	  	$('#google_results').fadeOut(300);
	  	$("#cls_remove").fadeOut(300);
	  	

	  });
	  $(document).click((event) => {
		  if (!$(event.target).closest('.forminput').length) {
		  	
		    $('#google_results').fadeOut(300);
		  }        
		});

	  function performSearch() {
	    var panelbar, filter, searchRegEx, $span;

	    filter = $("#head_location_val").val();
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

	$('.find_store').click(function() {
		var valid  = $('#head_location_val').val();
		if (!valid) {
			$(".forminput").append('<span id="header_location_val-error" class="text-danger">'+Lang.get('js_messages.store.field_required')+'</span>');
    		return false;
		}
		var service_type= 1;
		var url_search = getUrls('store_location');
		var location_val = $('#user_address').val();
		console.log($('#user_address').val());
		$scope.street_address = ($scope.street_address)?$scope.street_address:$scope.city;
		$http.post(url_search,{
			location: location_val,
			service_type:service_type,
		}).then(function(response){
			var url = getUrls('feeds');
			window.location.href = url;
		});
	});

	$scope.setSession = function(id) {
		var url_search = getUrls('set_service_type');
		$http.post(url_search,{
			service_type : 1,
		}).then(function(response){
			var url = getUrls('feeds');
			window.location.href =  url ;
		});
	}
	
}]);