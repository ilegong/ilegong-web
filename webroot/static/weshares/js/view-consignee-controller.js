(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesConsigneeCtrl', WesharesConsigneeCtrl);

  function WesharesConsigneeCtrl($scope) {
    var vmc = this;
    vmc.getTabBarItemWidth = getTabBarItemWidth;
    vmc.getTabBarStyle = getTabBarStyle;
    vmc.tabBarItemWidth = 33.3;
    var vm = vmc.parent;
    $scope.$on('shareDataHasLoad', function () {
      //初始化选择哪种快递方式
      vm.selectShipType = getSelectTypeDefaultVal();
      vmc.tabBarItemWidth = getTabBarItemWidth();
    });

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
      return 'width: ' + vmc.tabBarItemWidth + '%';
    }


    function getSelectTypeDefaultVal() {
      if (vm.weshareSettings.pin_tuan && vm.weshareSettings.pin_tuan.status == 1) {
        vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
        return 3;
      }
      if (vm.weshareSettings.kuai_di && vm.weshareSettings.kuai_di.status == 1) {
        return 0;
      }
      if (vm.weshareSettings.self_ziti && vm.weshareSettings.self_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
        return 1;
      }
      if (vm.weshareSettings.pys_ziti && vm.weshareSettings.pys_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
        return 2;
      }
      return -1;
    }

    // To protected access as vmc.parent
    Object.defineProperty(vmc, 'parent', {
      get: function () {
        return $scope.$parent.vm;
      }
    });
  }
})(window, window.angular);