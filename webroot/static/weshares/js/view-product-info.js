(function (window, angular) {
  angular.module('weshares')
    .controller('ShareProductViewCtrl', ShareProductViewCtrl);

  function ShareProductViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath, PoolProductInfo) {
    var vm = this;
    vm.showDetailView = true;
    vm.initProductInfo = initProductInfo;
    vm.initViewFieldName = initViewFieldName;
    vm.toggleTag = toggleTag;
    vm.cloneShare = cloneShare;
    vm.viewImage = viewImage;
    vm.toBuyShare = toBuyShare;
    vm.initProductBuyBtn = initProductBuyBtn;
    vm.staticFilePath = staticFilePath;
    activate();
    function activate() {
      vm.initProductInfo();
    }

    function initProductInfo() {
      var weshareId = angular.element(document.getElementById('shareProductView')).attr('data-weshare-id');
      vm.shareId = weshareId;
      vm.weshare = {};
      PoolProductInfo.prepareProductInfo(weshareId, function (data) {
        vm.weshare = data;
        vm.initViewFieldName();
        vm.initProductBuyBtn();
        $rootScope.loadingPage = false;
      });
    }

    //初始化购买按钮
    function initProductBuyBtn() {
      if (vm.weshare['buy_config']) {
        var buyConfig = vm.weshare['buy_config'];
        if (buyConfig['try']) {
          vm.buyShareId = buyConfig['try'];
          vm.buyButtonText = '试吃申请';
        }
        if (buyConfig['buy']) {
          vm.buyShareId = buyConfig['buy'];
          vm.buyButtonText = '渠道价购买';
        }
      }
    }

    function toBuyShare() {
      if (vm.buyShareId) {
        window.location.href = '/weshares/view/' + vm.buyShareId;
      } else {
        alert('该商品没有试吃！请联系客服。');
      }
    }

    function toggleTag(tag) {
      var currentToggleState = vm.toggleState[tag];
      currentToggleState['open'] = !currentToggleState['open'];
      currentToggleState['statusText'] = currentToggleState['open'] ? '收起' : '展开';
    }

    //发起自己的分享
    function cloneShare() {
      if (vm.cloneShareProcessing) {
        return;
      }

      vm.cloneShareProcessing = true;
      $http({method: 'GET', url: '/share_product_pool/clone_share/' + vm.shareId}).success(function (data) {
        if (data['success']) {
          var newShareId = data['shareId'];
          window.location.href = '/weshares/view/' + newShareId;
        } else {
          if (data['reason']) {
            if (data['reason' == 'not_login']) {
              alert('您还没有登录，请登录后进行操作。');
              window.location.href = '/users/login';
            } else {
              alert(data['reason']);
            }
          } else {
            alert('开团失败，请联系管理员');
          }
        }
        vm.cloneShareProcessing = false;
      }).error(function () {
        $log.log('clone share from product pool error');
        alert('系统错误，请联系管理员');
        vm.cloneShareProcessing = false;
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
  }
})(window, window.angular);