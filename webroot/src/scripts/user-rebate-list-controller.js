(function (window, angular) {
    angular.module('weshares')
        .controller('UserRebateListCtrl', UserCouponListCtrl);
    function UserCouponListCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        vm.loading = false;
        vm.dataOver = false;
        vm.myRebateNextPage = myRebateNextPage;
        vm.getRebateDetail = getRebateDetail;
        vm.toShareDetail = toShareDetail;
        vm.pageNum = 1;
        vm.rebates = [];
        active();
        function active() {
            $rootScope.loadingPage = false;
        }

        function getRebateDetail(data){
            if(data['reason'] == 1){
                return '您推荐的' + data['nickname'] + '报名了[' + data['title'] + ']';
            }
            if(data['reason'] == 2){
                return '您报名了[' + data['title'] + ']';
            }
        }

        function myRebateNextPage() {
            if (vm.loading || vm.dataOver) {
                return false;
            }
            vm.loading = true;
            var url = "/rebate_money/rebate_list/" + vm.pageNum;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.dataOver = true;
                } else {
                    vm.rebates = vm.rebates.concat(data);
                    vm.pageNum += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }

        function toShareDetail(data){
            window.location.href='/weshares/view/'+data['member_id']+'.html';
        }
    }
})(window, window.angular);