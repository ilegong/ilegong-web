(function (window, angular) {
	angular.module('weshares', ['ui.router'])
		.config(configCompileProvider)
		.config(configHttpProvider)
		.config(configStates)
		.controller('WesharesAddCtrl', WesharesAddCtrl);

	/* @ngInject */
	function configCompileProvider($compileProvider) {
		$compileProvider.imgSrcSanitizationWhitelist(/^\s*(https|file|blob|cdvfile|http|chrome-extension|blob:chrome-extension):|data:image\//);
	}

	/* @ngInject */
	function configHttpProvider($httpProvider) {
		$httpProvider.defaults.headers.common['Content-Type'] = 'application/json';
		$httpProvider.defaults.headers.post['Content-Type'] = 'application/json';
	}
	/* @ngInject */
	function configStates($stateProvider, $urlRouterProvider, $locationProvider) {
		$stateProvider
			.state('add', {url: '/add', templateUrl: '/weshares/templates/add.html',controller: 'WesharesAddCtrl as vm'});

		$urlRouterProvider.otherwise('/add');
		$locationProvider.hashPrefix('!').html5Mode(false);
	}

	function WesharesAddCtrl($scope, $rootScope, $log) {
		var vm = this;
		vm.click=click;
		activate();

		function activate() {
			vm.userId = 1;
		}
		function click(){
			$log.log('clicked');
		}
	}
})(window, window.angular);