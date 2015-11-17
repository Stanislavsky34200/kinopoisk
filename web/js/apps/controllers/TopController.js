angular.module('TopApp').controller('TopController', ['$http', '$routeParams', '$location', '$filter', '$scope', function($http, $routeParams, $location, $filter, $scope){
	$scope.currentDate = new Date();
	this.topPositions = [];
	this.date = typeof($routeParams.topDate) != 'undefined' ? new Date($routeParams.topDate) : new Date();
	if (!this.date){
		date = new Date();//In case of invalid date
	}
	this.errorMessage = false;
	this.$location = $location;
	
	var date = $filter('date')(new Date(this.date), 'yyyy-MM-dd');
	var ctrl = this;
	$http({
		method: 'GET',
		url: '/app.php/api/top/get_top_data/' + date
		}).then(function successCallback(response) {
			if (!response.data){
				ctrl.errorMessage = "There is no data for this date avaliable.";
			} else {
				console.log(response.data);
				ctrl.topPositions = response.data;
				ctrl.errorMessage = false;
			}
		}, function errorCallback(response) {
			ctrl.errorMessage = "Can't connect server. Please try later.";
	});
	
	this.getTop = function(){
		$location.path('/top/' + $filter('date')(new Date(this.date), 'yyyy-MM-dd'));
	};
}]);
