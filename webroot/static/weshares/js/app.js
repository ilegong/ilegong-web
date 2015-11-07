(function (window, angular) {
  angular.module('module.filters', []);
})(window, window.angular);

(function (window, angular) {
  angular.module('module.directives', []);
})(window, window.angular);

(function (window, angular) {
  angular.module('module.services', [])
    .service('Utils', Utils);

  function Utils() {
    return {
      isBlank: isBlank,
      isMobileValid: isMobileValid,
      isNumber: isNumber,
      toPercent: toPercent
    };
    function isMobileValid(mobile) {
      return /^1\d{10}$/.test(mobile);
    }

    function isBlank(str) {
      return (!str || /^\s*$/.test(str));
    }

    function isNumber(n) {
      return !isNaN(n);
    }

    function toPercent(value) {
      return Math.min(Math.round(value * 10000) / 100, 100);
    }
  }
})(window, window.angular);

(function (window, angular) {
  var app = angular.module('weshares', ['infinite-scroll', 'module.services', 'module.filters', 'module.directives', 'me-lazyload'])
    .constant('_', window._)
    .config(configCompileProvider)
    .config(configHttpProvider)
    .config(extendLog)
    .config(['$sceDelegateProvider', function ($sceDelegateProvider) {
      $sceDelegateProvider.resourceUrlWhitelist(
        ['self', PYS.staticFilePath + '/**']
      )
    }])
    .run(initApp);


  // share order constructor function to encapsulate HTTP and pagination logic
  app.factory('ShareOrder', function ($http, $templateCache) {

    var ShareOrder = function () {
      this.orders = [];
      this.order_cart_map = {};
      this.rebate_logs = {};
      this.users = {};
      this.shareId = 0;
      this.busy = false;
      this.noMore = false;
      this.page = 1;
      this.pageInfo = {};
    };

    /**
     * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
     * @param obj1
     * @param obj2
     * @returns obj3 a new object based on obj1 and obj2
     */
    function merge_options(obj1,obj2){
      var obj3 = {};
      for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
      for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
      return obj3;
    }

    ShareOrder.prototype.nextPage = function () {
      if (this.busy||this.noMore) return;
      if(this.page > this.pageInfo['page_count']){
        this.noMore = true;
        return;
      }
      this.busy = true;
      var url = "/weshares/get_share_order_by_page/" + this.shareId + "/" + this.page + ".json";
      $http({method: 'GET', url: url, cache: $templateCache}).
        success(function (data, status) {
          this.busy = false;
          this.orders = this.orders.concat(data['orders']);
          this.order_cart_map = merge_options(this.order_cart_map, data['order_cart_map']);
          this.rebate_logs = merge_options(this.rebate_logs, data['rebate_logs']);
          this.users = merge_options(this.users, data['users']);
          if(data['page_info']){
            this.pageInfo = data['page_info'];
          }
          this.page = this.page+1;
        }.bind(this)).
        error(function (data, status) {

        });
    };
    return ShareOrder;
  });

  //define static file path
  app.constant('staticFilePath', PYS.staticFilePath);

  app.filter('unsafe', function ($sce) {
    return function (val) {
      return $sce.trustAsHtml(val);
    };
  });

  app.directive('elemReady', function ($parse) {
    return {
      restrict: 'A',
      link: function ($scope, elem, attrs) {
        elem.ready(function () {
          $scope.$apply(function () {
            var func = $parse(attrs.elemReady);
            func($scope);
          })
        })
      }
    }
  });


  /* @ngInject */
  function configCompileProvider($compileProvider) {
    $compileProvider.imgSrcSanitizationWhitelist(/^\s*(https|file|blob|cdvfile|http|chrome-extension|blob:chrome-extension):|data:image\//);
  }

  /* @ngInject */
  function configHttpProvider($httpProvider) {
    $httpProvider.defaults.headers.common['Content-Type'] = 'application/json';
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/json';
  }

  /* @ngInject */
  function extendLog($provide) {
    $provide.decorator('$log', function ($delegate, $injector) {
      var _log = $delegate.log;
      var _warn = $delegate.warn;
      var _info = $delegate.info;
      var _debug = $delegate.debug;
      var _error = $delegate.error;
      var addMessage = function (message, forceLog) {
        var $rootScope = $injector.get("$rootScope");
        $rootScope.config = $rootScope.config || {logMode: false};
        if ($rootScope.config.logMode || forceLog) {
          $rootScope.messages = $rootScope.messages || [];
          $rootScope.messages.push(message);
        }
        return message;
      };

      $delegate.log = function (msg, forceLog) {
        _log(addMessage(msg, forceLog || false));
        return this;
      };
      $delegate.warn = function (msg, forceLog) {
        _warn(addMessage(msg, forceLog || false));
        return this;
      };
      $delegate.info = function (msg, forceLog) {
        _info(addMessage(msg, forceLog || false));
        return this;
      };
      $delegate.debug = function (msg, forceLog) {
        _debug(addMessage(msg, forceLog || false));
        return this;
      };
      $delegate.error = function (msg, forceLog) {
        _error(addMessage(msg, forceLog || false));
        return this;
      };

      return $delegate;
    });
  }

  function initApp($rootScope) {
    $rootScope._ = _;
    $rootScope.loadingPage = true;
  }
})(window, window.angular);
