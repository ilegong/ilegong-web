(function (window, angular) {
  angular.module('weshares')
    .controller('ShareProductViewCtrl', ShareProductViewCtrl);

  function ShareProductViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath) {
    var vm = this;
    vm.pageLoaded = pageLoaded;
    vm.initProductInfo = initProductInfo;
    activate();
    function activate() {
      vm.initProductInfo();
    }
    function initProductInfo() {
      var weshareId = angular.element(document.getElementById('shareProductView')).attr('data-weshare-id');
      vm.weshare = {};
      $http({
        method: 'GET',
        url: '/share_product_pool/get_share_product_detail/' + weshareId + '.json',
        cache: $templateCache
      }).success(function (data) {
        vm.weshare = data;
      }).error(function () {
        $log.log('get share product info error');
      });
    }

    function pageLoaded() {
      $rootScope.loadingPage = false;
    }
  }
})(window, window.angular);