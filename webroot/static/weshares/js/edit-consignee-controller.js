(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesEditConsigneeCtrl', WesharesEditConsigneeCtrl);

  function WesharesEditConsigneeCtrl($scope, CoreReactorChannel) {
    var vmc = this;

    vmc.selectConsignees = false;
    vmc.editConsignee = false;
    vmc.toConsigneeFormView = showConsigneeFormView;
    vmc.toBalanceView = toBalanceView;

    var vm = $scope.$parent.vm;
    active();
    function active() {
      CoreReactorChannel.onElevatedEvent($scope, 'EditConsignee', function () {
        vmc.selectConsignees = true;
      });
    }

    function toBalanceView(){
      vm.showBalanceView = true;
      vmc.selectConsignees = false;
      vmc.editConsignee = false;
    }

    function showConsigneeFormView() {
      vmc.selectConsignees = false;
      vmc.editConsignee = true;
    }
  }
})(window, window.angular);