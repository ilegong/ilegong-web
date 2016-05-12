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
        vm.focus_share = true;
        vm.focus_attend = false;
        vm.isShowUnSubButton = false;
        vm.isSub = $attrs.sub;
        vm.mySharesIng = [];
        vm.mySharesIngPage = 1;
        vm.mySharesEnd = [];
        vm.mySharesEndPage = 1;
        vm.mySharesBalance = [];
        vm.mySharesBalancePage = 1;
        vm.sub = sub;
        vm.unSub = unSub;
        vm.showUnSubButton = showUnSubButton;
        vm.shareNextPage = shareNextPage;
        vm.attendNextPage = attendNextPage;
        vm.viewUser = viewUser;
        vm.showShares = showShares;
        vm.initUid = initUid;
        vm.focunShare = focunShare;
        vm.focusAttend = focusAttend;
        vm.changeAvatar = changeAvatar;
        vm.goToComment = goToComment;
        vm.goToShare = goToShare;
        vm.getMyShares = getMyShares;

        function getMyShares()
        {
            
        }


        function goToShare(id){
            window.location.href = '/weshares/view/' + id;
        }

        function goToComment(){
            window.location.href = '/weshares/u_comment/' + vm.uid;
        }

        function changeAvatar(){
            window.location.href = '/users/change_avatar?ref=/weshares/user_share_info';
        }

        function focunShare() {
            vm.focus_share = true;
            vm.focus_attend = false;
        }
        function focusAttend()
        {
            vm.focus_share = false;
            vm.focus_attend = true;
        }
        
        function initUid(uid){
            vm.uid = uid;
        }
        function showShares()
        {
            console.log(vm.shares);
        }
        function attendNextPage(){
            if(vm.loading || vm.attendOver)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_attends/" + vm.uid + '/' + vm.attendPage;
            $http({method: 'GET', url: url}).
            success(function (data, status) {
                if(data.length == 0)
                {
                    vm.attendOver = true;
                }else{
                    vm.attend = vm.attend.concat(data);
                    vm.attendPage += 1;
                }
                console.log(vm.attend);
                vm.loading = false;
            }).error(function (data, status) {
                vm.loading = false;
            });
        }

        function shareNextPage(){
            if(vm.loading || vm.sharesOver)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_shares/" + vm.uid + '/' + vm.sharesPage;
            $http({method: 'GET', url: url}).
            success(function (data, status) {
                if(data.length == 0)
                {
                    vm.sharesOver = true;
                }else{
                    vm.shares = vm.shares.concat(data);
                    vm.sharesPage += 1;
                }
                console.log(vm.shares);
                vm.loading = false;
            }).error(function (data, status) {
                vm.loading = false;
            });
        }
        
        function sub(){
            vm.loading = true;
            $http({method: 'GET', 'url': '/weshares/subUser/' + vm.uid}).success(function (data) {
                if (data['success']) {
                    vm.isSub = 1;
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

        function unSub() {
            vm.loading = true;
            $http({method: 'GET', url: '/weshares/unSubUser/' + vm.uid}).success(function (data) {
                if (data['success']) {
                    vm.isSub = 0;
                }else{
                    alert('取消失败');
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
            vm.isShowUnSubButton = false;
        }
        
        function showUnSubButton(){
            vm.isShowUnSubButton = !vm.isShowUnSubButton;
        }

        function viewUser(uid){
            window.location.href = '/weshares/user_share_info/'+uid;
        }
    }
})(window, window.angular);