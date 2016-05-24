(function (window, angular) {
  var app = angular.module('weshares', ['infinite-scroll', 'module.services', 'module.filters', 'module.directives'])
    .constant('_', window._)
    .config(configCompileProvider)
    .config(configHttpProvider)
    .config(extendLog)
    .config(['$sceDelegateProvider', function ($sceDelegateProvider) {
      $sceDelegateProvider.resourceUrlWhitelist(
        ['self', 'http://*.tongshijia.com/**']
      )
    }])
    .run(initApp)
    .controller('DefaultCtrl', DefaultCtrl);

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
      $delegate.log = function (msg, forceLog) {
        _log(msg);
        return this;
      };
      return $delegate;
    });
  }

  function initApp($rootScope, $http) {
    $rootScope._ = _;
    $rootScope.loadingPage = true;
    $rootScope.clickPage = function(){
      $rootScope.$broadcast('page_clicked', {});
    }
    $rootScope.checkHasUnRead = function(){
      $http.get('/share_opt/check_opt_has_new.json').success(function (data) {
        if (data['has_new']) {
          $rootScope.showUnReadMark = true;
        }
      });
    }

  }

  function DefaultCtrl($rootScope){
    $rootScope.loadingPage = false;
    $rootScope.checkHasUnRead();
  }
})(window, window.angular);
