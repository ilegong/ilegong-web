(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesConsigneeCtrl', WesharesConsigneeCtrl);

  function WesharesConsigneeCtrl($scope) {
    var vmc = this;
    vmc.getTabBarItemWidth = getTabBarItemWidth;
    vmc.getTabBarStyle = getTabBarStyle;
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

    // To protected access as vmc.parent
    Object.defineProperty(vmc, 'parent', {
      get: function () {
        return $scope.$parent.vm;
      }
    });
  }
})(window, window.angular);