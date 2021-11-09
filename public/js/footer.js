app.controller('footer', ['$scope', '$http','$rootScope', function($scope, $http,$rootScope) {
	$('#language_footer').change(function() {
		$http.post(APP_URL + "/set_session", {
			language: $(this).val()
		}).then(function(data) {
			toastr.success(data.data.success, data.data.message);
			setTimeout(function() {
				location.reload();
			},1000);
		});
	});

	$('#js-currency-select').change(function(){
		currency_code = $(this).val();
		console.log(currency_code);
		$http.post(APP_URL+'/set_session', {currency: currency_code}).then(function(data){
			if(window.location.href.split("?")[0].split("/").indexOf("order_track"))
				location_to = window.location.href;
			else
				location_to = window.location.href.split("?")[0];
			window.location = location_to;
		});
	});
	
	
}]);