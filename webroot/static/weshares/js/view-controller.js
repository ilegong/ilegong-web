(function (window, angular) {
    angular.module('weshares')
        .controller('WesharesViewCtrl', WesharesViewCtrl);

    function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache) {
        var vm = this;
        vm.nextStep = nextStep;
        activate();
        function activate() {
            $http({method: 'GET', url: '/weshares/detail/3', cache: $templateCache}).
                success(function(data, status) {
                    $log.log(data);
                    vm.addresses = data['addresses'];
                    vm.creator =data['creator'];
                    vm.info = data['info'];
                    vm.products = data['products'];
                }).
                error(function(data, status) {
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