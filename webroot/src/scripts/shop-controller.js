/**
 * Created by ellipsis on 16/9/7.
 */
(function (window, angular) {
    angular.module('weshares')
        .controller('ShopCtrl', ShopCtrl);

    function ShopCtrl($http, $log, $templateCache, $rootScope) {
        var vm = this;
        vm.type = 1;
        vm.loadingData = false;
        vm.top = [];
        vm.shares = [];
        vm.page = 1;
        vm.over = false;
        vm.show_desc = false;
        vm.showShareDialog = false;
        vm.showMaskBackGround = false;
        vm.showNotifyView = false;
        vm.setTop = setTop;
        vm.sub = sub;
        vm.unSub = unSub;
        vm.loadData = loadData;
        vm.go = go;
        vm.showShare = showShare;
        vm.hideAllLayer = hideAllLayer;
        vm.msg = msg;
        vm.notice = notice;
        vm.sendNotice = sendNotice;
        vm.notify = {title: ''};
        active();
        function active() {
            $rootScope.uid = angular.element(document.getElementById('ShopCtrl')).attr('data-user_id');
            vm.uid = angular.element(document.getElementById('ShopCtrl')).attr('data-user_id');
            vm.sub_status = angular.element(document.getElementById('ShopCtrl')).attr('data-sub_status');
            vm.loadData();
            $rootScope.loadingPage = false;
        }

        function loadData() {
            vm.loadingData = true;
            var url = "/weshares/shop_shares/" + vm.uid + "/" + vm.page;
            $http({method: 'GET', url: url, cache: $templateCache}).success(function (data) {
                if (data.length == 0) {
                    vm.over = false;
                } else {
                    if (vm.page == 1) {
                        vm.top[0] = data.shift();
                    }
                    vm.shares = vm.shares.concat(data);
                    vm.page = vm.page + 1;
                    vm.loadingData = false;
                }
            }).error(function (data, status) {
            });
        }

        function setTop(id) {
            vm.loadingData = true;
            var url = "/weshares/shop_set_top/" + id;
            $http({method: 'GET', url: url, cache: $templateCache}).success(function (data) {
                var tmp = [];
                tmp[0] = vm.top[0];
                for (var i = 0; i < vm.shares.length; i++) {
                    if (id != vm.shares[i].Weshare.id) {
                        tmp[tmp.length] = vm.shares[i];
                    } else {
                        vm.top[0] = vm.shares[i];
                    }
                }
                vm.shares = tmp;
            }).error(function (data, status) {
            });
        }

        function sub() {
            vm.loadingData = true;
            $http.get('/weshares/subUser/' + vm.uid).success(function (data) {
                if (data['success']) {
                    vm.sub_status = 1;
                } else {
                    if (data['reason'] == 'not_sub') {
                        alert('请先关注我们的服务号');
                        window.location.href = data['url'];
                    }
                }
                vm.loadingData = false;
            }).error(function () {
                vm.loadingData = false;
            });
        }

        function unSub() {
            vm.loadingData = true;
            $http.get('/weshares/unSubUser/' + vm.uid).success(function (data) {
                if (data['success']) {
                    vm.sub_status = 0;
                } else {
                    alert('取消失败');
                }
                vm.loadingData = false;
            }).error(function () {
                vm.loadingData = false;
            });
        }

        function go(id) {
            if (id > 0) {
                window.location.href = "/weshares/view/" + id + ".html?from=shop";
            }
        }

        function showShare() {
            vm.showShareDialog = true;
            vm.showMaskBackGround = true;
        }

        function hideAllLayer() {
            vm.showShareDialog = false;
            vm.showMaskBackGround = false;
            vm.showNotifyView = false;
        }

        function msg() {
            if (vm.uid > 0) {
                window.location.href = "/share_faq/faq/0/" + vm.uid;
            }
        }

        function notice() {
            vm.showMaskBackGround = true;
            vm.showNotifyView = true;
        }

        function sendNotice() {
            if (!vm.notify.title || !vm.notify.title.trim()) {
                alert('请输入动态！');
                return false;
            }
            if (vm.loadingData) {
                return false;
            }
            vm.loadingData = true;
            $http.post('/weshares/notice_from_shop', vm.notify).success(function (data) {
                $log.log(data);
                vm.loadingData = false;
                vm.hideAllLayer();
            }).error(function () {
                vm.loadingData = false;
            });
        }
    }
})(window, window.angular);