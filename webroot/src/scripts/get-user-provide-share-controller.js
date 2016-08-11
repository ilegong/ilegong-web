(function (window, angular) {
    angular.module('weshares')
        .controller('GetUserProvideShareCtr', GetUserProvideShareCtr);

    function GetUserProvideShareCtr($rootScope, $http, $attrs, Utils) {
        var vm = this;
        $rootScope.loadingPage = false;
        vm.inSearchShare = false;
        vm.staticFilePath = Utils.staticFilePath();
        vm.uid = $attrs.uid;
        vm.loading = true;
        vm.doBack = doBack;
        vm.loadShareData = loadShareData;
        active();
        function active() {
            loadShareData();
        }
        function doBack(){
            if(vm.inNormalShareView){
                window.location.href='/weshares/get_self_info.html';
            }else{
                vm.inNormalShareView = true;
                vm.inSearchShare = false;
            }
        }


        function getLoadSharesUrl() {
            return '/weshares/my_provide_share_list_api.json';
        }

        function loadShareData() {
            var url = getLoadSharesUrl();
            $http.get(url).success(function (data) {
                vm.shares = data;
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }
    }
})(window, window.angular);