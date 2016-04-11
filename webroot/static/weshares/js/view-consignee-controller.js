(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesConsigneeCtrl', WesharesConsigneeCtrl);

  function WesharesConsigneeCtrl($scope) {
    var vmc = this;
    vmc.getTabBarItemWidth = getTabBarItemWidth;

    vmc.getTabBarStyle = getTabBarStyle;

    var vm = vmc.parent;
    // 函数
    vm.showSelectSelfZitiAddressPageFunc = showSelectSelfZitiAddressPageFunc;
    vm.showSelectKuaiDiAddressPageFunc = showSelectKuaiDiAddressPageFunc;
    vm.showEditSelfZitiAddressPageFunc = showEditSelfZitiAddressPageFunc;
    vm.showEditKuaiDiAddressPageFunc = showEditKuaiDiAddressPageFunc;
    vm.showKuaiDiTabPageFunc = showKuaiDiTabPageFunc;
    vm.showSelfZitiTabPageFunc = showSelfZitiTabPageFunc;
    vm.showPysZitiTabPageFunc = showPysZitiTabPageFunc;
    vm.showPinTuanTabPageFunc = showPinTuanTabPageFunc;

    // 默认一部分初始数据, 将来从接口的json修改
    vm.hasDefaultKuaiDiDeliveryAddress = false;
    vm.hasDefaultSelfZitiDeliveryAddress = false;

    active();
    function active() {
    }

    function getTabBarItemWidth() {
      var shipTypes = ['pin_tuan', 'kuai_di', 'self_ziti', 'pys_ziti'];
      var num = 0;
      _.each(shipTypes, function (item) {
        if (vmc.parent.weshareSettings[item] && vmc.parent.weshareSettings[item]['status'] == 1) {
          num = num + 1;
        }
      });
      return num ? 100 / num : 100;
    }


    function getTabBarStyle() {
      vmc.tabBarItemWidth = getTabBarItemWidth();
      return 'width: ' + vmc.tabBarItemWidth + '%';
    }

    function showSelectKuaiDiAddressPageFunc()
    {
      vm.showTabPage = false;
      vm.showSelectKuaiDiAddressPage = true;
      vm.showEditKuaiDiAddressPage = false;
      vm.showSelectSelfZitiAddressPage = false;
      vm.showEditSelfZitiAddressPage = false;
    }

    function showEditKuaiDiAddressPageFunc()
    {
      vm.showTabPage = false;
      vm.showSelectKuaiDiAddressPage = false;
      vm.showEditKuaiDiAddressPage = true;
      vm.showSelectSelfZitiAddressPage = false;
      vm.showEditSelfZitiAddressPage = false;
    }

    function showSelectSelfZitiAddressPageFunc()
    {
      vm.showTabPage = false;
      vm.showSelectKuaiDiAddressPage = false;
      vm.showEditKuaiDiAddressPage = false;
      vm.showSelectSelfZitiAddressPage = true;
      vm.showEditSelfZitiAddressPage = false;
    }

    function showEditSelfZitiAddressPageFunc()
    {
      vm.showTabPage = false;
      vm.showSelectKuaiDiAddressPage = false;
      vm.showEditKuaiDiAddressPage = false;
      vm.showSelectSelfZitiAddressPage = false;
      vm.showEditSelfZitiAddressPage = true;
    }

    function showKuaiDiTabPageFunc() {
      vm.showSelfZitiAddress = false;
      vm.showEditAddress = false;
      vm.showKuaiDiTab = true;
      vm.showSelfZitiTab = false;
      vm.showPysZitiTab = false;
      vm.showPinTuanTab = false;
    }

    function showSelfZitiTabPageFunc() {
      vm.showSelfZitiAddress = false;
      vm.showEditAddress = false;
      vm.showKuaiDiTab = false;
      vm.showSelfZitiTab = true;
      vm.showPysZitiTab = false;
      vm.showPinTuanTab = false;
    }

    function showPysZitiTabPageFunc() {
      vm.showSelfZitiAddress = false;
      vm.showEditAddress = false;
      vm.showKuaiDiTab = false;
      vm.showSelfZitiTab = false;
      vm.showPysZitiTab = true;
      vm.showPinTuanTab = false;
    }

    function showPinTuanTabPageFunc() {
      vm.showSelfZitiAddress = false;
      vm.showEditAddress = false;
      vm.showKuaiDiTab = false;
      vm.showSelfZitiTab = false;
      vm.showPysZitiTab = false;
      vm.showPinTuanTab = true;
    }

    // To protected access as vmc.parent
    Object.defineProperty(vmc, 'parent', {
      get: function () {
        return $scope.$parent.vm;
      }
    });
  }
})(window, window.angular);