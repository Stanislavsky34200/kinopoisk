var templatesPath = '/js/apps/templates/';
angular
	.module('TopApp', ['ngRoute',])
	.config(function ($routeProvider) {
		$routeProvider
			.when('/top', {
				'controller' : 'TopController',
				'templateUrl' : templatesPath + 'Top.html',
				'controllerAs' : 'topCtrl',
			})
			.when('/top/:topDate', {
				'controller' : 'TopController',
				'templateUrl' : templatesPath + 'Top.html',
				'controllerAs' : 'topCtrl',
			})
			.when('/film/:filmId', {
				'controller' : 'FilmController',
				'templateUrl' : templatesPath + 'Film.html',
				'controllerAs' : 'filmCtrl',
			})
			.otherwise({'redirectTo' : '/top'});
	})
	.filter('html', function(){//Messy workaround for displaing html spec characters
		return function (val) { 
			var el = document.createElement("div");
     		el.innerHTML = val;
     		str =   el.textContent || el.innerText;
      		return str;
		};
	})
	.run(function ($rootScope, $location) {
		$rootScope.history = [$location.$$path];
		$rootScope.$on('$routeChangeSuccess', function() {
		    $rootScope.history.push($location.$$path);
		});
		$rootScope.back = function () {
		    if ($rootScope.history.length > 1)
		    	$location.path($rootScope.history.splice(-2)[0]);
			else 
				$location.path('/top');
		};
	});
