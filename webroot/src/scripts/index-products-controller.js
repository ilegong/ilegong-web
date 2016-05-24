(function (window, angular) {

  angular.module('weshares')
    .controller('IndexCtrl', IndexCtrl);


  function IndexCtrl($scope, $rootScope, $http, $log, $attrs, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.checkHasUnRead = checkHasUnRead;
    vm.getSummary = getSummary;

    activate();
    function activate() {
      $rootScope.showUnReadMark = false;
      $rootScope.proxies = [];
      $rootScope.loadingPage=false;
      vm.uid = -1;
      vm.checkHasUnRead();
      $http.get('/users/get_id_and_proxies').success(function (data) {
        $log.log(data);
        if (data.uid != null) {
          $rootScope.uid = data.uid;
          $rootScope.proxies = _.map(data.proxies, function (pid) {
            return parseInt(pid);
          })
        }
        else {
          $log.log('User not logged in');
        }
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
      });

      var tag = $attrs.tag;
      vm.indexProducts = [];
      $http.get('/index_products/index_products/' + tag).success(function (data) {
        $log.log(data);
        vm.indexProducts = _.map(data, function (p) {
          return {'id': p.IndexProduct.id, 'shareId': p.IndexProduct.share_id, 'summary': p.IndexProduct.summary};
        });
      }).error(function (data, e) {
        $log.log('Failed to get index products: ' + e);
      });
    }

    function getSummary(shareId) {
      var indexProduct = _.find(vm.indexProducts, function (p) {
        return p.shareId == shareId;
      });
      if (_.isEmpty(indexProduct)) {
        return {orders_count: 0, view_count: 0, orders_and_creators: []};
      }
      return indexProduct.summary;
    }

    function checkHasUnRead() {
      $http.get('/share_opt/check_opt_has_new.json').success(function (data) {
        if (data['has_new']) {
          $rootScope.showUnReadMark = true;
        }
      });
    }
  }
})
(window, window.angular);