angular.module('TopApp').controller('FilmController', ['$http', '$routeParams', function($http, $routeParams){
	this.film = false;
	this.statistics = [];
	this.errorMessage = parseInt($routeParams.filmId) != NaN ? false : "Invalid film id";
	var ctrl = this;
	if (!this.errorMessage){
		$http({
			method: 'GET',
			url: '/app.php/api/top/get_film_data/' + $routeParams.filmId
			}).then(function successCallback(response) {
				if (!response.data){
					ctrl.errorMessage = "This film doesen't exist.";
				} else {
					console.log(response.data);
					ctrl.film = response.data.film;
					ctrl.statistics = response.data.statistics;
					ctrl.errorMessage = false;
				}
			}, function errorCallback(response) {
				ctrl.errorMessage = "Can't connect server. Please try again later.";
		});
	}
}]);
