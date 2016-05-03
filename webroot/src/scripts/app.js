(function (window, angular) {
  angular.module('module.filters', [])
      .filter('thumb', function() {
    return function(input, type) {
      input = input || '';
      if(input.indexOf('/s/') >= 0 || input.indexOf('/m/') >= 0){
        return input;
      }

      var thumb_type = 's';
      if(type == 'm'){
        thumb_type = 'm';
      }

      if(input.indexOf('avatar/') >= 0){
        return input.replace('/avatar/', '/avatar/' + thumb_type + '/');
      }
      if(input.indexOf('/images/') >= 0){
        return input.replace('/images/', '/images/' + thumb_type + '/');
      }

      return input;
    };
  })
})(window, window.angular);
(function (window, angular) {
  angular.module('module.directives', [])
    .directive('fallbackSrc', function () {
      var fallbackSrc = {
        link: function postLink(scope, iElement, iAttrs) {
          iElement.bind('error', function () {
            var oldSrc = angular.element(this).attr("src");
            if(oldSrc!=iAttrs.fallbackSrc){
              angular.element(this).attr("src", iAttrs.fallbackSrc);
            }
          });
        }
      };
      return fallbackSrc;
    }).directive('stringToNumber', function () {
      return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
          ngModel.$parsers.push(function (value) {
            return '' + value;
          });
          ngModel.$formatters.push(function (value) {
            return parseFloat(value);
          });
        }
      };
    });

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


  //多个控制器共享数据
  app.factory('CoreReactorChannel', function ($rootScope) {
    var elevatedEvent = function (event, data) {
      $rootScope.$broadcast(event, data);
    };

    // subscribe to elevatedCoreTemperature event.
    // note that you should require $scope first
    // so that when the subscriber is destroyed you
    // don't create a closure over it, and te scope can clean up.
    var onElevatedEvent = function ($scope, event, handler) {
      $scope.$on(event, function (e, data) {
        // note that the handler is passed the problem domain parameters
        handler(data);
      });
    };
    // other CoreReactorChannel events would go here.
    return {
      elevatedEvent: elevatedEvent,
      onElevatedEvent: onElevatedEvent
    };
  });

  // share order constructor function to encapsulate HTTP and pagination logic
  app.factory('ShareOrder', function ($http, $templateCache) {

    var ShareOrder = function () {
      this.orders = [];
      this.order_cart_map = {};
      this.rebate_logs = {};
      this.users = {};
      this.levelData = {};
      this.shareId = 0;
      this.busy = false;
      this.noMore = false;
      this.page = 1;
      this.pageInfo = {};
      //this.referShareId = 0;
      //this.loadedShareIds = [];
      this.orderComments = {};
      this.orderCommentReplies = {};
      this.combineComment = 1;
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
      }
      this.busy = true;
      var url = "/weshares/get_share_order_by_page/" + this.shareId + "/" + this.page + ".json?combineComment=" + this.combineComment;
      $http({method: 'GET', url: url, cache: $templateCache}).
        success(function (data, status) {
          this.busy = false;
          this.orders = this.orders.concat(data['orders']);
          this.order_cart_map = merge_options(this.order_cart_map, data['order_cart_map']);
          this.rebate_logs = merge_options(this.rebate_logs, data['rebate_logs']);
          this.users = merge_options(this.users, data['users']);
          this.levelData = merge_options(this.levelData, data['level_data']);
          if(data['page_info']){
            this.pageInfo = data['page_info'];
            //this.referShareId = data['page_info']['refer_share_id'];
          }
          if(data['comment_data']){
            this.orderComments = merge_options(this.orderComments, data['comment_data']['order_comments']);
            this.orderCommentReplies = merge_options(this.orderCommentReplies, data['comment_data']['comment_replies']);
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

  app.constant('shipTypes', {
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
  });

  app.filter('unsafe', function ($sce) {
    return function (val) {
      return $sce.trustAsHtml(val);
    };
  });

  app.directive('readMore', function () {
    return {
      restrict: 'A',
      transclude: true,
      replace: true,
      scope: {
        moreText: '@',
        lessText: '@',
        words: '@',
        ellipsis: '@',
        char: '@',
        limit: '@',
        content: '@'
      },
      link: function (scope, elem, attr, ctrl, transclude) {
        var moreText = angular.isUndefined(scope.moreText) ? ' <a class="read-more">Read More...</a>' : ' <a class="read-more">' + scope.moreText + '</a>',
          lessText = angular.isUndefined(scope.lessText) ? ' <a class="read-less">Less ^</a>' : ' <a class="read-less">' + scope.lessText + '</a>',
          ellipsis = angular.isUndefined(scope.ellipsis) ? '' : scope.ellipsis,
          limit = angular.isUndefined(scope.limit) ? 150 : scope.limit;
        attr.$observe('content', function (str) {
          readmore(str);
        });
        transclude(scope.$parent, function (clone, scope) {
          readmore(clone.text().trim());
        });
        function readmore(text) {
          var text = text,
            orig = text,
            regex = /\s+/gi,
            charCount = text.length,
            wordCount = text.trim().replace(regex, ' ').split(' ').length,
            countBy = 'char',
            count = charCount,
            foundWords = [],
            markup = text,
            more = '';
          if (!angular.isUndefined(attr.words)) {
            countBy = 'words';
            count = wordCount;
          }
          if (countBy === 'words') { // Count words
            foundWords = text.split(/\s+/);
            if (foundWords.length > limit) {
              text = foundWords.slice(0, limit).join(' ') + ellipsis;
              more = foundWords.slice(limit, count).join(' ');
              markup = '<div class="less-text">' + text + moreText + '</div><div class="more-text">' + orig + lessText + '</div>';
            }
          } else { // Count characters
            if (count > limit) {
              text = orig.slice(0, limit) + ellipsis;
              text = text.replace(/<\/?[^>]+(>|$)/g, "");
              more = orig.slice(limit, count);
              markup = '<div class="less-text">' + text + moreText + '</div><div class="more-text">' + orig + lessText + '</div>';
            }
          }
          elem.append(markup);
          angular.element(document.getElementsByClassName('read-more')[0]).bind('click', function () {
            document.getElementsByClassName('less-text')[0].style.display = 'none';
            document.getElementsByClassName('read-more')[0].style.display = 'none';
            document.getElementsByClassName('more-text')[0].style.display = 'block';
            document.getElementsByClassName('read-less')[0].style.display = 'block';
            videoIFrame=document.getElementById('video');
            if(videoIFrame){video.style.dislay='block';}
          });
          angular.element(document.getElementsByClassName('read-less')[0]).bind('click', function () {
            document.getElementsByClassName('less-text')[0].style.display = 'block';
            document.getElementsByClassName('read-more')[0].style.display = 'block';
            document.getElementsByClassName('more-text')[0].style.display = 'none';
            document.getElementsByClassName('read-less')[0].style.display = 'none';
            videoIFrame=document.getElementById('video');
            if(videoIFrame){video.style.dislay='none';}
          });
        }
      }
    };
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
