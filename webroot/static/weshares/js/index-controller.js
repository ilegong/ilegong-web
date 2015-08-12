(function (window, angular, wx) {
  angular.module('weshares')
    .controller('WesharesIndexCtrl', WesharesIndexCtrl);

  function WesharesIndexCtrl($scope, $rootScope, $log, $http, Utils) {
    var vm = this;
    activate();
    function activate() {
      $log.log('hello world');
    }
  }
})(window, window.angular, window.wx);