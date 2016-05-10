(function (window, angular) {
  angular.module('module.services', [])
    .service('Utils', Utils)
    .service('CoreReactorChannel', CoreReactorChannel)
    .service('ShareOrder', ShareOrder);

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

  function CoreReactorChannel($rootScope) {
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
  }

  function ShareOrder($http, $templateCache) {

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
    function merge_options(obj1, obj2) {
      var obj3 = {};
      for (var attrname in obj1) {
        obj3[attrname] = obj1[attrname];
      }
      for (var attrname in obj2) {
        obj3[attrname] = obj2[attrname];
      }
      return obj3;
    }

    ShareOrder.prototype.nextPage = function () {
      if (this.busy || this.noMore) return;
      if (this.page > this.pageInfo['page_count']) {
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
          if (data['page_info']) {
            this.pageInfo = data['page_info'];
            //this.referShareId = data['page_info']['refer_share_id'];
          }
          if (data['comment_data']) {
            this.orderComments = merge_options(this.orderComments, data['comment_data']['order_comments']);
            this.orderCommentReplies = merge_options(this.orderCommentReplies, data['comment_data']['comment_replies']);
          }
          this.page = this.page + 1;
        }.bind(this)).
        error(function (data, status) {

        });
    };
    return ShareOrder;
  }
})(window, window.angular);