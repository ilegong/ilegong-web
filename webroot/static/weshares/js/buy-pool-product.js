/**
 * pool product controller
 * not use
 */
(function (window, angular) {
  angular.module('weshares').controller('BuyPoolProductCtrl', BuyPoolProductCtrl);
  function BuyPoolProductCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath, PoolProductInfo) {
    var vm = this;
    vm.backPoolProductInfoView = backPoolProductInfoView;
    vm.orderTotalPrice = 0;
    vm.submitOrder = submitOrder;
    vm.staticFilePath = staticFilePath;
    activate();
    function activate() {

    }

    function backPoolProductInfoView() {

    }

    function submitOrder($type) {

    }
  }
})(window, window.angular);