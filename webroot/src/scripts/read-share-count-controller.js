/**
 * Created by ellipsis on 16/7/15.
 */
(function (window, angular) {
    angular.module('weshares')
        .controller('ReadShareCountCtr', ReadShareCountCtr);

    function ReadShareCountCtr($rootScope, $http) {
        var vm = this;
        $rootScope.loadingPage = false;
        vm.loading = false;
        vm.shares = {
            list: [],
            page: 1,
            over: false
        };
        vm.read = {
            list: [],
            page: 1,
            over: false
        };
        vm.sharers = {
            list: [],
            page: 1,
            over:false
        };

        vm.loadNextPage = loadNextPage;
        vm.loadReadNextPage = loadReadNextPage;
        vm.loadSharerNextPage = loadSharerNextPage;
        vm.goShareDetail = goShareDetail;
        vm.goReadDetail = goReadDetail;
        vm.doBack = doBack;
        vm.goShareView = goShareView;

        function goShareView(id){
            window.location.href='/weshares/view/'+id+'.html?';
        }

        function doBack(){
            window.location.href='/weshares/entrance.html';
        }

        function goShareDetail(id) {
            window.location.href = '/weshares/share_count/'+id;
        }

        function goReadDetail(id) {
            window.location.href = '/weshares/read_count/'+id;
        }
        
        function loadReadNextPage(id){
            if (vm.loading || vm.read.over) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/read_count_api/" + id + '/' + vm.read.page;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.read.over = true;
                } else {
                    vm.read.list = vm.read.list.concat(data);
                    vm.read.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
        
        function loadSharerNextPage(id){
            if (vm.loading || vm.sharers.over) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/share_count_api/" + id + '/' + vm.sharers.page;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.sharers.over = true;
                } else {
                    vm.sharers.list = vm.sharers.list.concat(data);
                    vm.sharers.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }

        function loadNextPage() {
            if (vm.loading || vm.shares.over) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/read_share_count_api/" + vm.shares.page;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.shares.over = true;
                } else {
                    vm.shares.list = vm.shares.list.concat(data);
                    vm.shares.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
    }
})(window, window.angular);