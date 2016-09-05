(function (window, angular) {
    angular.module('weshares')
        .controller('UserScoresCtrl', UserScoresCtrl);
    function UserScoresCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        vm.scoreList = {
            page: 1,
            scores: [],
            over: false,
            total: 0
        };
        vm.loadData = loadData;
        vm.loadingData = false;
        active();
        function active() {
            $rootScope.loadingPage = false;
        }

        function loadData() {
            var url = "/scores/score_list/" + vm.scoreList.page + ".json";
            vm.loadingData = true;
            $http({method: 'GET', url: url, cache: $templateCache}).success(function (data) {
                if (data.length == 0) {
                    vm.scoreList.over = true;
                } else {
                    vm.scoreList.scores = vm.scoreList.scores.concat(data);
                    vm.scoreList.page = vm.scoreList.page + 1;
                }
                vm.loadingData = false;
            }).error(function (data, status) {
                vm.loadingData = false;
            });
        }
    }
})(window, window.angular);