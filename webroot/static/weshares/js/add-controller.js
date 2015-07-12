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
			var productHasError = _.any(vm.weshare.products, function (product) {
				var nameHasError = vm.validateProductName(product);
				var priceHasError = vm.validateProductPrice(product);
				return nameHasError || priceHasError;
			});
			if (titleHasError || productHasError) {
				return;
			}

			vm.showShippmentInfo = true;
		}

		function submit() {
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
			if (vm.weshareTitleHasError) {
				if (_.isEmpty(vm.weshare.title)) {
					vm.titleErrorMsg = '分享标题不能为空';
				}
				if (vm.weshare.title.length > 30) {
					vm.titleErrorMsg = '标题太长喽，最多输入30个字喔！';
				}
			}
			return vm.weshareTitleHasError;
		}

		function validateProductName(product) {
			product.nameHasError = _.isEmpty(product.name) || product.name.length > 9;
			if (product.nameHasError) {
				if (_.isEmpty(product.name)) {
					product.nameErrorMsg = '描述不能为空喔！';
				}
				if (product.name.length > 9) {
					product.nameErrorMsg = '描述太长喽，最多输入9个字喔！';
				}
			}
			return product.nameHasError;
		}

		function validateProductPrice(product) {
			product.priceHasError = _.isEmpty(product.price) || !Utils.isNumber(product.price);
			if (product.priceHasError) {
				if (_.isEmpty(product.price)) {
					product.priceErrorMsg = '价格不能为空喔！';
				}
				if (!vm.isNumber(product.price)) {
					product.priceErrorMsg = '请填写数字！';
				}
			}
			return product.priceHasError;
		}
	}
})(window, window.angular, window.wx);