(function (window, angular) {
    angular.module('weshares')
        .controller('GetUserInfoCtr', GetUserInfoCtr);

    function GetUserInfoCtr($rootScope , $http , $attrs) {
        $rootScope.loadingPage = false;
        var vm = this;
        vm.shares = [];
        vm.attend = [];
        vm.sharesPage = 1;
        vm.attendPage = 1;
        vm.sharesOver = false;
        vm.attendOver = false;
        vm.loading = false;
        vm.isShowUnSubButton = false;
        vm.isSub = $attrs.sub;
        vm.sub = sub;
        vm.unSub = unSub;
        vm.showUnSubButton = showUnSubButton;
        vm.shareNextPage = shareNextPage;
        vm.attendNextPage = attendNextPage;
        vm.viewUser = viewUser;
        
        function attendNextPage(uid){
            if(vm.loading || vm.attendOver)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_attends/" + uid + '/' + vm.attendPage;
            $http({method: 'GET', url: url}).
            success(function (data, status) {
                if(data.length == 0)
                {
                    vm.attendOver = true;
                }else{
                    vm.attend = vm.attend.concat(data);
                    vm.attendPage += 1;
                }
            }).error(function (data, status) {});
            vm.loading = false;
        }

        function shareNextPage(uid){
            if(vm.loading || vm.sharesOver)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_shares/" + uid + '/' + vm.sharesPage;
            $http({method: 'GET', url: url}).
            success(function (data, status) {
                if(data.length == 0)
                {
                    vm.sharesOver = true;
                }else{
                    vm.shares = vm.shares.concat(data);
                    vm.sharesPage += 1;
                }
            }).error(function (data, status) {});
            vm.loading = false;
        }
        
        function sub(uid){
            vm.loading = true;
            UserSubscribe.sub(uid , vm);
            $http({method: 'GET', 'url': '/weshares/subscribe_sharer/' + uid}).success(function (data) {
                if (data['success']) {
                    afterSub();
                } else {
                    if (data['reason'] == 'not_sub') {
                        alert('请先关注我们的服务号');
                        window.location.href = "https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=403992659&idx=1&sn=714a1a5f0bb4940f895e60f2f3995544";
                    }
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }

        function unSub(uid) {
            vm.loading = true;
            $http({method: 'GET', url: '/weshares/unsubscribe_sharer/' + uid}).success(function (data) {
                if (data['success']) {
                    afterUnSub();
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
        
        function showUnSubButton(){
            vm.isShowUnSubButton = !vm.isShowUnSubButton;
        }

        function viewUser(uid){
            window.location.href = '/weshares/user_share_info/'+uid;
        }
    }
})(window, window.angular);