(function (window, angular) {
    angular.module('weshares')
        .controller('PayResultCtr', PayResultCtr);

    function PayResultCtr($rootScope) {
        var vm = this;
        $rootScope.loadingPage = false;
        $rootScope.proxies = [];

        vm.initProxies = function (uid) {
            $rootScope.proxies.push(uid);
        };
        vm.initUid = function (uid) {
            $rootScope.uid = uid;
        };
    }

})(window, window.angular);