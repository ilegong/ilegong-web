(function (window, angular) {
    angular.module('weshares')
        .controller('WesharesViewCtrl', YourCtrl);

    function YourCtrl($scope, $rootScope, $log, $http) {
        var vm = this;
        vm.nextStep = nextStep;
        activate();
        function activate() {
            vm.showShippmentInfo = false;
        }
        function nextStep() {
            if (_.isEmpty(vm.weshare.title)) {
                return false;
            }
            vm.showShippmentInfo = true;
        }
    }
})(window, window.angular);