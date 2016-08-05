(function (window, angular) {
    angular.module('weshares')
        .controller('UserListCtrl', UserListCtrl);

    function UserListCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        vm.loadData = loadData;
        vm.toggleHideShowUnSubBtn = toggleHideShowUnSubBtn;
        vm.viewUser = viewUser;
        vm.search = search;
        vm.tab = tab;

        $rootScope.uid = 0;
        vm.type = 1;
        vm.loadingData = false;

        vm.self = {
            page:1,
            users:[],
            over:false,
            total:0
        };
        vm.comm = {
            page:1,
            users:[],
            over:false,
            total:0
        };
        vm.total = 0;
        vm.processSubmit = false;
        vm.queryWord = '';
        vm.searchWord = '';
        active();
        function active() {
            vm.type = angular.element(document.getElementById('userListView')).attr('data-type');
            vm.self.total = angular.element(document.getElementById('userListView')).attr('data-total_self');
            vm.comm.total = angular.element(document.getElementById('userListView')).attr('data-total_comm');
            $rootScope.uid = angular.element(document.getElementById('userListView')).attr('data-user_id');
            vm.total = vm.self.total;
            $rootScope.loadingPage = false;
            $http.get('/users/get_id_and_proxies').success(function (data) {
                if (data.uid != null) {
                    $rootScope.uid = data.uid;
                    $rootScope.proxies = _.map(data.proxies, function (pid) {
                        return parseInt(pid);
                    })
                }
                else {
                }
            }).error(function (data, e) {
                $log.log('Failed to get proxies: ' + e);
            });
        }

        function tab(type) {
            vm.type = type;
            if (vm.type == 1) {
                vm.total = vm.self.total;
            } else {
                vm.total = vm.comm.total;
                if(!vm.comm.over && vm.comm.page == 1)
                {
                    loadData();
                }
            }
        }

        function toggleHideShowUnSubBtn(uid) {
            if (!vm.flag_show_un_sub[uid]) {
                vm.flag_show_un_sub[uid] = true;
                return;
            }
            vm.flag_show_un_sub[uid] = !vm.flag_show_un_sub[uid];
        }

        function viewUser(uid) {
            window.location.href = '/weshares/user_share_info/' + uid;
        }



        function search() {
            vm.queryWord = vm.searchWord;
            vm.self.page = 1;
            vm.self.users = [];
            vm.self.over = false;
            vm.comm.page = 1;
            vm.comm.users = [];
            vm.comm.over = false;
            vm.loadingData = false;
            loadData();
        }

        function loadData() {
            vm.loadingData = true;
            var page = vm.type == 1 ? vm.self.page : vm.comm.page;
            var url = "/weshares/get_fans_data/" + vm.type + "/" + page + ".json?query=" + vm.queryWord;
            $http({method: 'GET', url: url, cache: $templateCache}).success(function (data) {

                if (vm.type == 1) {
                    if(data.length == 0)
                    {
                        vm.self.over = true;
                    }else{
                        vm.self.users = vm.self.users.concat(data);
                        vm.self.page = vm.self.page + 1;
                    }
                } else {
                    if(data.length == 0)
                    {
                        vm.comm.over = true;
                    }else{
                        vm.comm.users = vm.comm.users.concat(data);
                        vm.comm.page = vm.comm.page + 1;
                    }
                }
                vm.loadingData = false;
            }).error(function (data, status) {
            });
        }
    }
})(window, window.angular);