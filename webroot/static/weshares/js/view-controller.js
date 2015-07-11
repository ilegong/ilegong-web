(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl);

  function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache) {
    var vm = this;
    vm.nextStep = nextStep;
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    activate();
    function activate() {
      $http({method: 'GET', url: '/weshares/detail/3', cache: $templateCache}).
        success(function (data, status) {
          $log.log(data);
          vm.weshare = data['weshare'];
          vm.ordersDetail = data['ordersDetail'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function nextStep() {
      if (_.isEmpty(vm.weshare.title)) {
        return false;
      }
      vm.showShippmentInfo = true;
    }
  }
})(window, window.angular);