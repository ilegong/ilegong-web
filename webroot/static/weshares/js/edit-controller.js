(function (window, angular, wx) {

	angular.module('weshares')
		.constant('wx', wx)
		.controller('WesharesEditCtrl', WesharesEditCtrl);


	function WesharesEditCtrl($scope, $rootScope, $log, $http, wx, Utils) {
		var vm = this;
		vm.chooseAndUploadImage = chooseAndUploadImage;
		vm.uploadImage = uploadImage;
		vm.deleteImage = deleteImage;

		vm.toggleProduct = toggleProduct;
		vm.toggleAddress = toggleAddress;

		vm.nextStep = nextStep;
		vm.createWeshare = createWeshare;

		vm.validateTitle = validateTitle;
		vm.validateProductName = validateProductName;
		vm.validateProductPrice = validateProductPrice;

		activate();

		function activate() {
			vm.showShippmentInfo = false;
      var weshareId = angular.element(document.getElementById('weshareEditView')).attr('data-id');
      $log.log(weshareId);
      //add
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
      if(weshareId){
        //update
        $http.get('/weshares/get_share_info/'+weshareId).success(function(data){
          $log.log(data);
          vm.weshare = data;
        }).error(function(data){

        });
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

		function createWeshare() {
      if(vm.isInProcess){
        return;
      }
      vm.isInProcess = true;
			vm.weshare.addresses = _.filter(vm.weshare.addresses, function(address){
				return !_.isEmpty(address.address);
			});

			$log.log('submitted').log(vm.weshare);
			$http.post('/weshares/save', vm.weshare).success(function (data, status, headers, config) {
				if (data.success) {
					$log.log('post succeeded, data: ').log(data);
					window.location.href = '/weshares/view/' + data['id'];
				}
				else {
          vm.isInProcess = false;
					$log.log("failed with status: " + status + ", data: ").log(data);
				}
			}).error(function (data, status, headers, config) {
          vm.isInProcess = false;
					$log.log("failed with status :" + status + ", data: ").log(data).log(', and config').log(config);
				});
		}

		function validateTitle() {
			vm.weshareTitleHasError = _.isEmpty(vm.weshare.title) || vm.weshare.title.length > 50;
			return vm.weshareTitleHasError;
		}

		function validateProductName(product) {
			product.nameHasError = _.isEmpty(product.name) || product.name.length > 9;
			return product.nameHasError;
		}

		function validateProductPrice(product) {
			product.priceHasError = !product.price || !Utils.isNumber(product.price);
			return product.priceHasError;
		}
	}
})(window, window.angular, window.wx);