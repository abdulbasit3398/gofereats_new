$(document).on('click', '.confirm-delete', function () {
	var url = $(this).attr('data-href');
	swal({
		title: 'Are you sure delete?',
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Yes, delete it!',
		cancelButtonText: 'Cancel',
		confirmButtonClass: "btn btn-success",
		cancelButtonClass: "btn btn-danger",
		buttonsStyling: false
	}).then(function () {
		window.location = url;
	});
});

// Code for the store_preparation_time Validator
var $preparation_time_validator = $('#store_preparation_time').validate({
	rules: {
		"max_time[]": { required: true },
		"day[]": { required: true },
		"from_time[]": { required: true,greate_then:true },
		"to_time[]": { required: true },
		"status[]": { required: true },
	},
	errorElement: "span",
	errorClass: "text-danger",
	errorPlacement: function( label, element ) {
		if(element.attr( "data-error-placement" ) === "container" ){
			container = element.attr('data-error-container');
			$(container).append(label);
		} else {
			label.insertAfter( element ); 
		}
	},
});

$('#open_time_form').validate({
	rules: {
		"day[]": { required: true },
		"start_time[]": { required: true,greate_then:true },        
		"end_time[]": { required: true },                       
		"status[]": { required: true },                       
	},
	errorElement: "span",
	errorClass: "text-danger",
	errorPlacement: function( label, element ) {
		if(element.attr( "data-error-placement" ) === "container" ){
			container = element.attr('data-error-container');
			$(container).append(label);
		} else {
			label.insertAfter( element ); 
		}
	},
});

$.validator.addMethod('greate_then', function(value, element, param) {
	var end_time = $(element).attr('data-end_time');
	var start_time = $(element).val();

	if(end_time) {
		return start_time < end_time;
	}
	else {
		return 'false';
	}
}, 'The start time should be less than the end time');

app.controller('cancelOrderController', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#order_cancel_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true }, 
			"translations[1][name]" : { required: true }, 
			"translations[0][reason]" : { required: true },                
			"translations[1][reason]" : { required: true },                
			type : { required: true },  
			reason :  { required: true },                                             
			status : { required: true },               

		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The Name field is required.'},
			'translations[0][reason]': {required :'The Reason field is required.'},                                
			'translations[1][reason]': {required :'The Reason field is required.'},                                
			reason: {required :'The Reason field is required.'},
			status: {required :'The Status field is required.'},
			type: {required :'The Type field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});
}]);

app.controller('promoController', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#promo_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][lang_code]" : { required: true },
			"translations[1][lang_code]" : { required: true },
			promo_type : { required: true },  
			code :  { required: true },                
			price : { required: true,number: true },               
			currency_code : { required: true }, 
			percentage : { required: true,number: true },
			start_date : { required: true }, 
			end_date : { required: true }, 
			status : { required: true },                  
		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][lang_code]': {required :'The Code field is required.'},                
			'translations[1][lang_code]': {required :'The Code field is required.'},                
			promo_type: {required :'The Promo Type field is required.'},
			code: {required :'The Code field is required.'},
			price: {required :'The Price field is required.'},
			currency_code: {required :'The Status field is required.'},
			start_date: {required :'The Start_Date field is required.'},
			end_date: {required :'The End_Date field is required.'},
			status: {required :'The Status field is required.'},
			percentage: {required :'The Percentage field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});

	$("#input_start_date").click(function(){
	  	$('.promo_start_date').addClass('d-none');
	});

	$("#input_end_date").click(function(){
	  	$('.promo_end_date').addClass('d-none');
	});

}]);

app.controller('homeSliderController', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {



	var $validator = $('#home_slider_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			"translations[0][description]" : { required: true },
			"translations[1][description]" : { required: true },
			"translations[0][title]": { required: true },
			"translations[1][title]": { required: true },
			name : { required: true },  
			description :  { required: true },                
			title : { required: true },               
			status : { required: true }, 
			image: {

				required : {
					depends: function(element){
						$formType = $('.form_type').val();
						if($formType == 'add'){
							return true;
						}else{
							return false;
						}
					}  
				},



				image_valitation:"png|jpg|jpeg|gif"},
				type : { required: true },                    
			},
			messages: {
				'translations[1][locale]': {required :'The Language field is required.'},                
				'translations[0][locale]': {required :'The Language field is required.'},                
				'translations[1][name]': {required :'The Name field is required.'},
				'translations[0][name]': {required :'The Name field is required.'},
				'translations[1][description]' : { required: 'The Description field is required.' },
				'translations[0][description]' : { required: 'The Description field is required.' },
				"translations[1][title]" : { required: 'The Title field is required.' },              
				"translations[0][title]" : { required: 'The Title field is required.' },              
				title: {required :'The Title field is required.'},
				name: {required :'The Name field is required.'},
				description: {required :'The Description field is required.'},
				status: {required :'The Status field is required.'},
				type: {required :'The Type field is required.'},
			},
			errorElement: "span",
			errorClass: "text-danger ng-binding",
			errorPlacement: function( label, element ) {
				if(element.attr( "data-error-placement" ) === "container" ){
					container = element.attr('data-error-container');
					$(container).append(label);
				} else {
					label.insertAfter( element ); 
				}
			},
		});  

	$.validator.addMethod("image_valitation", function(value, element, param) {

		param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
		return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
	}, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));
}]);

app.controller('cuisineController', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	$.validator.addMethod("image_valitation", function(value, element, param) {

		param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
		return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
	}, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));

}]);

app.controller('help', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#help_categpry_form').validate({
		ignore: [],
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			'translations[0][description]': { required: true },
			'translations[1][description]': { required: true },
			category_id : { required: true },
			subcategory_id : { required: true },                
			question : { required: true },
			answer : { required: true },   
			status : { required: true },               

		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Question field is required.'},
			'translations[1][name]': {required :'The Question field is required.'},
			'translations[0][description]': {required :'The Answer field is required.'},
			'translations[1][description]': {required :'The Answer field is required.'},
			category_id: {required :'The Category field is required.'},
			subcategory_id: {required :'The SubCategory field is required.'},
			question: {required :'The Question field is required.'},
			answer: {required :'The Answer field is required.'},
			status: {required :'The Status field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});

	$scope.change_category = function(value) {

		$http.get(APP_URL+'/admin/ajax_help_subcategory/'+value).then(function(response) {

			$scope.subcategory = response.data;
			$timeout(function() { $('#input_subcategory_id').val($('#hidden_subcategory_id').val()); $('#hidden_subcategory_id').val('') }, 10);
		});
	};
	$timeout(function() { $scope.change_category($scope.category_id); }, 10);
	$scope.multiple_editors = function(index) {
		setTimeout(function() {
			$("#editor_"+index).Editor();
			$("#editor_"+index).parent().find('.Editor-editor').html($('#content_'+index).val());
		}, 100);
	}
	$("[name='submit']").click(function(e){
		$scope.content_update();
	});
	$scope.content_update = function() {
		$.each($scope.translations,function(i, val) {
			$('#content_'+i).text($('#editor_'+i).Editor("getText"));
		})
		return  false;
	}
}]);

app.controller('issue_type', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
	var $validator = $('#issue_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			type : { required: true },
			issue : { required: true },                
			status : { required: true },                

		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Issue field is required.'},
			'translations[1][name]': {required :'The Issue field is required.'},
			type: {required :'The Type field is required.'},
			status: {required :'The Status field is required.'},
			issue: {required :'The Issue field is required.'},

		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});
}]);

app.controller('page', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#static_page_form').validate({
		ignore: [],
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			"translations[0][content]" : { required: true },
			"translations[1][content]" : { required: true },
			name : { required: true },
			url : { required: true },                
			footer : { required: true },
			status : { required: true },
			user_page : { required: true },
			content : { required: true },                
		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The Name field is required.'},
			'translations[0][content]': {required :'The Content field is required.'},
			'translations[1][content]': {required :'The Content field is required.'},
			name: {required :'The Name field is required.'},
			status: {required :'The Status field is required.'},
			footer: {required :'The Footer field is required.'},
			user_page: {required :'The User Page field is required.'},
			content: {required :'The Content field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});

	$scope.multiple_editors = function(index) {
		setTimeout(function() {
			$("#editor_"+index).Editor();
			$("#editor_"+index).parent().find('.Editor-editor').html($('#content_'+index).val());
		}, 100);
	}

	$("[name='submit']").click(function(e){
		$scope.content_update();
	});

	$scope.content_update = function() {
		$.each($scope.translations,function(i, val) {
			$('#content_'+i).text($('#editor_'+i).Editor("getText"));
		})
		return false;
	};
}]);

app.controller('service_type', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#service_form').validate({
		ignore: [],
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			"translations[0][description]" : { required: true },
			"translations[1][description]" : { required: true },
			service_name : { required: true },
			service_description : { required: true },                
			service_image : { required : {
					depends: function(element){
						if(!$('#service_id').val()){
							return true;
						}else{
							return false;
						}
					}  
				},image_valitation:"png|jpg|jpeg|gif" },                
			mobile_service_image : { required : {
					depends: function(element){
						if(!$('#service_id').val()){
							return true;
						}else{
							return false;
						}
					}  
				},image_valitation:"png|jpg|jpeg|gif" },
			service_type_banner_image : { required : {
					depends: function(element){
						if(!$('#service_id').val()){
							return true;
						}else{
							return false;
						}
					}  
				},image_valitation:"png|jpg|jpeg|gif" },
			has_addon : { required: true },
			status : { required: true },
		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The  Name field is required.'},
			'translations[0][description]': {required :'The Description field is required.'},
			'translations[1][description]': {required :'The Description field is required.'},
			service_name: {required :'The Name field is required.'},
			status: {required :'The Status field is required.'},
			service_image: {required :'The image field is required.'},
			mobile_service_image: {required :'The mobile image field is required.'},
			service_type_banner_image: {required :'The banner image field is required.'},
			has_addon: {required :'The Addon field is required.'},
			description: {required :'The Description field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});

	$scope.multiple_editors = function(index) {
		setTimeout(function() {
			$("#editor_"+index).Editor();
			$("#editor_"+index).parent().find('.Editor-editor').html($('#description_'+index).val());
		}, 100);
	}

	$("[name='submit']").click(function(e){
		$scope.content_update();
	});

	$scope.content_update = function() {
		$.each($scope.translations,function(i, val) {
			console.log($('#editor_'+i).Editor("getText"));
			$('#description_'+i).text($('#editor_'+i).Editor("getText"));
		})
		$('#service_description').text($('#txtEditor').Editor("getText"));
		return false;
	};

	$.validator.addMethod("image_valitation", function(value, element, param) {
		param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
		return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
	}, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));


}]);

app.controller('helpSubCategoryController', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#help_subcategory_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			name : { required: true },
			category_id : { required: true },                                
			status : { required: true },
			description : { required: true },               
			"translations[0][description]" : { required: true },                                
			"translations[1][description]" : { required: true },                                
		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The Name field is required.'},
			'translations[0][description]': {required :'The Description field is required.'},
			'translations[1][description]': {required :'The Description field is required.'},
			name: {required :'The Name field is required.'},
			category_id: {required :'The Category field is required.'},                
			status: {required :'The Status field is required.'},
			description : {required :'The Description field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});
}]);

app.controller('category_language', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

	var $validator = $('#help_category_form').validate({
		rules: {
			"translations[0][locale]" : { required: true },                
			"translations[1][locale]" : { required: true },                
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			name : { required: true },
			type : { required: true },                                
			status : { required: true },               

		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},                
			'translations[1][locale]': {required :'The Language field is required.'},                
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The Name field is required.'},
			name: {required :'The Name field is required.'},
			type: {required :'The Type field is required.'},                
			status: {required :'The Status field is required.'},

		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element ); 
			}
		},
	});
}]);

app.filter('checkKeyValueUsedInStack', ["$filter", function($filter) {
	return function(value, key, stack) {
		var found = $filter('filter')(stack, {locale: value});
		var found_text = $filter('filter')(stack, {key: ''+value}, true);
		return !found.length && !found_text.length;
	};
}])

app.filter('checkActiveTranslation', ["$filter", function($filter) {
	return function(translations, languages) {
		var filtered =[];
		$.each(translations, function(i, translation){
			if(languages.hasOwnProperty(translation.locale))
			{
				filtered.push(translation);
			}
		});
		return filtered;
	};
}]);

app.controller('store', ['$scope','$http','$timeout', function($scope,$http,$timeout) {

	$scope.add_document = function(){
		$scope.all_document.push({ 'document_name': ''});
	};
	
		$scope.document_del=[];
		$scope.delete_document = function(index,id){
			
			$scope.all_document.splice(index, 1);
			var method = 'POST';
			var url = 'delete_documents';
			var FormData = { document_id : id }
			$http({
				method: method,
				url: url,
				data: FormData,
			}).
			success(function(response) {
			}).
			error(function(response) {
			});
			return false;
			$scope.document_del.push(id);
		};


	$('#datepickerdob').datetimepicker({
            format: 'DD-MM-YYYY',
            maxDate: new Date(),
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
         });

	//open time
	$scope.add_open_time = function() {
		$('.text-danger').text('');
		$scope.open_time_timing.push({'day':''});
	};

	$scope.delete_open_time = function(index) {  
		if($scope.open_time_timing.length < 2) {
			return false;     
		}
		$scope.open_time_timing.splice( index, 1 );   
	};

	//preparation time
	$scope.add_preparation_time = function () {
		$scope.preparation_timing.push({id:'',max_time:50});
	};

	$scope.remove_preparation =function(index) {
		$scope.preparation_timing.splice(index, 1);
	};

	$scope.increment =function ($index){ 
		var value = parseInt($scope.preparation_timing[$index].max_time);
		if(value >=55)
		{
			$scope.preparation_timing[$index].max_time = 55;
			return false;
		}
		$scope.preparation_timing[$index].max_time = value+5;
	};

	$scope.decrement =function ($index){ 
		if($scope.preparation_timing[$index].max_time ==5) return false;
		$scope.preparation_timing[$index].max_time -= 5;
	};

	$scope.default_increment =function (){ 
		if($scope.max_time >=55) return false;
		$scope.max_time += 5;
	};

	$scope.default_decrement =function (){ 
		if($scope.max_time ==5) return false;
		$scope.max_time -= 5;
	};

    //Google Place Autocomplete Code
    $("#location_val").keypress(function(e) {
    	$scope.latitude = $scope.longitude = '';
    	$scope.$apply();
    	if (event.keyCode === 13) { 
    		event.preventDefault(); 
    	}
    });

    var autocomplete;
    initAutocomplete();

    function initAutocomplete()
    {
    	autocomplete = new google.maps.places.Autocomplete(document.getElementById('location_val'));
    	autocomplete.addListener('place_changed', fillInAddress);
    }

    function fillInAddress() 
    {
    	fetchMapAddress(autocomplete.getPlace());
    }

    function fetchMapAddress(data)
    {
    	var componentForm = {
    		street_number: 'short_name',
    		route: 'long_name',
    		sublocality_level_1: 'long_name',
    		sublocality: 'long_name',
    		locality: 'long_name',
    		administrative_area_level_1: 'long_name',
    		country: 'short_name',
    		postal_code: 'short_name',
    		administrative_area_level_1: 'long_name',
    	};

    	$scope.postal_code  = '';
    	$scope.city     = '';
    	$scope.latitude   = '';
    	$scope.longitude  = '';


    	var place = data;
    	var street_number ='';
    	for (var i = 0; i < place.address_components.length; i++) 
    	{
    		var addressType = place.address_components[i].types[0];

    		if (componentForm[addressType]) 
    		{
    			var val = place.address_components[i][componentForm[addressType]];
    			if (addressType == 'street_number')
    				street_number = val;
    			if (addressType == 'route')
    				$scope.address_line_1 = street_number + ' ' + val;
    			if (addressType == 'postal_code')
    				$scope.postal_code = val;
    			if (addressType == 'locality')
    				$scope.city = val;  
    			if (addressType == 'administrative_area_level_1')
    				$scope.state = val;   
    			if (addressType == 'country')
    				$scope.country_code = val;
    		}
    	}

    	$scope.latitude  = place.geometry.location.lat();
    	$scope.longitude = place.geometry.location.lng();
    	$scope.is_auto_complete = 1;
    	$scope.$apply();
    }   

    $('#location_val').keyup(function(){
    	$scope.is_auto_complete = '';
    });

	$(document).ready(function() {
		$scope.checkItem($scope.service_type);
	});	

	$scope.checkItem = function(service_type) {
	    $scope.cusine = '';
	    var url = getUrl('category_type');
	    $('.category_div').addClass('loading'); 
		$.ajax({
		    type: 'GET',
		    url: url,
		    data: { service_type_id: service_type},
	    	dataType: "json",
		    success: function(response) {
		    	if(response) {
		    		$('.category_div').removeClass('loading');
			   	 	$scope.cusine = response.value;
			    	$scope.$apply();
			    	$('#category').find('option').remove();
		    	}	
		    }
		});
	};

 $scope.isChecked =function(category) {
 	return $scope.selected_cuisines.findIndex(x => x == category) != -1;
 }

}]);

app.controller('driver_management', ['$scope','$http','$timeout', function($scope,$http,$timeout) {
	$('#datepickerdob').datetimepicker({
	    format: 'DD-MM-YYYY',
	    maxDate: new Date(),
	    useCurrent: false,
	    icons: {
	        time: "fa fa-clock-o",
	        date: "fa fa-calendar",
	        up: "fa fa-chevron-up",
	        down: "fa fa-chevron-down",
	        previous: 'fa fa-chevron-left',
	        next: 'fa fa-chevron-right',
	        today: 'fa fa-screenshot',
	        clear: 'fa fa-trash',
	        close: 'fa fa-remove'
	    },
   	 });
}]);


app.filter('checkKeyValueUsedInStack', ["$filter", function($filter) {
	return function(value, key, stack) {
		var found = $filter('filter')(stack, {[key]: value});
		var found_text = $filter('filter')(stack, {[key]:''+value}, true);
		return !found.length && !found_text.length;
	};
}]);

app.controller('vehicleController', ['$scope','$http','$timeout','$filter','fileUploadService',function($scope,$http,$timeout,$filter,fileUploadService) {

	var $validator = $('#vehicle_form').validate({

		rules: {
			"translations[0][locale]" : { required: true },
			"translations[1][locale]" : { required: true },
			"translations[0][name]" : { required: true },
			"translations[1][name]" : { required: true },
			name : { required: true },
			status : { required: true },
			vehicle_image: {
				required : {
					depends: function(element){
						$formType = $('.form_type').val();
						if($formType == 'add'){
							return true;
						}else{
							return false;
						}
					}  
				},
				image_valitation:"png|jpg|jpeg|gif"
			},                                    
		},
		messages: {
			'translations[0][locale]': {required :'The Language field is required.'},
			'translations[1][locale]': {required :'The Language field is required.'},
			'translations[0][name]': {required :'The Name field is required.'},
			'translations[1][name]': {required :'The Name field is required.'},
			name: {required :'The Name field is required.'},
			status: {required :'The Status field is required.'},
		},
		errorElement: "span",
		errorClass: "text-danger ng-binding",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			}
			else {
				label.insertAfter( element ); 
			}
		},
	});

	$.validator.addMethod("image_valitation", function(value, element, param) {

		param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
		return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
	}, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));


}]);

app.controller('menu_editor', ['$scope','$http','$timeout','$filter','fileUploadService',function($scope,$http,$timeout,$filter,fileUploadService) {

	var $validator = $('.form_valitate').validate({
		rules: {
			menu_item_name: { required: true },
			menu_item_desc: { required: true },
			menu_item_price: { required: true,number:true,maxlength:7},
			menu_item_tax: { required: true,number:true,maxlength:7,max:100 },
			item_status: { required: true },
			item_image: { image_valitation:"png|jpg|jpeg|gif"},
			"menu_item_translations[0][locale]":{ required: true },
			"menu_item_translations[1][locale]":{ required: true },
			"menu_item_translations[0][name]":{ required: true },
			"menu_item_translations[1][name]":{ required: true },
			"menu_item_translations[0][description]":{ required: true },
			"menu_item_translations[1][description]":{ required: true },

		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element );
			}
		},
	});

	$.validator.addMethod("image_valitation", function(value, element, param) {
		param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
		return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
	}, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));

	var $validator = $('.update_menu_time').validate({
		rules: {
			menu_name: { required: true },
			"menu_timing_day[]": { required: true },
			"menu_timing_start_time[]": { required: true,greate_then:true },
			"menu_timing_end_time[]": { required: true },
			"translations[0][locale]":{ required: true },
			"translations[1][locale]":{ required: true },
			"translations[0][name]":{ required: true },
			"translations[1][name]":{ required: true },
		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			if(element.attr( "name" ) === "menu_timing_day[]" || element.attr( "name" ) === "menu_timing_start_time[]" || 
				element.attr( "name" ) === "menu_timing_end_time[]") {
				element.parent().parent().append( label );
			}
			else {
				if(element.attr( "data-error-placement" ) === "container" ){
					container = element.attr('data-error-container');
					$(container).append(label);
				} else {
					label.insertAfter( element );
				}
			}
		},
	});

	$.validator.addMethod('greate_then', function(value, element, param) {
		var end_time = $(element).attr('data-end_time');
		var start_time = $(element).val();
		if(end_time) {
			return start_time < end_time;
		}
		else {
			return 'false';
		}
	}, 'The start time should be less than the end time');

	$('#category_add_form').validate({
		rules: {
			category_name: {required: true},
			"category_translations[0][locale]": { required: true },
			"category_translations[1][locale]": { required: true },
			"category_translations[0][name]": { required: true },
			"category_translations[1][name]": { required: true },
		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element );
			}
		},
	});

	$('#category_edit_form').validate({
		rules: {
			category_name: {required: true},
			"category_translations[0][locale]": { required: true },
			"category_translations[1][locale]": { required: true },
			"category_translations[0][name]": { required: true },
			"category_translations[1][name]": { required: true },
		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element );
			}
		},
	});

	$scope.initToggleBar = function() {
		$scope.menu_item_index = null;
		$scope.category_index = null;
		if(!$scope.$$phase) {
			$scope.$apply();
		}
	};

	$scope.select_menu = function(index) {
		if($scope.menu_index==='' || $scope.menu_index===null)
			$scope.menu_index = index;
		else{
			if($scope.menu_index==index)
				$scope.menu_index = null;
			else
				$scope.menu_index = index;
			$scope.category_index = null;
			$scope.menu_item_index = null;
		}
	};

	$scope.category = function(index, menu_index) {
		$scope.category_index = index;
		$scope.menu_index = menu_index;
		$scope.menu_item_index = null;

		/*var modifierList = _.map($scope.menu, function(menu) {
			return _.map(menu.menu_category, function(menu_category) {
				return _.map(menu_category.menu_item, function(menu_item) {
					return _.map(menu_item.menu_item_modifier, function(item) {
						return { label: item.name, key: item.id };
					});
				});
			});
		});*/

		var modifierList = [];

		$scope.menu.forEach(function(menu) {
			menu.menu_category.forEach(function(menu_category) {
				menu_category.menu_item.forEach(function(menu_item) {
					menu_item.menu_item_modifier.forEach(function(item) {
						var menu_item_modifier_item = _.map(item.menu_item_modifier_item, function(modifier_item) {
							return { name:modifier_item.name,price:modifier_item.price };
						});

						current_modifier = {
							count_type : item.count_type,
							is_multiple : item.is_multiple,
							is_required : item.is_required,
							min_count : item.min_count,
							max_count : item.max_count,
							menu_item_modifier_item : menu_item_modifier_item,
							name : item.name
						};

						modifierList.push({ label: item.name, key: item.id,current_modifier : current_modifier });
					});
				});
			});
		});

		$("#modifier_input").autocomplete({        
			source: modifierList,
			select: function(event, ui) {
				$scope.modifier_input = ui.item.label;
				$scope.selected_modifier = ui.item.current_modifier;
				$scope.applyScope();
				return false;
			}
		});
	};

	$scope.select_menu_item = function(index) {
		$('#myFileField').val('');
		$scope.menu_item_index = index;
		$scope.menu_item_details = $scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index];
		;
		$scope.menu_item_translations = $scope.menu_item_details.translations;
	};

	$scope.add_category = function(menu_id,name){
		$scope.isDisabled = false;
		$('.text-danger').text('');
		$scope.menu_id = menu_id;
		$scope.category_name = name;
		$scope.category_translations = [];
	};

	$scope.edit_category = function(id,name,menu){
		$scope.isDisabled = false;
		$('.text-danger').text('');
		var translations;
		$scope.menu_id = '';
		$scope.category_name = name;
		$scope.category_id = id;
		translations = menu.translations;
		if (typeof menu.translations === 'undefined') {
			translations = [];
		}
		$scope.category_translations = translations;
	};

	
	$(document).ready(function(){
		//Get remaing categoy for each menu
		$scope.menu.forEach(function(menu,key) {
			if(menu.total_category > 1){
				for (var i=2;  i<= menu.total_category; i++) {
					let url 		= getUrl('get_category');
					let store_id 	= $scope.ori_store_id;
					let FormData 	= { store_id : store_id, menu_id : menu['menu_id'], page : i};
					$http({ method: 'POST', url: url, data: FormData}).success(function(response){
						if(response.menucategory){
							$scope.menu[key].menu_category = [...$scope.menu[key].menu_category, ...response.menucategory];
						}
					});
				}
			}
			if($scope.menu.length == key + 1){
				setTimeout(function(){
					checkCategory();
				},400)
			}
		});
		
	});	

	//Get category based remaing menu item
	function checkCategory(){
		$scope.menu.forEach(function(menu,key) {
			if(menu.menu_category){
				menu.menu_category.forEach(function(category,c_key){
					if(category.total_item > 1){
						$scope.getItem(c_key,category.menu_category_id,category.total_item,key);
					}
				});
			}
		});
	}
	
	$scope.getItem = async function ($index, $category_id, $total_item, $menu_index)
	{
		for (var i=2;  i<= $total_item; i++) {
			let url 		= getUrl('get_item');
			let FormData 	= { category_id : $category_id, page : i};
			await $http({ method: 'POST', url: url, data: FormData}).success(function(response){
				if(response.item){
					$scope.menu[$menu_index].menu_category[$index].menu_item = [...$scope.menu[$menu_index].menu_category[$index].menu_item, ...response.item];
				}
			});
		}
	};
	

	// $scope.isDisabled = false;	
	$scope.save_category = function(action) {
		if($scope.category_name != undefined && $scope.category_name.length !=0 ){
			$scope.isDisabled = true;
		}
		if(action == 'add'){
			var $valid = $('#category_add_form').valid();
			if (!$valid) {
				$validator.focusInvalid();
				return false;
			}
		}
		else{
			var $valid = $('#category_edit_form').valid();
			if (!$valid) {
				$validator.focusInvalid();
				return false;
			}
		}
		var method = 'POST';
		var url = getUrl('update_category');
		var FormData = { store_id:$scope.ori_store_id, 'name' : $scope.category_name ,'id' : $scope.category_id,'action' : action , 'menu_id' : $scope.menu_id, 'locale': $scope.locale};
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.item_all_details').removeClass('loading');
			if(action=='edit'){
				$('#sub_edit_modal').modal('toggle');
				var getMenu = response.translations;
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_category = response.category_name;
				
			}
			else {
				$('#add_modal').modal('toggle');
				$scope.menu[$scope.menu_index].menu_category.push({
					'menu_category_id' : response.category_id,
					'menu_category' : response.category_name,
					'menu_item' : [],
					'translations' : response.translations
				});
			}
		}).
		error(function(response) {
			$('.item_all_details').removeClass('loading');
			$scope.codeStatus = response || "Request failed";
		});
		return false;
	};

	$scope.menu_time = function(index,menu_id,name){ 
		$(".menubtnDisbale").attr("disabled", false);
		$('.text-danger').text('');
		$scope.menu_index = index;
		$scope.menu_id = menu_id;
		var method = 'GET';
		var url = getUrl('menu_time',{'id':menu_id});  
		var FormData = { store_id:$scope.store_id};
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			var getMenu = response.translations;
			$scope.translations = getMenu[0].translations;
			$scope.menu_timing = response.menu_time;
			$scope.menu_name = name;
			$('.item_all_details').removeClass('loading');
		}).
		error(function(response) {
		});
		return false;
	};

	$scope.add_menu_pop = function () {
		$(".menubtnDisbale").attr("disabled", false);
		$scope.menubtnDisbale=false;
		$scope.menu_timing = [];
		$scope.menu_name = "";
		$scope.menu_index = null;
		$scope.menu_id = null;
		$scope.translations = [];
		$('.text-danger').text('');
	};

	$scope.add_menu_time = function () {
		$scope.menu_timing.push({id:''});
	};

	$scope.remove_menu_time= function(item,id) {
		$scope.menu_timing.splice(item, 1);
		$('.add_loading').addClass('loading');
		var method = 'GET';
		var url = 'remove_menu_time/'+id;
		var FormData = { store_id:$scope.store_id};

		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.add_loading').removeClass('loading');
		}).
		error(function(response) {
			$('.add_loading').removeClass('loading');
		});
		return false;
	};

	$scope.update_menu_time = function() {
		var $valid = $('.update_menu_time').valid();
		if (!$valid) {
			$validator.focusInvalid();
			return false;
		}
		var method = 'POST';
		var url = getUrl('update_menu_time');
		var FormData = { store_id:$scope.ori_store_id , menu_time : $scope.menu_timing,menu_id : $scope.menu_id ,menu_name : $scope.menu_name,locale:$scope.locale }
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.item_all_details').removeClass('loading');
			$('#edit_modal').modal('toggle');
			$(".menubtnDisbale").attr("disabled", false);
			if(response.message == false) {
				return false;
			}
			if($scope.menu_id) {
				$scope.menu[$scope.menu_index].menu = response.menu_name;
			}
			else {
				$scope.menu = response.menu;
			}

		}).
		error(function(response) {
			$('.item_all_details').removeClass('loading');
		});
		return false;
	};

	$scope.update_item = function() {
		$scope.modifier_error = '';
		$('.item_all_details').addClass('loading');
		var $valid = $('.form_valitate').valid();
		if (!$valid) {
			$validator.focusInvalid();
			$('.item_all_details').removeClass('loading');
			return false;
		}
		var method = 'POST';
		var uploadUrl = getUrl('update_menu_item');
		var file =document.getElementById('myFileField').files[0]; 
		var FormData = {menu_item : $scope.menu_item_details}
		promise = fileUploadService.uploadFileToUrl(file, uploadUrl,{ menu_item : $scope.menu_item_details },$scope.user_id,$scope.locale,$scope.menu_item_details.menu_item_modifier);

		promise.success(function(response) {
			$('.item_all_details').removeClass('loading');
			if(response.status == false) {
				$scope.modifier_error = response.status_message;
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index] = response.menu_item;
				$scope.menu_item_details = $scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index];
				return false;
			}
			if(response.menu_item_id) {
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.new_menu_item_index] = response;
			}

			if(response.edit_menu_item_image) {
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index].item_image=response.edit_menu_item_image;
			}

			$scope.menu_item_index = null;
			$scope.new_menu_item_index = null;
		}).
		error(function(response) {
		});
		return false;
	};

	$scope.add_new_item = function() {
		if($scope.new_menu_item_index == null) {
			$scope.menu_item_index = $scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item.length;
		}
		else {
			$scope.menu_item_index = $scope.new_menu_item_index;
		}
		$scope.new_menu_item_index = $scope.menu_item_index;

		$scope.menu_item_details = {
			'menu_item_id' : '',
			'menu_item_name' : '',
			'menu_item_desc' : '',
			'menu_item_price' :'',
			'menu_item_tax_percentage' :'' ,
			'menu_item_type' :'' ,
			'menu_item_status' :'' ,
			'item_image' : null,
			'menu_id' : $scope.menu[$scope.menu_index].menu_id,
			'category_id' : $scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_category_id,
			'menu_item_modifier' : [],
		};

		$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index] = $scope.menu_item_details;

		$('#myFileField').val('');
		$('#file_text').text('');
		$('span.upload_text').removeAttr('title');
		$('#chooses_file').removeAttr("style");
		$('#banner_name').text(Lang.get('js_messages.file.choose_file'));
		$scope.myFile='';
		$scope.menu_item_translations= [];
	};

	$scope.set= function(set_id,name) {
		$('.delete_item_msg').text('');
		$scope.remove_id = set_id;
		$scope.delete_name = name;
		if(name=='menu') {
			$scope.menu_index = set_id;
		}
		if(name == 'item') {
			$scope.modifier_input = '';
		}
		$scope.myFile = '';
	};

	$scope.remove_item= function(item,$text) {
		var item = item;
		$('.add_loading').addClass('loading');
		var method = 'POST';
		var url = getUrl('delete_menu');
		var FormData = {category_index:$scope.category_index,store_id:$scope.store_id,menu : $scope.menu[$scope.menu_index],category : $text ,key : item }

		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.add_loading').removeClass('loading');
			$('.delete_item_msg').text('');
			if(response.status=='false') {
				$('.delete_item_msg').text(response.status_message);
				$('#delete_error_modal').modal();
				return false;
			}
			$('#delete_modal').modal('hide');
			if($text=='menu')
			{
				$scope.menu.splice(item, 1);
				$scope.category_index = null;
				$scope.menu_item_index = null;
				$scope.menu_index = null;
			}
			else if($text=='category') {
				$scope.menu[$scope.menu_index].menu_category.splice(item, 1);
				$scope.category_index = null;
			}
			else {
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item.splice(item, 1);
				$scope.menu_item_index = null;
			}
		}).
		error(function(response) {
			$('.add_loading').removeClass('loading');
		});
		return false;
	};

	$scope.add_preparation_time = function () {
		$scope.menu_timing.push({id:''});
	};

	// Modifier Related Function Start
	$scope.changeLocale = function() {
		$('.add_loading').addClass('loading');
		$scope.locale = $scope.trans_language;
		var url = getUrl('menu_locale');
		var data = {store_id : $scope.ori_store_id,locale:$scope.locale};

		$http.post(url,data).then(function(response) {
            if(response.status == 200) {
                $scope.current_language = $scope.getCurrentLanguage();
                $scope.min_item_error = false;
                $scope.menu = response.data.menu;
				$scope.applyScope();
				$('.add_loading').removeClass('loading');
            }
        }, function(response) {
            if(response.status == '300') {
                window.location = APP_URL + '/store';
            }
            else if(response.status == '500') {
                window.location.reload();
            }
        });
	};

	$scope.isEmpty = function(value) {
		return (value == undefined || value == '');
	};
	
	$.validator.addMethod( "greaterThan", function ( value, element, param ) {
	    var target = $(param);

	    if ( this.settings.onfocusout && target.not( ".validate-greaterThan-blur" ).length ) {
	        target.addClass( "validate-greaterThan-blur" ).on( "blur.validate-greaterThan", function () {
	            $(element).valid();
	        });
	    }
	    
	    var min_count = ($scope.isEmpty(target.val())) ? -1 : target.val();
	    return parseInt(value) > parseInt(min_count);
	}, Lang.get('js_messages.store.enter_greater_value'));

	$.validator.addMethod("decimalValue", function (value, element) {
        return this.optional(element) || /^\d{0,4}(\.\d{0,2})?$/i.test(value);
    }, "Please Enter Valid Item Price");

	// Code for the Validator
	var $update_menu_modifier = $('.update_menu_modifier').validate({
		onkeyup: false,
		onclick: false,
		onfocusout: false,
		rules: {
			menu_name: { required: true },
			count_type: { required: true,number:true,maxlength:7},
			min_count: { required: true },
			max_count: { required: true,greaterThan: "#min_count"},
			is_multiple: { required: false},
			is_required: { required: true },
			"item_modifer_name[]": { required: true },
			"item_modifer_price[]": { number: true },
		},
		messages: {
			menu_name: { required: Lang.get('js_messages.store.please_enter_all_required_fields') },
			count_type: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			min_count: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			max_count: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			is_multiple: { number:Lang.get('js_messages.store.please_enter_all_required_fields')},
			is_required: { number:Lang.get('js_messages.store.please_enter_all_required_fields')},
			"item_modifer_name[]": { required: Lang.get('js_messages.store.please_enter_all_required_fields') },
		},
		showErrors : function(errorMap, errorList) {
			if(errorList[0] != undefined) {
				$('.required-error').text(errorList[0].message).show();
			}
		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			/*if(element.attr( "data-error-placement" ) === "container" ) {
				container = element.attr('data-error-container');
				$(container).append(label);
			}
			else {
				label.insertAfter( element );
			}*/
		},
	});

	$scope.openModifierPopup = function(index = '',selected_modifier = '',name = '') {
		$('#add_modifier_modal').modal('show');
		$scope.modifier_index = index;
		if(selected_modifier != '') {
			$scope.current_modifier = angular.copy(selected_modifier);
			return true;
		}
		if(index === '') {
			$scope.current_modifier = {'count_type':'0','is_multiple' : 0};
			$scope.current_modifier.menu_item_modifier_item = [];
			$scope.current_modifier.menu_item_modifier_item.push({id:''});
		}
		else {
			$scope.current_modifier = angular.copy($scope.menu_item_details.menu_item_modifier[index]);
		}
		if(name != '') {
			$scope.current_modifier.name = name;
		}
	};

	$scope.addModifierPopup = function() {
		if(typeof $scope.selected_modifier != 'undefined') {
			var length = Object.keys($scope.selected_modifier).length;
			if(length > 0) {
				$scope.openModifierPopup('',$scope.selected_modifier);
				return true;
			}
		}
		$scope.openModifierPopup('','',$scope.modifier_input);
	};

	$scope.resetSelectedModifier = function() {
		$scope.selected_modifier = {};
	};

	$scope.addItemModifierItem = function() {
		$scope.current_modifier.menu_item_modifier_item.push({id:''});
	};

	$scope.removeItemModifierItem = function(index) {
		$scope.current_modifier.menu_item_modifier_item.splice(index,1);
	};

	$scope.updateModifier = function(index) {
		var $valid = $('#update_menu_modifier').valid();
		$scope.min_item_error = false;
		if (!$valid) {
			$update_menu_modifier.focusInvalid();
			return false;
		}
		$('.required-error').hide();
		// Validate Modifier Item based on min_count, max_count and is_multiple
		if($scope.current_modifier.max_count > $scope.current_modifier.menu_item_modifier_item.length) {
			$scope.min_item_error = true;
			$scope.applyScope();
			return false;
		}

		if(index === '') {
			$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index].menu_item_modifier.push($scope.current_modifier);
		}
		else {
			$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index].menu_item_modifier[index] = $scope.current_modifier;
		}
		$('#add_modifier_modal').modal('hide');
	};

	$scope.removeModifier = function(index) {
		$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index].menu_item_modifier.splice(index,1);
	};

	$scope.updateRequiredInfo = function() {
		if($scope.current_modifier.count_type == 0) {
			$scope.current_modifier.min_count = '';
			if($scope.current_modifier.max_count == 0) {
				$scope.current_modifier.is_required = 1;
				var temp_modifier_items = $scope.current_modifier.menu_item_modifier_item[0];
				$scope.current_modifier.menu_item_modifier_item = [];
				$scope.current_modifier.menu_item_modifier_item.push(temp_modifier_items);
			}
			if($scope.current_modifier.max_count < 2) {
				$scope.current_modifier.is_multiple = 0;
			}
		}		
	};

	$scope.getCurrentLanguage = function() {
		var index = $scope.language_list.findIndex(x => x.value == $scope.locale);
		return $scope.language_list[index].name;
	};

	$scope.applyScope = function() {
		if(!$scope.$$phase) {
			$scope.$apply();
		}
	};

	$('#add_modifier_modal').on('hidden.bs.modal', function () {
		$('.required-error').hide();
		$scope.min_item_error = false;
	});
}]);

app.service('fileUploadService', function ($http, $q) {
	this.uploadFileToUrl = function (file, uploadUrl,data,user_id,locale,item_modifiers) {
		var fileFormData = new FormData();
		fileFormData.append('file', file);
		fileFormData.append('store_id', user_id);
		fileFormData.append('locale', locale);
		fileFormData.append('item_modifiers',JSON.stringify(item_modifiers));
		if(data) {
			$.each(data, function(i, v){
				$.each(v, function(j, k){
					fileFormData.append(j, k);
				})
			})
		}
		return $http({
			url: uploadUrl,
			method: 'POST',
			data: fileFormData,
			headers: { 'Content-Type': undefined},
			transformRequest: angular.identity
		});
	}
});

$(document).ready(function () {
	$(function () {
		$('[data-toggle="tooltip"]').tooltip();
		$('[data-toggle="popover"]').popover();
	});

	$(document).mouseup(function(e) {
		var container = $(".tooltip-content");
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
		}
	});

	$(document).on('click', '.tooltip-link', function () {
		var pos = $(this).position();
		$(this).find('.tooltip-content').toggle();
	});
	$('.menu-name .icon-pencil-edit-button').click(function () {
		$('.menu-name input').prop('readonly', false);
	});

	$("#payout_preference").on('show.bs.modal', function(e) {
	   	var payout_details = $(e.relatedTarget).data('payout-details');
		var inHTML = "";

		$.each(payout_details, function(key, value) {
			if(value != '') {
		    	inHTML += "<tr><td>"+ key + "</td><td>"+ value + "</td></tr>"
			}
		});

		$("#payout_details").html(inHTML);
	});
});


app.filter('checkKeyValueUsedInStack', ["$filter", function($filter) {
	return function(value, key, stack) {
		var found = $filter('filter')(stack, {[key]: value});
		var found_text = $filter('filter')(stack, {[key]:''+value}, true);

		return !found.length && !found_text.length;
	};
}]);


app.controller('send_message', ['$scope', '$http', function($scope, $http) {
	$(document).ready(function(){
		console.log($scope.users_list);
		$scope.getUserList();
	});

	$scope.getUserList = async function()
	{
		for (var i=2;  i<= $scope.last_page; i++) {
			let url = getUrl('get_user_list');
			await $http({ method: 'POST', url: url, data: { page : i}}).success(function(response){
				$scope.users_list = [...$scope.users_list, ...response.users_list];
				if(!$scope.$$phase) {
					$scope.$apply();
				}
			});
			$('.selectpicker').selectpicker('refresh');
		}
		$('.selectpicker').selectpicker('refresh');
	};

}]);

