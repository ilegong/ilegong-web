(function (window, angular) {
    angular.module('weshares')
        .controller('UserCouponListCtrl', UserCouponListCtrl);
    function UserCouponListCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        active();
        function active() {
            $rootScope.loadingPage = false;
        }
    }
})(window, window.angular);