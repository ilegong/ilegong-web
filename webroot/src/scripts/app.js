(function (window, angular) {
  var app = angular.module('weshares', ['infinite-scroll', 'module.services', 'module.filters', 'module.directives'])
    .constant('_', window._)
    .constant('staticFilePath', PYS.staticFilePath)
    .constant('shipTypes', {
      "101": "申通",
      "102": "圆通",
      "103": "韵达",
      "104": "顺丰",
      "105": "EMS",
      "106": "邮政包裹",
      "107": "天天",
      "108": "汇通",
      "109": "中通",
      "110": "全一",
      "111": "宅急送",
      "112": "全峰",
      "113": "快捷",
      "115": "城际快递",
      "132": "优速",
      "133": "增益快递",
      "134": "万家康",
      "135": "京东快递",
      "136": "德邦快递",
      "137": "自提",
      "138": "百富达",
      "139": "黑狗",
      "140": "E快送",
      "141": "国通快递",
      "142": "人人快递",
      "143": "百世汇通"
    })
    .config(configCompileProvider)
    .config(configHttpProvider)
    .config(extendLog)
    .config(['$sceDelegateProvider', function ($sceDelegateProvider) {
      $sceDelegateProvider.resourceUrlWhitelist(
        ['self', PYS.staticFilePath + '/**']
      )
    }])
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
      $delegate.log = function (msg, forceLog) {
        _log(msg);
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
