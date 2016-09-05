(function (window, angular) {
    angular.module('weshares')
        .controller('UserScoresCtrl', UserScoresCtrl);
    function UserScoresCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        active();
        function active() {
            $rootScope.loadingPage = false;
        }
    }
})(window, window.angular);