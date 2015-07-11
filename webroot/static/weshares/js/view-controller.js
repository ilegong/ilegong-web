(function (window, angular) {
  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl);

  function WesharesViewCtrl($state, $scope, $rootScope, $log, $http, $templateCache, $stateParams) {
    var vm = this;
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.viewImage = viewImage;
    function viewImage(url){
      wx.previewImage({
        current: url,
        urls: vm.weshare.images
      });
    }
    activate();
    function activate() {
      $http({method: 'GET', url: '/weshares/detail/' + $stateParams.id, cache: $templateCache}).
        success(function (data, status) {
          $log.log(data);
          vm.weshare = data['weshare'];
          vm.ordersDetail = data['ordersDetail'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }
  }
})(window, window.angular);