(function (window, angular) {
  angular.module('weshares')
    .controller('SubscriptionController', SubscriptionController);

  function SubscriptionController($rootScope, $http, $log) {
    var sub = this;
    sub.isSubscribed = isSubscribed;
    sub.unSubscribe = unSubscribe;
    sub.subscribe = subscribe;
    sub.clickSubscribedBtn = clickSubscribedBtn;
    sub.showUnSubscribeBtn = showUnSubscribeBtn;

    activate();
    function activate() {
      $rootScope.clickPage = function() {
        _.each($rootScope.proxies, function (p) {
          p.showUnSubscribeBtn = false;
        });
      }
    }

    function isSubscribed(proxyId) {
      return _.any($rootScope.proxies, function (p) {
        return p.id == proxyId
      });
    }

    function subscribe(proxyId) {
      if ($rootScope.uid < 0) {
        $window.location.href = '/users/login';
        return;
      }

      if (sub.subscribeInProcess) {
        return;
      }

      sub.subscribeInProcess = true;
      $http.get('/weshares/subscribe_sharer/' + proxyId + "/" + $rootScope.uid).success(function (data) {
        if (data.success) {
          $rootScope.proxies.push({id: proxyId, showUnSubscribeBtn: false});
        }
        sub.subscribeInProcess = false;
      }).error(function (data, e) {
        sub.subscribeInProcess = false;
        $log.log('Failed to get proxies: ' + e);
      });
    }

    function unSubscribe(proxyId) {
      if ($rootScope.uid < 0) {
        $window.location.href = '/users/login';
        return;
      }

      if (sub.unSubscribeInProcess) {
        return;
      }

      sub.unSubscribeInProcess = true;
      $http.get('/weshares/unsubscribe_sharer/' + proxyId + "/" + $rootScope.uid).success(function (data) {
        if (data.success) {
          $rootScope.proxies = _.reject($rootScope.proxies, function (proxy) {
            return proxy.id == proxyId
          });
        }
        sub.unSubscribeInProcess = false;
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
        sub.unSubscribeInProcess = false;
      });
    }

    function clickSubscribedBtn(proxyId, $event) {
      _.each($rootScope.proxies, function (p) {
        p.showUnSubscribeBtn = p.id == proxyId;
      });
      $event.stopPropagation();
    }

    function showUnSubscribeBtn(proxyId) {
      var proxy = _.find($rootScope.proxies, function (p) {
        return p.id == proxyId;
      });
      return !_.isEmpty(proxy) && proxy.showUnSubscribeBtn;
    }
  }
})(window, window.angular);