app.controller('language', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
	$scope.update_language = function(data) {
		$http.post(ajax_url_list['language_update'],data).then(function(response) {
			$(".alert").remove();
			if(response.data.success == true)
				$('td[data-lang="' + response.data.converted_message.key_value + '"][data-sub_key="' + response.data.converted_message.sub_key + '"]').text(response.data.converted_message.convert_message);
			else
			{
				$('.flash-container').append('<div class="alert alert-danger text-center" role="alert"><a href="#" class="alert-close" data-dismiss="alert"></a>Data add,edit & delete Operation are restricted in live.</div>')
			}
		});
	};

	$(document).ready(function () {
	  var companyTable=  $('#example').DataTable();   
	    $('#example').on('click', 'td', function () {
	    	var str = this;
	    	var get = str.innerText
	       	var name = $('td', this).eq().text();
	       	var lang = $(this).attr("data-lang");;
	       	var sub_key = $(this).attr("data-sub_key");
	       	var main_key =  $(this).attr("data-main_key");
	       	$('#company-full-name').attr('data-sub_key',sub_key);
	       	$('#company-full-name').attr('data-lang',lang);	
	       	$('#company-full-name').attr('data-main_key',main_key);	
	      	$("#company-full-name").val(get);
	      	$('#DescModal').modal("show");
	    });
	});

	$("#update_api_user_language").click(function(){
		var current_url = window.location.href.split('?')[0];
		var last_part = current_url.substr(current_url.lastIndexOf('/'));
		var last_part1 = current_url.substr(current_url.lastIndexOf('/') + 1);
		var language = $("#company-full-name").attr("data-lang");
		var sub_key  = $("#company-full-name").attr("data-sub_key");
		var main_key = $("#company-full-name").attr("data-main_key");
	  	var messages = $('#company-full-name').val();
	  	var data = {}
	  	data['lanuage']    = language
		data['main_key']   = main_key; 
		data['sub_key']    = sub_key; 
		data['messages']   = messages 
		data['file']	   = last_part1
		$scope.update_language(data);
	});

}]);

