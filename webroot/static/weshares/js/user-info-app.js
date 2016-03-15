(function (window, angular) {
  angular.module('module.filters', [])

})(window, window.angular);


(function (window, angular) {
  angular.module('module.services', [])

})(window, window.angular);

(function (window, angular) {
  angular.module('module.directives', [])
})(window, window.angular);

(function (window, angular) {
  var app = angular.module('UserInfo', ['infinite-scroll', 'module.services', 'module.filters', 'module.directives', 'me-lazyload'])
    .constant('_', window._)
    .config(configCompileProvider)
    .config(configHttpProvider)
    .config(extendLog)
    .run(initApp);

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
