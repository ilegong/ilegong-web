(function (window, angular) {
    angular.module('weshares')
        .controller('PayResultCtr', PayResultCtr);

    function PayResultCtr($rootScope, $http, Utils) {
        var vm = this;
        $rootScope.loadingPage = false;
    }

})(window, window.angular);