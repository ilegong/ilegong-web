(function (window, angular) {

  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl);

  function WesharesViewCtrl($state, $scope, $rootScope, $log, $http, $templateCache, $stateParams, Utils) {
    var vm = this;
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.viewImage = viewImage;
    vm.getShowAddress = getShowAddress;
		vm.submitOrder = submitOrder;

    activate();
    function activate() {
			var weshareId = $stateParams.id;
			vm.weshare = {};
      $http({method: 'GET', url: '/weshares/detail/' + weshareId, cache: $templateCache}).
        success(function (data, status) {
          $log.log(data);
          vm.weshare = data['weshare'];
					vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
          vm.weshare.showAddresses = vm.getShowAddress();
          vm.ordersDetail = data['ordersDetail'];
          vm.currentUser = data['current_user'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

		function getShowAddress(){
			var addresses = _.map(vm.weshare.addresses,function(item){
				return item['address'];
			});
			return addresses.join('  ');
		}

		function viewImage(url){
			wx.previewImage({
				current: url,
				urls: vm.weshare.images
			});
		}

		function increaseProductNum(product){
			if(!Utils.isNumber(product.count)){
				product.num = 0;
			}
			product.num = product.num + 1;
		}
		function decreaseProductNum(product){
			if(product.num >= 1){
				product.num = product.num - 1;
			}
		}
		function submitOrder(paymentType){
			var products = _.filter(vm.weshare.products, function(product){
				return product.count > 0;
			});
			products = _.map(products, function(product){return {id: product.id, num:product.num};});
			var orderData = {
				weshare_id: vm.weshare.id,
				address_id: vm.weshare.selectedAddressId,
				products: products,
				buyer: {name: vm.buyerName, mobilephone: vm.buyerMobilePhone}
			};
			$log.log(orderData);
			return;
			$http.post('', orderData).success(function(){
			}).error(function(){
			});
		}
	}
})(window, window.angular);