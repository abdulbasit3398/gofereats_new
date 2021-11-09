// Code for the Validator
$('#choose_files').click(function() {
	$('#document').trigger('click');
});

$('#additional_choose_files').click(function(){
	$('#additional_document').trigger('click');
});

var store_valitAate = $("#signup_form").validate({
	ignore: ':hidden:not(.do-not-ignore)',
	rules: {
		name:{ required:true },
		address:{ required:true },
		first_name:{ required:true },
		last_name:{ required:true },
		city:{ required:true },
		mobile_number:{ required:true,minlength:6,number:true},
		email:{ required:true,email:true },
		service_type:{ required:true },
		password: { required:true,minlength:6 },
		conform_pasword: {
			equalTo: "#password"
		}
	},
	messages: {
		'name' : {  required : Lang.get('js_messages.store.name_of_the_store')},
		'address' : {  required : Lang.get('js_messages.store.store_address')},
		'first_name' : {  required :  Lang.get('js_messages.store.first_name')},
		'last_name' : {  required :  Lang.get('js_messages.store.last_name')},
		'mobile_number' : {  required : Lang.get('js_messages.store.phone_number'),minlength:Lang.get('js_messages.store.please_enter_at_least_characters'),number:Lang.get('js_messages.store.please_enter_valid_number')},
		'email' : {  required : Lang.get('js_messages.store.email_address') , email : Lang.get('js_messages.store.valid_email_address')},
		'service_type' : {  required : Lang.get('js_messages.store.please_select_service_type')},
		'city' : {  required : Lang.get('js_messages.store.city')},
		'password' : {  required : Lang.get('js_messages.store.password'),minlength : Lang.get('js_messages.store.please_enter_at_least_characters')},
		'conform_pasword': {  equalTo : Lang.get('js_messages.store.conform_pasword'),minlength : Lang.get('js_messages.store.please_enter_at_least_characters')},
	},
	errorElement: "span",
	errorClass: "text-danger",
	errorPlacement: function( label, element ) {
		console.log(element);
		if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element );
			}
	}
});
// Code for the Validator
var $menu_item_validator = $('.form_valitate1').validate({
	rules: {
		menu_item_name: { required: true },
		menu_item_price: { required: true,number:true,maxlength:7},
		menu_item_tax: { number:true,maxlength:7,max:100 },
		item_status: { required: true },
		item_image: {
			required: { depends: function(element){
				if($(element).attr('data')!='null'){
					return false;
				}
				else{
					return true;
				}
			} },images_size_check : "10",document_valitation:"png|jpg|jpeg|pdf" }
		},
		messages: {
			menu_item_name: { required: Lang.get('js_messages.store.field_required') },
			menu_item_price: { required: Lang.get('js_messages.store.field_required'),number:Lang.get('js_messages.store.please_enter_valid_number'),maxlength:Lang.get('js_messages.store.please_enter_no_more_than_characters')},
			menu_item_tax: { number:Lang.get('js_messages.store.field_required'),maxlength:Lang.get('js_messages.store.please_enter_no_more_than_characters'),max:Lang.get('js_messages.store.please_enter_value_less_than_or_equal') },
			item_status: { required: Lang.get('js_messages.store.field_required') },
			item_image:  {  required : Lang.get('js_messages.store.field_required')},
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
// Code for the Validator
var $menu_time_validator = $('.update_menu_time1').validate({
	rules: {
		menu_name: { required: true },
		"menu_timing_day[]": { required: true },
		"menu_timing_start_time[]": { required:true,greate_then:true },
		"menu_timing_end_time[]": { required: true },
	},
	messages: {
		menu_name : {  required : Lang.get('js_messages.store.field_required')},
		'menu_timing_day[]': {  required : Lang.get('js_messages.store.field_required')},
		'menu_timing_start_time[]': {  required : Lang.get('js_messages.store.field_required') , greate_then : Lang.get('js_messages.store.start_time_should_less_than_end_time')},
		'menu_timing_end_time[]':{  required : Lang.get('js_messages.store.field_required')}
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
// Code for the store_preparation_time Validator
var $preparation_time_validator = $('#store_preparation_time').validate({
	rules: {
		"max_time[]": { required: true },
		"day[]": { required: true },
		"from_time[]": { required: true,greate_then:true },
		"to_time[]": { required: true },
		"status[]": { required: true },
	},
	messages: {
		'max_time[]': {  required : Lang.get('js_messages.store.field_required')},
		'day[]' : {  required : Lang.get('js_messages.store.field_required')},
		'from_time[]': {  required : Lang.get('js_messages.store.field_required'),greate_then : Lang.get('js_messages.store.start_time_should_less_than_end_time')},
		'to_time[]': {  required : Lang.get('js_messages.store.field_required')},
		'status[]':{  required : Lang.get('js_messages.store.field_required')}
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
// Code for the Validator
$('#profile_form').validate({
	rules: {
		first_name: { required: true },
		last_name: { required: true },
		email: { required: true, email: true },
		phone_code: { required: true },
		mobile_number: { required: true,number:true,minlength:6},
		dob: { required: true },
		store_name: { required: true },
		description: { required: true },
		price_rating: { required: true },
		'cuisine[]': { required: true },
		type: { required: true },
		address: { required: true },
		banner_image: { image_valitation:"png|jpg|jpeg|gif"},
	},
	messages: {
		'store_name' : {  required : Lang.get('js_messages.store.field_required')},
		'first_name' : {  required :  Lang.get('js_messages.store.field_required')},
		'last_name' : {  required :  Lang.get('js_messages.store.field_required')},
		'email' : {  required : Lang.get('js_messages.store.field_required') , email : Lang.get('js_messages.store.valid_email_address')},
		'phone_code' : {  required : Lang.get('js_messages.store.field_required')},
		'dob' : {  required : Lang.get('js_messages.store.field_required')},
		'description' : {  required : Lang.get('js_messages.store.field_required')},
		'type' : {  required : Lang.get('js_messages.store.field_required')},
		'price_rating' : {  required : Lang.get('js_messages.store.field_required')},
		'cuisine[]' : {  required : Lang.get('js_messages.store.field_required')},
		'address' : {  required : Lang.get('js_messages.store.field_required')},
		'mobile_number' : {  required : Lang.get('js_messages.store.field_required'),number:Lang.get('js_messages.store.please_enter_valid_number'),minlength:Lang.get('js_messages.store.please_enter_at_least_characters')},
		'banner_image' : {  image_valitation : Lang.get('js_messages.store.please_upload_images_like_file_only')},
	},
	errorElement: "span",
	errorClass: "text-danger",
	errorPlacement: function( label, element ) {
		if(element.attr( "data-error-placement" ) === "container" ){
			if($(element).parent().hasClass("mobile-error"))
			{
				label.insertAfter($('.mobile-error'));
			}
			else
				label.insertAfter($('.cuisine_error'));
		} else {
			label.insertAfter( element );
		}
	},
});
var $open_time_validator = $('#open_time_form').validate({
	rules: {
		"day[]": { required: true },
		"start_time[]": { required: true,greate_then:true },
		"end_time[]": { required: true },
		"status[]": { required: true },
	},
	messages: {
		'day[]' : {  required : Lang.get('js_messages.store.field_required')},
		'start_time[]' : {  required : Lang.get('js_messages.store.field_required'),greate_then : Lang.get('js_messages.store.start_time_should_less_than_end_time')},
		'end_time[]' : {  required : Lang.get('js_messages.store.field_required')},
		'status[]' : {  required : Lang.get('js_messages.store.field_required')},
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
var $document_validator = $('#store_documents').validate({
	rules: {
		"document_name[]": { required: true },
		"document_file[]": {
			required: { depends: function(element){
				if($(element).attr('data')!='null'){
					return false;
				}
				else{
					return true;
				}
			} },images_size_check : "10",document_valitation:"png|jpg|jpeg|pdf" }
		},
		messages: {
			'document_name[]' : {  required : Lang.get('js_messages.store.field_required')},
			'document_file[]' : {  required : Lang.get('js_messages.store.field_required')},
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
}, Lang.get('js_messages.store.start_time_should_less_than_end_time'));
$.validator.addMethod('custom_required', function(value, element, param) {
	var validate_confirm = $(element).attr('data-rule-required');
	var value = $(element).val();
	if(validate_confirm) {
		return value!='';
	}
	else {
		return true;
	}
}, Lang.get('js_messages.store.field_required'));
$.validator.addMethod("image_valitation", function(value, element, param) {
	param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, $.validator.format(Lang.get('js_messages.store.please_upload_images_like_file_only')));
$.validator.addMethod("document_valitation", function(value, element, param) {
	param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, $.validator.format(Lang.get('js_messages.store.please_upload_file_like_format')));
$.validator.addMethod("images_size_check", function(value, element, params) {
	files = element.files;
	if(files.length > 0)
	{
		if(files[0].size >10240000)
		{
			return false;
		}
		return true;
	}
	else
	{
		return true;
	}
}, $.validator.format(Lang.get('js_messages.store.document_file_may_not_greater')));
/*
A directive to enable two way binding of file field
*/
app.directive('demoFileModel', function ($parse) {
	return {
restrict: 'A', //the directive can be used as an attribute only
/*
link is a function that defines functionality of directive
scope: scope associated with the element
element: element on which this directive used
attrs: key value pair of element attributes
*/
link: function (scope, element, attrs) {
	var model = $parse(attrs.demoFileModel),
modelSetter = model.assign; //define a setter for demoFileModel
//Bind change event on the element
element.bind('change', function () {
//Call apply on scope, it checks for value changes and reflect them on UI
scope.$apply(function () {
//set the model value
modelSetter(scope, element[0].files[0]);
});
});
}
};
});
app.service('fileUploadService', function ($http, $q) {
	this.uploadFileToUrl = function (file, uploadUrl,data,locale,item_modifiers) {
		// FormData, object of key/value pair for form fields and values
		var fileFormData = new FormData();
		fileFormData.append('locale', locale);
		fileFormData.append('file', file);
		fileFormData.append('item_modifiers',JSON.stringify(item_modifiers));
		if(data){
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

app.controller('store_signup', ['$scope','$http','$timeout', function($scope,$http,$timeout) {
	$('#signup_form').submit(function(){
		if ($('#country_code').val()=='' && $('#location_val').val()!='') {
			$('.location_error').text(Lang.get('js_messages.store.please_select_from_google_autocomplete'));
			return false;
		}
		$('.location_error').text('');
	})
	var autocomplete;
	initAutocomplete();
	function initAutocomplete()
	{
		autocomplete = new google.maps.places.Autocomplete(document.getElementById('location_val'),{types: ['geocode']});
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
		$('#postal_code').val('');
		$('#city').val('');
		$('#latitude').val('');
		$('#longitude').val('');
		$('#country_code').val('');
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
		$('#country_code').val($scope.country_code);
		$('#postal_code').val($scope.postal_code);
		$('#city').val($scope.city);
		$('#state').val($scope.state);
		$('#address_line_1').val($scope.address_line_1);
		$('#latitude').val($scope.latitude) ;
		$('#longitude').val($scope.longitude) ;
		$scope.is_auto_complete = 1;
		$scope.$apply();
		if($scope.country_code!='') {
			$('.location_error').text('');
		}
	}
	$('#location_val').keyup(function(){
		$scope.is_auto_complete = '';
	});
	//min and max preparation time validation
	$('#min_time').change(function() {
		var min_time = $('#min_time').val();
		var max_time = $('#max_time').val();
		if(min_time>=max_time){
			$('#min_error').css('display','block');
			$('#profile_save').attr('disabled','disabled');
		}
		else{
			$('#min_error').css('display','none');
			$('#profile_save').removeAttr('disabled');
		}
	});
	$('#max_time').change(function(){
		var min_time = $('#min_time').val();
		var max_time = $('#max_time').val();
		if(min_time>=max_time){
			$('#max_error').css('display','block');
			$('#profile_save').attr('disabled','disabled');
		}
		else{
			$('#max_error').css('display','none');
			$('#profile_save').removeAttr('disabled');
		}
	})
}]);



app.filter('checkKeyValueUsedInStack', ["$filter", function($filter) {
	return function(value, key, stack) {
		var found = $filter('filter')(stack, {[key]: value});
		var found_text = $filter('filter')(stack, {[key]:''+value}, true);
		return !found.length && !found_text.length;
	};
}]);


app.controller('menu_editor', ['$scope','$http','$timeout','$filter','fileUploadService',function($scope,$http,$timeout,$filter,fileUploadService) {
	// Code for the Validator
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
		messages: {
			menu_item_name: { required: Lang.get('js_messages.store.field_required') },
			menu_item_desc: { required: Lang.get('js_messages.store.field_required') },
			menu_item_price: { required: Lang.get('js_messages.store.field_required'),number:Lang.get('js_messages.store.please_enter_valid_number'),maxlength:Lang.get('js_messages.store.please_enter_no_more_than_characters')},
			menu_item_tax: { required: Lang.get('js_messages.store.field_required'),number:Lang.get('js_messages.store.please_enter_valid_number'),maxlength:Lang.get('js_messages.store.please_enter_no_more_than_characters'),max:Lang.get('js_messages.store.please_enter_value_less_than_or_equal') },
			item_status: { required: Lang.get('js_messages.store.field_required') },
			item_image:  {  required : Lang.get('js_messages.store.field_required'),image_valitation:Lang.get('js_messages.store.please_upload_images_like_file_only')},
			"menu_item_translations[0][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_item_translations[1][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_item_translations[0][name]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_item_translations[1][name]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_item_translations[0][description]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_item_translations[1][description]":{ required: Lang.get('js_messages.store.field_required') },
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

	// Code for the Validator
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
		messages: {
			menu_name: { required: Lang.get('js_messages.store.field_required') },
			"menu_timing_day[]":{ required: Lang.get('js_messages.store.field_required') },
			"menu_timing_start_time[]": { required: Lang.get('js_messages.store.field_required') , greate_then: Lang.get('js_messages.store.start_time_should_less_than_end_time') },
			"menu_timing_end_time[]": { required: Lang.get('js_messages.store.field_required') },
			"translations[0][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"translations[1][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"translations[0][name]":{ required: Lang.get('js_messages.store.field_required') },
			"translations[1][name]":{ required: Lang.get('js_messages.store.field_required') },
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

	$('#category_add_form').validate({
		rules: {
			category_name: {required: true},
			"category_translations[0][locale]": { required: true },
			"category_translations[1][locale]": { required: true },
			"category_translations[0][name]": { required: true },
			"category_translations[1][name]": { required: true },
		},
		messages: {
			category_name: { required: Lang.get('js_messages.store.field_required') },
			"category_translations[0][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[1][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[0][name]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[1][name]":{ required: Lang.get('js_messages.store.field_required') },
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
		messages: {
			category_name: { required: Lang.get('js_messages.store.field_required') },
			"category_translations[0][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[1][locale]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[0][name]":{ required: Lang.get('js_messages.store.field_required') },
			"category_translations[1][name]":{ required: Lang.get('js_messages.store.field_required') },
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
		$('.text-danger').text('');
		$scope.menu_id = menu_id;
		$scope.category_name = name;
		$scope.category_translations = [];
	};

	$scope.edit_category = function(id,name,menu){
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

	$scope.save_category = function(action){
		if(action == 'add'){
			var $valid = $('#category_add_form').valid();
			if (!$valid) {
				$validator.focusInvalid();
				return false;
			}
		}else{
			var $valid = $('#category_edit_form').valid();
			if (!$valid) {
				$validator.focusInvalid();
				return false;
			}
		}
		var method = 'POST';
		var url = 'update_category';
		var FormData = { 'name' : $scope.category_name ,'id' : $scope.category_id,'action' : action , 'menu_id' : $scope.menu_id, 'locale': $scope.locale };
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.item_all_details').removeClass('loading');
			if(action=='edit') {
				$('#sub_edit_modal').modal('toggle');
				var getMenu = response.translations;
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_category = response.category_name;
			}
			else {
				$('#add_category_modal').modal('toggle');
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
		$('.text-danger').text('');
		$scope.menu_index = index;
		$scope.menu_id = menu_id;
		var method = 'GET';
		var url = 'menu_time/'+ menu_id;
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
		}).
		success(function(response) {
			$scope.menu_name   = name;
			$scope.menu_timing = response.menu_time;
			$('.item_all_details').removeClass('loading');
		}).
		error(function(response) {
		});
		return false;
	};

	$scope.add_menu_pop = function () {
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
		$http({
			method: method,
			url: url,
		}).
		success(function(response) {
			$('.add_loading').removeClass('loading');
		}).
		error(function(response) {
			$('.add_loading').removeClass('loading');
		});
		return false;
	};

	$scope.update_menu_time = function(){
		var $valid = $('.update_menu_time').valid();
		if (!$valid) {
			$validator.focusInvalid();
			return false;
		}
		var method = 'POST';
		var url = 'update_menu_time';
		var FormData = { menu_time : $scope.menu_timing,menu_id : $scope.menu_id ,menu_name : $scope.menu_name,locale : $scope.locale  }
		$('.item_all_details').addClass('loading');
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.item_all_details').removeClass('loading');
			$('#edit_menu_modal').modal('toggle');
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
		var file = $scope.myFile;
		var uploadUrl = "update_menu_item",
		promise = fileUploadService.uploadFileToUrl(file, uploadUrl,{ menu_item : $scope.menu_item_details },$scope.locale,$scope.menu_item_details.menu_item_modifier);
		promise.success(function(response) {
			$('.item_all_details').removeClass('loading');
			$("#chooses_file").removeAttr("style");
			$('#file_text').text('');
			$('#banner_name').text('Choose file');
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
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item[$scope.menu_item_index].menu_item_name=response.edit_menu_item_name;
			}
			$scope.menu_item_translations = response.translations;
			$scope.menu_item_index = null;
			$scope.new_menu_item_index = null;
		}).
		error(function(response) {
		});
		return false;
	};

	$scope.add_new_item = function() {
		$('#myFileField').val('');
		$scope.myFile = '';
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
		$scope.myFile = '';
		$('.delete_item_msg').text('');
		$scope.remove_id 	= set_id;
		$scope.delete_name 	= name;
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
		var url = 'delete_menu';
		var FormData = {category_index:$scope.category_index, menu : $scope.menu[$scope.menu_index],category : $text ,key : item }
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.add_loading').removeClass('loading');
			$('.delete_item_msg').text('');
			if(response.status=='false') {
				$('#delete_error_modal').modal();
				$('.delete_item_msg').text(response.status_message);
				return false;
			}
			if($text=='menu')
			{
				$scope.menu.splice(item, 1);
				$scope.category_index = null;
			}
			else if($text=='category')
			{
				$scope.menu[$scope.menu_index].menu_category.splice(item, 1);
				$scope.category_index = null;
			}
			else
			{
				$scope.menu[$scope.menu_index].menu_category[$scope.category_index].menu_item.splice(item, 1);
				$scope.menu_item_index = null;
			}
		}).
		error(function(response) {
		});
		return false;
	};

	// Modifier Related Functions
	$scope.changeLocale = function() {
		$('.add_loading').addClass('loading');
		$scope.locale = $scope.trans_language;
		var url = 'menu_locale';
		var data = {locale:$scope.locale};

		$http.post(url,data).then(function(response) {
			if(response.status == 200) {
				$scope.current_language = $scope.getCurrentLanguage();
				$scope.modifier_error = '';
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
	
	$.validator.addMethod("greaterThan", function ( value, element, param ) {
		var target = $( param );

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
	}, Lang.get('js_messages.store.enter_valid_item_price'));

	// Code for the Validator
	var $update_menu_modifier = $('#update_menu_modifier').validate({
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
			"item_modifer_name[]": { required: true},
			"item_modifer_price[]": { number: true,decimalValue: true},
		},
		messages: {
			menu_name: { required: Lang.get('js_messages.store.please_enter_all_required_fields') },
			count_type: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			min_count: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			max_count: { required: Lang.get('js_messages.store.please_enter_all_required_fields')},
			is_multiple: { number:Lang.get('js_messages.store.please_enter_all_required_fields')},
			is_required: { number:Lang.get('js_messages.store.please_enter_all_required_fields')},
			"item_modifer_name[]": { required:Lang.get('js_messages.store.please_enter_all_required_fields') },
			"item_modifer_price[]": {number:Lang.get('js_messages.store.please_enter_valid_number') },
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
			if(!$scope.$$phase) {
				$scope.$apply();
			}
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
	}

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
		$scope.applyScope();
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


	/*Menu Loading functionality*/
	
	$(document).ready(function(){
		$scope.menu.forEach(function(menu,key) {
			if(menu.total_category > 1){
				for (var i=2;  i<= menu.total_category; i++) {
					let url 		= getUrl('get_category');
					let store_id 	= $scope.ori_store_id;
					let FormData 	= { store_id : store_id, menu_id : menu['menu_id'], page : i};
					console.log(menu);
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
	
	/*'End Here'*/

}]);

app.controller('preparation_time', ['$scope','$http','$timeout','$filter',function($scope,$http,$timeout,$filter) {
	$scope.add_preparation_time = function () {
		$scope.preparation_timing.push({id:'',max_time:50});
	}
	$scope.time_status = 1;
	$scope.remove_preparation =function(index)
	{
		$scope.preparation_timing.splice(index, 1);
	}
	$scope.increment =function ($index){
		var value = parseInt($scope.preparation_timing[$index].max_time);
		if(value >=55)
		{
			$scope.preparation_timing[$index].max_time = 55;
			return false;
		}
		$scope.preparation_timing[$index].max_time = value+5;
	}
	$scope.decrement =function ($index){
		if($scope.preparation_timing[$index].max_time ==5) return false;
		$scope.preparation_timing[$index].max_time -= 5;
	}
	$scope.default_increment =function (){
		if($scope.max_time >=55) return false;
		$scope.max_time += 5;
	}
	$scope.default_decrement =function (){
		if($scope.max_time ==5) return false;
		$scope.max_time -= 5;
	}
}]);
app.controller('offer', ['$scope','$http','$timeout','$filter',function($scope,$http,$timeout,$filter) {
	$scope.add_document = function(){
		$scope.all_document.push({ 'document_name': ''});
	};

	$scope.delete_document = function(index){
		$scope.all_document.splice(index, 1);
	};
	var offer_form_validate = $('#offer_form').validate({
		ignore: ':hidden:not(.do-not-ignore)',
		rules: {
			offer_title:{ required:true },
			offer_description:{ required:true },
			from:{ required:true },
			to:{ required:true },
			percentage:{ required:true,numeric:true,max:100,number:true },
		},
		messages: {
			'offer_title' : {  required : Lang.get('js_messages.store.title_field_required'),},
			'offer_description' : {  required : Lang.get('js_messages.store.description_field_required'),},
			'from' : {  required : Lang.get('js_messages.store.start_date_field_required'),},
			'to' : {  required : Lang.get('js_messages.store.end_date_field_required'),},
			'percentage' : {  required : Lang.get('js_messages.store.percentage_field_required'), numeric : Lang.get('js_messages.store.please_enter_valid_number'), max : Lang.get('js_messages.store.please_enter_value_less_than_or_equal'), number : Lang.get('js_messages.store.please_enter_valid_number'),min : Lang.get('js_messages.store.please_enter_value_grt_than_or_equal')},
		},
		errorElement: "span",
		errorClass: "text-danger",
		errorPlacement: function( label, element ) {
			if(element.attr( "data-error-placement" ) === "parent" ){
				element.parent().append( label );
			} else if(element.attr( "data-error-placement" ) === "next2" ){
				label.insertAfter( element.next() );
			} else if(element.attr( "data-error-placement" ) === "container" ){
				container = element.attr('data-error-container');
				$(container).append(label);
			} else {
				label.insertAfter( element );
			}
		}
	});
	$.validator.addMethod('type_price', function(value, element, param) {
		var max_price = $('#offer_max_price').val();
		var min_price = $('#offer_min_price').val();
		if($.isNumeric( min_price )==true && $.isNumeric( max_price )==true) {
			return true;
		}
		else {
			return false;
		}
	}, 'The Price value must be a number');
	$.validator.addMethod('less_then', function(value, element, param) {
		var max_price = $('#offer_max_price').val();
		var min_price = $('#offer_min_price').val();
		if(parseInt(min_price)<parseInt(max_price)) {
			return true;
		}
		else {
			return false;
		}
	}, 'The Min Price should be less than Max Price');
	$.validator.addMethod('greater', function(value, element, param) {
		var min_price = $('#offer_min_price').val();
		if(parseInt(min_price)>0) {
			return true;
		}
		else {
			return false;
		}
	}, 'The Min Price should be greater than 0');
	$.validator.addMethod('greater_then', function(value, element, param) {
		var max_price = $('#offer_max_price').val();
		var min_price = $('#offer_min_price').val();
		if(parseInt(min_price)<parseInt(max_price)) {
			return true;
		}
		else {
			return false;
		}
	}, 'The Max Price should be greater than Min Price');
	$.validator.addMethod('numeric', function(value, element, param) {
		var percentage = $('#percentage').val();
		if(percentage>0) {
			return true;
		}
		else {
			return false;
		}
	}, 'The percentage must greater than 0');
	$scope.add_offer =function (){
		var $valid = $('#offer_form').valid();
		if (!$valid) {
			offer_form_validate.focusInvalid();
			return false;
		}
		$('.offer_form_div').addClass('loading');
		var start_date =$('#from').val();
		var end_date =$('#to').val();
		var method = 'POST';
		var url = 'offers';
		var FormData = { new_offers : $scope.edit_offer ,start_date : start_date , end_date : end_date }
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('.offer_form_div').removeClass('loading');
			$('#offer_modal').modal('toggle');
			$scope.offers = response.offer;
			$('#offer_close').trigger('click');
			location.reload();
			offer_status();
		}).
		error(function(response) {
			$('.offer_form_div').removeClass('loading');
		});
		return false;
	}
	$(document).ready(function() {
		offer_status();
	});
	function offer_status() {
		$.each($scope.offers,function(key,value) {
			if(value.status==0) {
				$('#checkbox_offer_'+key).removeAttr('checked');
			}
			else if(value.status==1) {
				$('#checkbox_offer_'+key).attr('checked','checked');
			}
			else{
			}
		});
	}
	$(document).on('change', ".offer_check", function(){
		var status;
		if($(this).is(":checked")){
			status = 1;
		}
		else{
			status = 0;
		}
		var index = $(this).attr('data-val');
		var url= getUrls('offers_status');
		$http.post(url,{
			id      : index,
			status  : status
		}).then(function(response){
		});
	})
	$scope.set_offers =function($index,$title)
	{
		$('#offer_modal').modal('toggle');
		$('span.text-danger').remove();
		$('input').removeClass('text-danger');
		$scope.edit_offer =  angular.copy($scope.offers[$index]);
		$scope.offer_title = $title;
	}
	$(function () {
		$("#from").datepicker({
			numberOfMonths: 1,
			dateFormat: 'yy-mm-dd',
			minDate: 0,
			onSelect: function (selected) {
				var dt = new Date(selected);
				dt.setDate(dt.getDate());
				$("#to").datepicker("option", "minDate", dt);
			}
		});
		$("#to").datepicker({
			numberOfMonths: 1,
			dateFormat: 'yy-mm-dd',
			minDate: 0,
			onSelect: function (selected) {
				var dt = new Date(selected);
				dt.setDate(dt.getDate() - 1);
				if(strtotime($("#from").val())!=time())
					$("#from").datepicker("option", "maxDate", dt);
			}
		});
	});
	$scope.set= function(index,id) {
		$scope.offer_index = index;
		$scope.offer_id = id;
	}
	$scope.delete_offer= function() {
		$index = $scope.offer_index;
		id = $scope.offer_id;
		$scope.offers.splice($index, 1);
		var method = 'GET';
		var url = 'remove_offer/'+id;
		$http({
			method: method,
			url: url,
		}).
		success(function(response) {
		}).
		error(function(response) {
		});
		return false;
	}
}]);
app.controller('store_document', ['$scope','$http','$timeout','$filter',function($scope,$http,$timeout,$filter) {
	$scope.add_document = function(){
		$scope.all_document.push({ 'document_name': ''});
	};
	$scope.document_del=[];
	$scope.delete_document = function(index,id){
		$scope.all_document.splice(index, 1);
		$scope.document_del.push(id);
	};
}]);
app.filter('checkKeyValueUsedInStore', ["$filter", function($filter) {
	return function(value, key, stack) {
		var found = $filter('filter')(stack, {[key]: value});
		var found_text = $filter('filter')(stack, {[key]:''+value}, true);
		return !found.length && !found_text.length;
	};
}])
app.controller('profile', ['$scope','$http','$timeout','$filter',function($scope,$http,$timeout,$filter) {

	$scope.add_open_time = function() {
		$scope.open_time_timing.push({'day':''});
	};
	$scope.delete_open_time = function(index) {
		if($scope.open_time_timing.length < 2)
			return false;
		$scope.open_time_timing.splice( index, 1 );
	};
	$scope.open_time_timing = [];
	$scope.update_open_time = function()
	{
		var $valid = $('#open_time_form').valid();
		if (!$valid) {
			$open_time_validator.focusInvalid();
			return false;
		}
		var method = 'POST';
		var url = 'update_open_time';
		var FormData = { open_time_timing : $scope.open_time_timing }
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
	}
	$scope.add_document = function(){
		$scope.all_document.push({ 'document_name': ''});
	};
	$scope.document_del=[];
	$scope.delete_document = function(index,id){
		console.log(id);
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
	$scope.update_documents = function()
	{
		var $valid = $('#store_documents').valid();
		if (!$valid) {
			$document_validator.focusInvalid();
			return false;
		}
		var method = 'POST';
		var url = 'update_documents';
		var FormData = { open_time_timing : $scope.open_time_timing }
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
	}

	$("#location_value").keypress(function(e) {
    	$scope.latitude = $scope.longitude = '';
    	$scope.$apply();
    	if (event.keyCode === 13) {  
    		event.preventDefault(); 
    	}
    });

    $('#location_value').keyup(function(){
    	$scope.is_auto_complete = '';
    });

	$scope.autocomplete = '';
	$scope.initialize_autocomplete = function()
	{
		autocomplete_elem = document.getElementById('location_value');
		$scope.autocomplete = new google.maps.places.Autocomplete(autocomplete_elem, { types: ['address']});
		$scope.autocomplete.addListener('place_changed', $scope.fillInAddress);
	}
	$scope.fillInAddress = function()
	{
		place = $scope.autocomplete.getPlace();
		$scope.fetchMapAddress(place);
	}
	$scope.fetchMapAddress = function(data) {
		var componentForm = {
			street_number: 'short_name',
			route: 'long_name',
			sublocality_level_1: 'long_name',
			sublocality: 'long_name',
			locality: 'long_name',
			administrative_area_level_1: 'long_name',
			country: 'short_name',
			postal_code: 'short_name'
		};
		var street_number = '';
		var place = data;
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				var val = place.address_components[i][componentForm[addressType]];
				if (addressType == 'street_number')
					street_number = val;
				if (addressType == 'route')
					$scope.street = street_number + ' ' + val;
				if (addressType == 'postal_code')
					$scope.postal_code = val;
				if (addressType == 'locality')
					$scope.city = val;
				if (addressType == 'administrative_area_level_1')
					$scope.state = val;
				if (addressType == 'country')
					$scope.country = val;
			}
		}
		$scope.latitude = place.geometry.location.lat();
		$scope.longitude = place.geometry.location.lng();
		$scope.$apply();
	}
	

	$scope.initialize_map = function ()
	{
		var map_element = document.getElementById('location_map');
		if(!$scope.latitude || !$scope.longitude || !map_element)
		{
			return false;
		}
		$scope.map = new google.maps.Map(map_element, {
			center: {
				lat: parseFloat($scope.latitude),
				lng: parseFloat($scope.longitude)
			},
			zoom: 16,
			scrollwheel: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.SMALL
			}
		});
		$scope.initialize_marker();
	}
	$scope.initialize_marker = function()
	{
		var location_position = new google.maps.LatLng($scope.latitude, $scope.longitude);
		$scope.location_marker = new google.maps.Marker({
			map:$scope.map,
			draggable:true,
				// animation: google.maps.Animation.DROP,
				position: location_position,
				icon:new google.maps.MarkerImage(
					APP_URL+'/images/map_pin.png',
					new google.maps.Size(34, 50),
					new google.maps.Point(0, 0),
					new google.maps.Point(17, 50)
					)
			});
		google.maps.event.addListener($scope.location_marker, 'dragend', function()
		{
			marker_location = $scope.location_marker.getPosition();
			$scope.latitude = marker_location.lat();
			$scope.longitude = marker_location.lng();
			$scope.$apply();
		});
	};
	$scope.$watch('latitude', function(new_value, old_value) {
		
	});

	$scope.send_message = function(){
		var method = 'POST';
		var mobile_number = $('#mobile_number').val();
		if(!$.isNumeric( mobile_number )) {
			$('.message_status').text('The phone number must be number');
			return false
		}
		if(mobile_number.length<7)
		{
			$('.message_status').text('The phone number must be of minimum 6 digit ');
			return false
		}
		$('#profile_form').addClass('loading');
		var url =  getUrls('send_message');;
		var FormData = {'code' : $('#phone_code').val() ,'mobile_no' : mobile_number}
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('#profile_form').removeClass('loading');
			if(response.status=='Success'){
				$('.phone_code_val').text($('#phone_code').val());
				$('.phone_number_val').text($('#mobile_number').val());
				$('#verify_modal').modal('show');
				$('.confirn_code').text(Lang.get('js_messages.store.message_send_successfully'));
				$('.message_status').text('');
				$('#verify_code').val(response.code);
				$scope.verify_code = response.code;
			}
			else
				$('.message_status').text(response.message);
		}).error(function(response) {
		});
		return false;
	};

	$scope.verify_mobile_code = function() {
		if($('#verify_code').val()!=$scope.verify_code)
		{
			$('.confirn_code').text(Lang.get('js_messages.store.invalid_code'));
			return false;
		}
		$('#verify_modal_content').addClass('loading');
		var method = 'POST';
		var url =  getUrls('confirm_phone_no');;
		var FormData = {}
		$http({
			method: method,
			url: url,
			data: FormData,
		}).
		success(function(response) {
			$('#verify_modal_content').removeClass('loading');
			$('#verify_modal').modal('hide');
			$('.verify_link').hide();
		}).
		error(function(response) {
		});
		return false;
	};
}]);
