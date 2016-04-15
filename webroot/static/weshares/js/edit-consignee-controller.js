(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesEditConsigneeCtrl', WesharesEditConsigneeCtrl);

  function WesharesEditConsigneeCtrl($scope, $http ,CoreReactorChannel) {
    var vmc = this;

    vmc.selectConsignees = false;
    vmc.editConsignee = false;
    vmc.showConsigneeFormView = showConsigneeFormView;
    vmc.showConsigneeListView = showConsigneeListView;
    vmc.toBalanceView = toBalanceView;

    var vm = $scope.$parent.vm;
    active();
    function active() {
      CoreReactorChannel.onElevatedEvent($scope, 'EditConsignee', function () {
        vmc.selectConsignees = true;
      });

    }

    function loadConsignees(){
      $http({method: 'GET', url: '/users/get_consignee_list.json'})
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

    function showConsigneeListView(){
      vmc.selectConsignees = true;
      vmc.editConsignee = false;
    }

  }
})(window, window.angular);