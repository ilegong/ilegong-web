(function (window, angular) {
    angular.module('weshares')
        .controller('GetUserInfoCtr', GetUserInfoCtr);

    function GetUserInfoCtr($rootScope , $http , $attrs ,staticFilePath) {
        var vm = this;
        $rootScope.loadingPage = false;
        vm.focus = 'share';
        vm.staticFilePath = staticFilePath;
        vm.uid = $attrs.uid;
        vm.loading = false;
        vm.shares = {
            list : [],
            page : 1,
            over : false
        };
        vm.attends = {
            list : [],
            page : 1,
            over : false
        };
        vm.isShowUnSubButton = false;
        vm.isSub = $attrs.sub;
        vm.mine = {
            sharesIng : [],
            sharesIngPage : 1,
            sharesIngOver : false,
            sharesEnd : [],
            sharesEndPage : 1,
            sharesEndOver : false,
            sharesBalance : [],
            sharesBalancePage : 1,
            sharesBalanceOver : false,
            order : [],
            orderPage : 1,
            orderOver : false,
            tmpShares : [],
            tmpSharesOver : false
        };
        vm.sub = sub;
        vm.unSub = unSub;
        vm.showUnSubButton = showUnSubButton;
        vm.shareNextPage = shareNextPage;
        vm.attendNextPage = attendNextPage;
        vm.myShareNextPage = myShareNextPage;
        vm.myOrderNextPage = myOrderNextPage;
        vm.viewUser = viewUser;
        vm.changeAvatar = changeAvatar;
        vm.goToComment = goToComment;
        vm.goToShare = goToShare;
        vm.delShare = delShare;
        vm.stopShare = stopShare;

        function delShare(id){
            if(vm.loading)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/delete_share/"+id;
            $http.get(url).success(function(data){
                if(data.success)
                {
                    var tmp = [];
                    for(var i = 0 ; i < vm.mine.tmpShares.length ; i++)
                    {
                        if(parseInt(vm.mine.tmpShares[i].id) != id)
                        {
                            tmp.push(vm.mine.tmpShares[i]);
                        }
                    }
                    vm.mine.tmpShares = tmp;
                    if(vm.focus == 'left'){
                        vm.mine.sharesIng = tmp;
                    }else if(vm.focus == 'middle'){
                        vm.mine.sharesEnd = tmp;
                    }else if(vm.focus == 'right'){
                        vm.mine.sharesBalance = tmp;
                    }
                }
                vm.loading = false;
            }).error(function(){
                alert("截团失败,请联系管理员!");
                vm.loading = false;
            });
        }

        function stopShare(id){
            if(vm.loading)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/stopShare/"+id;
            $http.get(url).success(function(data){
                if(data.success)
                {
                    var tmp = [];
                    for(var i = 0 ; i < vm.mine.tmpShares.length ; i++)
                    {
                        if(parseInt(vm.mine.tmpShares[i].id) != id)
                        {
                            tmp.push(vm.mine.tmpShares[i]);
                        }
                    }
                    vm.mine.tmpShares = tmp;
                    if(vm.focus == 'left'){
                        vm.mine.sharesIng = tmp;
                    }else if(vm.focus == 'middle'){
                        vm.mine.sharesEnd = tmp;
                    }else if(vm.focus == 'right'){
                        vm.mine.sharesBalance = tmp;
                    }
                }
                vm.loading = false;
            }).error(function(){
                alert("截团失败,请联系管理员!");
                vm.loading = false;
            });
        }

        function myShareNextPage()
        {
            if(vm.loading)
            {
                return false;
            }
            vm.loading = true;
            if(vm.focus == 'left')
            {
                if(vm.mine.sharesIngOver){
                    vm.mine.tmpShares = vm.mine.sharesIng;
                    return false;
                }
                var url = "/weshares/my_shares_list_api/1/" + vm.mine.sharesIngPage;
                $http.get(url).success(function (data) {
                    if(data.length == 0)
                    {
                        vm.mine.sharesIngOver = true;
                        vm.mine.tmpSharesOver = true;
                    }else{
                        vm.mine.sharesIng = vm.mine.sharesIng.concat(data);
                        vm.mine.sharesIngPage += 1;
                        vm.mine.tmpSharesOver = false;
                        vm.mine.tmpShares = vm.mine.sharesIng;
                    }
                    vm.loading = false;
                }).error(function () {
                    vm.loading = false;
                });
            }else if(vm.focus == 'middle'){
                if(vm.mine.sharesEndOver){
                    vm.mine.tmpShares = vm.mine.sharesEnd;
                    return false;
                }
                var url = "/weshares/my_shares_list_api/2/" + vm.mine.sharesEndPage;
                $http.get(url).success(function (data) {
                    if(data.length == 0)
                    {
                        vm.mine.sharesEndOver = true;
                        vm.mine.tmpSharesOver = true;
                    }else{
                        vm.mine.sharesEnd = vm.mine.sharesEnd.concat(data);
                        vm.mine.sharesEndPage += 1;
                        vm.mine.tmpSharesOver = false;
                        vm.mine.tmpShares = vm.mine.sharesEnd;
                    }
                    vm.loading = false;
                }).error(function () {
                    vm.loading = false;
                });
            }else if(vm.focus == 'right')
            {
                if(vm.mine.sharesBalanceOver){
                    vm.mine.tmpShares = vm.mine.sharesBalance;
                    return false;
                }
                var url = "/weshares/my_shares_list_api/3/" + vm.mine.sharesBalancePage;
                $http.get(url).success(function (data) {
                    if(data.length == 0)
                    {
                        vm.mine.sharesBalanceOver = true;
                        vm.mine.tmpSharesOver = true;
                    }else{
                        vm.mine.sharesBalance = vm.mine.sharesBalance.concat(data);
                        vm.mine.sharesBalancePage += 1;
                        vm.mine.tmpSharesOver = false;
                        vm.mine.tmpShares = vm.mine.sharesBalance;
                    }
                    vm.loading = false;
                }).error(function () {
                    vm.loading = false;
                });
            }
        }

        function myOrderNextPage() {
            if(vm.loading || vm.mine.orderOver)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/my_order_list_api/" + vm.mine.orderPage;
            $http.get(url).success(function (data) {
                if(data.length == 0)
                {
                    vm.mine.orderOver = true;
                }else{
                    vm.mine.order = vm.mine.order.concat(data);
                    vm.mine.orderPage += 1;
                    console.log(vm.mine.order);
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
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
        function attendNextPage(){
            if(vm.loading || vm.attends.over)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_attends/" + vm.uid + '/' + vm.attends.page;
            $http.get(url).success(function (data) {
                if(data.length == 0)
                {
                    vm.attends.over = true;
                }else{
                    vm.attends.list = vm.attends.list.concat(data);
                    vm.attends.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
        function shareNextPage(){
            if(vm.loading || vm.shares.over)
            {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_shares/" + vm.uid + '/' + vm.shares.page;
            $http.get(url).success(function (data) {
                if(data.length == 0)
                {
                    vm.shares.over = true;
                }else{
                    vm.shares.list = vm.shares.list.concat(data);
                    vm.shares.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
        function sub(){
            vm.loading = true;
            $http.get('/weshares/subUser/' + vm.uid).success(function (data) {
                if (data['success']) {
                    vm.isSub = 1;
                } else {
                    if (data['reason'] == 'not_sub') {
                        alert('请先关注我们的服务号');
                        window.location.href = data['url'];
                    }
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
        function unSub() {
            vm.loading = true;
            $http.get('/weshares/unSubUser/' + vm.uid).success(function (data) {
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