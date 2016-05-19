(function (window, angular) {
  angular.module('weshares')
    .controller('ShareOptIndexController', ShareOptIndexController);

  function ShareOptIndexController($rootScope, $http, $attrs, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    activate();

    function activate() {
    }
  }
})(window, window.angular);