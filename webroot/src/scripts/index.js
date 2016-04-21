(function (window, angular) {

  angular.module('weshares')
    .controller('IndexCtrl', IndexCtrl);


  function IndexCtrl($scope, $rootScope, $http, $log, $window) {
    var vm = this;
    vm.isSubscribed = isSubscribed;
    vm.unSubscribe = unSubscribe;
    vm.subscribe = subscribe;
    vm.clickSubscribedBtn = clickSubscribedBtn;
    vm.showUnSubscribeBtn = showUnSubscribeBtn;
    vm.clickPage = clickPage;

    activate();
    function activate() {
      vm.uid = -1;
      vm.proxies = [];
      $http.get('/users/get_id_and_proxies').success(function (data) {
        if (data.uid != null) {
          vm.uid = data.uid;
          vm.proxies = _.map(data.proxies, function (pid) {
            return {id: parseInt(pid), showUnSubscribeBtn: false};
          })
        }
        else{
          $log.log('User not logged in');
        }
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
      });
    }

    function isSubscribed(proxyId) {
      return _.any(vm.proxies, function(p){return p.id == proxyId});
    }

    function subscribe(proxyId) {
      if (vm.uid < 0) {
        $window.location.href = '/users/login';
        return;
      }

      if(vm.subscribeInProcess){
        return;
      }

      vm.subscribeInProcess = true;
      $http.get('/weshares/subscribe_sharer/' + proxyId + "/" + vm.uid).success(function (data) {
        if (data.success) {
          vm.proxies.push({id: proxyId, showUnSubscribeBtn: false});
        }
        vm.subscribeInProcess = false;
      }).error(function (data, e) {
        vm.subscribeInProcess = false;
        $log.log('Failed to get proxies: ' + e);
      });
    }

    function unSubscribe(proxyId) {
      if (vm.uid < 0) {
        $window.location.href = '/users/login';
        return;
      }

      if(vm.unSubscribeInProcess){
        return;
      }

      vm.unSubscribeInProcess = true;
      $http.get('/weshares/unsubscribe_sharer/' + proxyId + "/" + vm.uid).success(function (data) {
        if (data.success) {
          vm.proxies = _.reject(vm.proxies, function (proxy) {
            return proxy.id == proxyId
          });
        }
        vm.unSubscribeInProcess = false;
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
        vm.unSubscribeInProcess = false;
      });
    }

    function clickSubscribedBtn(proxyId, $event) {
      _.each(vm.proxies, function (p) {
        p.showUnSubscribeBtn = p.id == proxyId;
      });
      $event.stopPropagation();
    }

    function showUnSubscribeBtn(proxyId) {
      var proxy = _.find(vm.proxies, function (p) {
        return p.id == proxyId;
      });
      return !_.isEmpty(proxy) && proxy.showUnSubscribeBtn;
    }

    function clickPage() {
      _.each(vm.proxies, function (p) {
        p.showUnSubscribeBtn = false;
      });
    }
  }
})(window, window.angular);