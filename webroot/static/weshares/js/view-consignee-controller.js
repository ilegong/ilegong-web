(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesConsigneeCtrl', WesharesConsigneeCtrl);

  function WesharesConsigneeCtrl($scope, CoreReactorChannel) {
    var vmc = this;
    vmc.getTabBarItemWidth = getTabBarItemWidth;
    vmc.getTabBarStyle = getTabBarStyle;
    vmc.changeShipTab = changeShipTab;
    vmc.tabBarItemWidth = 33.3;
    vmc.toEditConsigneeView = toEditConsigneeView;
    var vm = $scope.$parent.vm;
    active();
    function active() {
      vm.selectShipType = getSelectTypeDefaultVal();
      vmc.tabBarItemWidth = getTabBarItemWidth();
    }

    function getTabBarItemWidth() {
      var shipTypes = ['kuai_di', 'self_ziti', 'pys_ziti'];
      var num = 0;
      _.each(shipTypes, function (item) {
        if (vm.weshareSettings[item] && vm.weshareSettings[item]['status'] == 1) {
          num = num + 1;
        }
      });
      return num ? 100 / num : 100;
    }

    function getTabBarStyle() {
      return 'width: ' + vmc.tabBarItemWidth + '%';
    }

    function changeShipTab(type) {
      vm.selectShipType = type;
    }

    function toEditConsigneeView() {
      vm.showBalanceView = false;
      vm.showEditConsigneeView = true;
      CoreReactorChannel.elevatedEvent('EditConsignee', {});
    }

    function getSelectTypeDefaultVal() {
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

  }
})(window, window.angular);