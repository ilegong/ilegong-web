(function (window, angular) {
	angular.module('weshares', ['ui.router'])
		.constant('_', window._)
		.config(configCompileProvider)
		.config(configHttpProvider)
		.config(configStates)
		.controller('WesharesAddCtrl', WesharesAddCtrl)
		.run(initApp);

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

	function initApp($rootScope){
		$rootScope._ = _;
	}

	function WesharesAddCtrl($scope, $rootScope, $log) {
		var vm = this;
		vm.submit = submit;
		vm.deleteProduct = deleteProduct;

		activate();

		function activate() {
			vm.showShippmentInfo = false;
			vm.products = [{}, {}, {},{}, {}];
		}

		function toggleProduct(product, isLast){
			if(isLast){
				vm.products.push({});
			}
			else{
				vm.products = _.without(vm.products, product);
			}
		}
		function submit(){
			$log.log('submitted');
		}
	}
})(window, window.angular);