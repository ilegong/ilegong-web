/**
 * pool product info factory
 */
(function (window, angular) {
  angular.module('weshares').factory('PoolProductInfo', function ($http, $log, $templateCache) {
    return {
      prepareProductInfo: function (weshareId,callBackFunc) {
        $http({
          method: 'GET',
          url: '/share_product_pool/get_share_product_detail/' + weshareId + '.json',
          cache: $templateCache
        }).success(function (data) {
          callBackFunc(data);
        }).error(function () {
          $log.log('get share product info error');
          $rootScope.loadingPage = false;
        });
      }
    };
  });
})(window, window.angular);