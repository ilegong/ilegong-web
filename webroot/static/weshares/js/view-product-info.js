(function (window, angular) {
  angular.module('weshares')
    .controller('ShareProductViewCtrl', ShareProductViewCtrl);

  function ShareProductViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath) {
    var vm = this;
    vm.pageLoaded = pageLoaded;
    vm.initProductInfo = initProductInfo;
    vm.initViewFieldName = initViewFieldName;
    vm.toggleTag = toggleTag;
    vm.cloneShare = cloneShare;
    vm.viewImage = viewImage;
    vm.foretaste = foretaste;
    vm.staticFilePath = staticFilePath;
    activate();
    function activate() {
      vm.initProductInfo();
    }

    function initProductInfo() {
      var weshareId = angular.element(document.getElementById('shareProductView')).attr('data-weshare-id');
      vm.shareId = weshareId;
      vm.weshare = {};
      $http({
        method: 'GET',
        url: '/share_product_pool/get_share_product_detail/' + weshareId + '.json',
        cache: $templateCache
      }).success(function (data) {
        //$log.log(data);
        vm.weshare = data;
        vm.initViewFieldName();
      }).error(function () {
        $log.log('get share product info error');
      });
    }

    function foretaste() {
      if (vm.weshare['foretaste_share_id']) {
        window.location.href = '/weshares/view/' + vm.weshare['foretaste_share_id'];
      } else {
        alert('该商品没有试吃！请联系客服。');
      }
    }

    function toggleTag(tag) {
      var currentToggleState = vm.toggleState[tag];
      currentToggleState['open'] = !currentToggleState['open'];
      currentToggleState['statusText'] = currentToggleState['open'] ? '收起' : '展开';
    }

    function cloneShare() {
      $http({method: 'GET', url: '/share_product_pool/clone_share/' + vm.shareId}).success(function (data) {
        if (data['success']) {
          var newShareId = data['shareId'];
          window.location.href = '/weshares/view/' + newShareId;
        } else {
          if (data['reason']) {
            alert('您还没有登录，请登录后进行操作。');
            window.location.href = '/users/login'
          } else {
            alert('分享失败请联系管理员');
          }
        }
      }).error(function () {
        $log.log('clone share from product pool error')
      });
    }

    function viewImage(url) {
      wx.previewImage({
        current: url,
        urls: vm.weshare.images
      });
    }

    function initViewFieldName() {
      vm.shareStatusText = vm.weshare.status == 2 ? '无效' : '可用';
      vm.toggleState = {0: {open: true, statusText: '收起'}};
      _.each(vm.weshare.tags, function (value, key) {
        vm.toggleState[key] = {
          open: true,
          statusText: '收起'
        };
      });
    }

    function pageLoaded() {
      $rootScope.loadingPage = false;
    }
  }
})(window, window.angular);