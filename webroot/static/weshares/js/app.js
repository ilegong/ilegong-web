(function (window, angular) {
	angular.module('weshares', ['ui.router'])
		.constant('_', window._)
		.config(configCompileProvider)
		.config(configHttpProvider)
		.config(configStates)
		.config(extendLog)
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
			.state('add', {url: '/add', templateUrl: '/static/weshares/templates/add.html',controller: 'WesharesAddCtrl as vm'});

		$urlRouterProvider.otherwise('/add');
		$locationProvider.hashPrefix('!').html5Mode(false);
	}
	/* @ngInject */
	function extendLog($provide){
		$provide.decorator('$log', function($delegate, $injector){
			var _log = $delegate.log;
			var _warn = $delegate.warn;
			var _info = $delegate.info;
			var _debug = $delegate.debug;
			var _error = $delegate.error;
			var addMessage = function(message, forceLog){
				var $rootScope = $injector.get("$rootScope");
				$rootScope.config = $rootScope.config || {logMode: false};
				if($rootScope.config.logMode || forceLog){
					$rootScope.messages = $rootScope.messages || [];
					$rootScope.messages.push(message);
				}
				return message;
			}

			$delegate.log = function(msg, forceLog){_log(addMessage(msg, forceLog || false)); return this;};
			$delegate.warn = function(msg, forceLog){_warn(addMessage(msg, forceLog || false)); return this;};
			$delegate.info = function(msg, forceLog){_info(addMessage(msg, forceLog || false)); return this;};
			$delegate.debug = function(msg, forceLog){_debug(addMessage(msg, forceLog || false)); return this;};
			$delegate.error = function(msg, forceLog){_error(addMessage(msg, forceLog || false)); return this;};

			return $delegate;
		});
	}

	function initApp($rootScope){
		$rootScope._ = _;
	}

	function WesharesAddCtrl($scope, $rootScope, $log) {
		var vm = this;
		vm.submit = submit;
		vm.toggleProduct = toggleProduct;

		activate();

		function activate() {
			vm.showShippmentInfo = false;
			vm.weshare = {
				name: '',
				products: [{name: 'fdaf', price: 100}],
				shippment: {send_date: ''}
			}
		}

		function toggleProduct(product, isLast){
			if(isLast){
				vm.products.push({name: 'fdaf', price: 100});
			}
			else{
				vm.products = _.without(vm.products, product);
			}
		}
		function submit(){
			$log.log('submitted').log(vm.weshare);
		}
	}
})(window, window.angular);