(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesEditConsigneeCtrl', WesharesEditConsigneeCtrl);

  function WesharesEditConsigneeCtrl($scope, CoreReactorChannel, $log) {
    var vmc = this;

    vmc.showSelectConsignee = false;
    vmc.showEditConsigneeView = false;
    vmc.showEditConsigneeView = showEditConsigneeView;

    var vm = $scope.$parent.vm;
    active();
    function active() {
      CoreReactorChannel.onElevatedEvent($scope, 'EditConsignee', function () {
        vm.showBalanceView = false;
        vmc.showSelectConsignee = true;
      });
    }

    function showEditConsigneeView() {
      vmc.showSelectConsignee = false;
      vmc.showEditConsigneeView = true;
    }

  }
})(window, window.angular);