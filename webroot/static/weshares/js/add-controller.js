(function (window, angular, wx) {

	angular.module('weshares')
		.constant('wx', wx)
		.controller('WesharesAddCtrl', WesharesAddCtrl);


	function WesharesAddCtrl($state, $scope, $rootScope, $log, $http, wx, Utils) {
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
				send_info: '',
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
				fail: function (res) {
					vm.messages.push({name: 'choose image failed', detail: res});
				}
			});
		}

		function uploadImage(localId) {
			wx.uploadImage({
				localId: localId,
				isShowProgressTips: 1,
				success: function (res) {
					$http.get('/downloads/download_wx_img?media_id=' + res.serverId).success(function (data, status, headers, config) {
						vm.messages.push({name: 'download image success', detail: data});
						var imageUrl = data['download_url'];
						if (!imageUrl || imageUrl == 'false') {
							return;
						}
						vm.weshare.images.push({url: imageUrl});
					}).error(function (data, status, headers, config) {
							vm.messages.push({name: 'download image failed', detail: data});
						});
				},
				fail: function (res) {
					vm.messages.push({name: 'upload image failed', detail: res});
				}
			});
		}

		function deleteImage(image) {
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
				vm.weshare.addresses.push({address: ''});
			}
			else {
				vm.weshare.addresses = _.without(vm.weshare.addresses, address);
			}
		}

		function nextStep() {
			var titleHasError = vm.validateTitle();
			var productHasError = false;
			_.each(vm.weshare.products, function (product) {
				var nameHasError = vm.validateProductName(product);
				var priceHasError = vm.validateProductPrice(product);
				productHasError = productHasError || nameHasError || priceHasError;
			});
			if (titleHasError || productHasError) {
				return;
			}

			vm.showShippmentInfo = true;
		}

		function submit() {
			vm.weshare.addresses = _.filter(vm.weshare.addresses, function(address){
				return !_.isEmpty(address.address);
			});

			$log.log('submitted').log(vm.weshare);
			$http.post('/weshares/create', vm.weshare).success(function (data, status, headers, config) {
				if (status == 200) {
					$log.log('post succeeded, data: ').log(data);
					$state.go('view', {id: data['id']});
				}
				else {
					$log.log("failed with status: " + status + ", data: ").log(data);
				}
			}).error(function (data, status, headers, config) {
					$log.log("failed with status :" + status + ", data: ").log(data).log(', and config').log(config);
				});
		}

		function validateTitle() {
			vm.weshareTitleHasError = _.isEmpty(vm.weshare.title) || vm.weshare.title.length > 30;
			return vm.weshareTitleHasError;
		}

		function validateProductName(product) {
			product.nameHasError = _.isEmpty(product.name) || product.name.length > 9;
			return product.nameHasError;
		}

		function validateProductPrice(product) {
			product.priceHasError = _.isEmpty(product.price) || !Utils.isNumber(product.price);
			return product.priceHasError;
		}
	}
})(window, window.angular, window.wx);