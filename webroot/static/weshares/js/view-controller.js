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
    vm.increaseProductNum = increaseProductNum;
    vm.decreaseProductNum =decreaseProductNum;

    activate();
    function activate() {
      var weshareId = $stateParams.id;
      vm.weshare = {};
      vm.orderTotalPrice = 0;
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

    function getShowAddress() {
      var addresses = _.map(vm.weshare.addresses, function (item) {
        return item['address'];
      });
      return addresses.join('  ');
    }

    function viewImage(url) {
      wx.previewImage({
        current: url,
        urls: vm.weshare.images
      });
    }

    function calOrderTotalPrice() {
      var products = _.filter(vm.weshare.products, function (product) {
        return product.num > 0;
      });
      var totalPrice = 0;
      _.each(products,function(product){
        totalPrice+=product.price*product.num;
      });
      vm.orderTotalPrice = totalPrice/100;
    }

    function increaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      product.num = product.num + 1;
      calOrderTotalPrice();
    }

    function decreaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      if (product.num >= 1) {
        product.num = product.num - 1;
      }
      calOrderTotalPrice();
    }

    function submitOrder(paymentType) {
      var products = _.filter(vm.weshare.products, function (product) {
        return product.num&&(product.num > 0);
      });
      products = _.map(products, function (product) {
        return {id: product.id, num: product.num};
      });
      var orderData = {
        weshare_id: vm.weshare.id,
        address_id: vm.weshare.selectedAddressId,
        products: products,
        buyer: {name: vm.buyerName, mobilephone: vm.buyerMobilePhone}
      };
      $log.log(orderData);
      $http.post('/weshares/makeOrder/', orderData).success(function (data) {
        $log.log(data);
        if(data.success){
          //pay
          window.location.href='/weshares/pay/'+data.orderId+'/'+paymentType;
        }
      }).error(function () {

      });
    }
  }
})(window, window.angular);