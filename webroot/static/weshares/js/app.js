(function (window, angular, wx) {
	angular.module('weshares', ['ui.router'])
		.constant('_', window._)
		.constant('wx', wx)
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
			.state('add', {url: '/add', templateUrl: '/static/weshares/templates/add.html',controller: 'WesharesAddCtrl as vm'})
			.state('view', {url: '/view', templateUrl: '/static/weshares/templates/view.html',controller: 'WesharesViewCtrl as vm'});

		$urlRouterProvider.otherwise('/add');
		$locationProvider.hashPrefix('!').html5Mode(false);
	}

	/* @ngInject */
	function extendLog($provide) {
		$provide.decorator('$log', function ($delegate, $injector) {
			var _log = $delegate.log;
			var _warn = $delegate.warn;
			var _info = $delegate.info;
			var _debug = $delegate.debug;
			var _error = $delegate.error;
			var addMessage = function (message, forceLog) {
				var $rootScope = $injector.get("$rootScope");
				$rootScope.config = $rootScope.config || {logMode: false};
				if ($rootScope.config.logMode || forceLog) {
					$rootScope.messages = $rootScope.messages || [];
					$rootScope.messages.push(message);
				}
				return message;
			}

			$delegate.log = function (msg, forceLog) {
				_log(addMessage(msg, forceLog || false));
				return this;
			};
			$delegate.warn = function (msg, forceLog) {
				_warn(addMessage(msg, forceLog || false));
				return this;
			};
			$delegate.info = function (msg, forceLog) {
				_info(addMessage(msg, forceLog || false));
				return this;
			};
			$delegate.debug = function (msg, forceLog) {
				_debug(addMessage(msg, forceLog || false));
				return this;
			};
			$delegate.error = function (msg, forceLog) {
				_error(addMessage(msg, forceLog || false));
				return this;
			};

			return $delegate;
		});
	}

	function initApp($rootScope) {
		$rootScope._ = _;
	}

	function WesharesAddCtrl($scope, $rootScope, $log, $http, $timeout, wx) {
		var vm = this;
		vm.chooseAndUploadImage = chooseAndUploadImage;
		vm.uploadImage = uploadImage;
		vm.deleteImage = deleteImage;

		vm.toggleProduct = toggleProduct;
		vm.toggleAddress = toggleAddress;

		vm.nextStep = nextStep;
		vm.submit = submit;

		vm.validateTitle = validateTitle;
		vm.validateProductName = validateProductName;
		vm.validateProductPrice = validateProductPrice;
		vm.isNumber = isNumber;

		activate();

		function activate() {
			vm.showShippmentInfo = false;
			vm.weshare = {
				title: '',
				description: '',
				images: [],
				products: [
					{name: ''}
				],
				send_date: '',
				addresses: [
					{address: ''}
				]
			}
			vm.messages = [];
		}

		function chooseAndUploadImage() {
			wx.chooseImage({
				success: function (res) {
					_.each(res.localIds, vm.uploadImage);
				},
				fail: function(res){
					vm.messages.push({name: 'choose image failed', detail: res});
				}
			});
		}
		function uploadImage(localId){
			wx.uploadImage({
				localId: localId,
				isShowProgressTips:1,
				success : function(res){
					$http.get('/downloads/download_wx_img?media_id='+res.serverId).success(function(data, status, headers, config){
						vm.messages.push({name: 'download image success', detail: data});
						var imageUrl = data['download_url'];
						if(!imageUrl || imageUrl=='false'){
							return;
						}
						vm.weshare.images.push({url: imageUrl});
					}).error(function(data, status, headers, config){
							vm.messages.push({name: 'download image failed', detail: data});
					});
				},
				fail: function(res){
					vm.messages.push({name: 'upload image failed', detail: res});
				}
			});
		}
		function deleteImage(image){
			vm.weshare.images = _.without(vm.weshare.images, image);
		}

		function toggleProduct(product, isLast) {
			if (isLast) {
				vm.weshare.products.push({name: ''});
			}
			else {
				vm.weshare.products = _.without(vm.weshare.products, product);
			}
		}

		function toggleAddress(address, isLast) {
			if (isLast) {
				vm.weshare.addresses.push({name: ''});
			}
			else {
				vm.weshare.addresses = _.without(vm.weshare.addresses, address);
			}
		}

		function nextStep() {
			var titleHasError = vm.validateTitle();
			var productHasError = _.any(vm.weshare.products, function(product){
				var nameHasError = vm.validateProductName(product);
				var priceHasError = vm.validateProductPrice(product);
				return nameHasError || priceHasError;
			});
			if(titleHasError || productHasError){
				return;
			}

			vm.showShippmentInfo = true;
		}

		function submit() {
			$log.log('submitted').log(vm.weshare);
			$http.post('/weshares/create', vm.weshare).success(function (data, status, headers, config) {
				if (status == 200) {
					$log.log('post succeeded, data: ').log(data);
				}
				else {
					$log.log("failed with status: " + status + ", data: ").log(data);
				}
			}).error(function (data, status, headers, config) {
					$log.log("failed with status :" + status + ", data: ").log(data).log(', and config').log(config);
				});
		}

		function validateTitle(){
			vm.weshareTitleHasError = _.isEmpty(vm.weshare.title) || vm.weshare.title.length > 30;
			return vm.weshareTitleHasError;
		}
		function validateProductName(product){
			product.nameHasError = _.isEmpty(product.name) || product.name.length > 9;
			return product.nameHasError;
		}
		function validateProductPrice(product){
			product.priceHasError = _.isEmpty(product.price) || !vm.isNumber(product.price);
			return product.priceHasError;
		}
		function isNumber(n){
			return Number(n) == n && (n %1 === 0);
		}
	}
})(window, window.angular, window.wx);
