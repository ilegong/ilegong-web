/**
 * pool product controller
 */
(function (window, angular) {
  angular.module('weshares').controller('BuyPoolProductCtrl', BuyPoolProductCtrl);
  function BuyPoolProductCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath) {
    var vm = this;
    vm.backPoolProductInfoView = backPoolProductInfoView;
    vm.orderTotalPrice = 0;
    activate();
    function activate() {

    }
    function backPoolProductInfoView() {

    }
  }
})(window, window.angular);