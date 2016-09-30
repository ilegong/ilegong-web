(function (window, angular) {
    angular.module('weshares')
        .controller('GetUserInfoCtr', GetUserInfoCtr);

    function GetUserInfoCtr($rootScope, $http, $attrs, Utils) {
        var vm = this;
        $rootScope.loadingPage = false;
        vm.inDeleteShare = false;
        vm.showLayer = false;
        vm.inNormalShareView = true;
        vm.inSearchShare = false;
        vm.focus = 'share';
        vm.staticFilePath = Utils.staticFilePath();
        vm.uid = $attrs.uid;
        vm.loading = false;
        vm.shares = {
            list: [],
            page: 1,
            over: false
        };
        vm.attends = {
            list: [],
            page: 1,
            over: false
        };
        vm.isShowUnSubButton = false;
        vm.isSub = $attrs.sub;
        vm.mine = {
            sharesIng: [],
            sharesIngPage: 1,
            sharesIngOver: false,
            sharesEnd: [],
            sharesEndPage: 1,
            sharesEndOver: false,
            sharesBalance: [],
            sharesBalancePage: 1,
            sharesBalanceOver: false,
            order: [],
            orderPage: 1,
            orderOver: false,
            tmpShares: [],
            tmpSharesOver: false,
            searchShares : [],
            searchSharesOver : false,
            searchSharesPage : 1
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
        vm.editInfo = editInfo;
        vm.editPwd = editPwd;
        vm.canManageShareInfo = canManageShareInfo;
        vm.canManageShareOrder = canManageShareOrder;
        vm.doDeleteShare = doDeleteShare;
        vm.reOpenShare = reOpenShare;
        vm.doSearch = doSearch;
        vm.mySearchShareNextPage = mySearchShareNextPage;
        vm.historySearchText = '';
        vm.doBack = doBack;

        function doBack(){
            if(vm.inNormalShareView){
                window.location.href='/weshares/get_self_info.html';
            }else{
                vm.inNormalShareView = true;
                vm.inSearchShare = false;
            }
        }

        function doSearch() {
            if (Utils.isBlank(vm.searchText)) {
                alert('请输入查询内容!');
                return false;
            }
            if (Utils.isBlank(vm.historySearchText)) {
                vm.historySearchText = vm.searchText;
            } else {
                if (vm.historySearchText != vm.searchText) {
                    vm.mine.searchShares = [];
                    vm.mine.searchSharesOver = false;
                    vm.mine.searchSharesPage = 1;
                }
            }
            vm.inNormalShareView = false;
            vm.inSearchShare = true;
            vm.mySearchShareNextPage();
        }

        function canManageShareInfo(share) {
            if (vm.loadShareType == 0) {
                return true;
            }
            var authTypes = share['auth_types'];
            if (_.indexOf(authTypes, 'ShareOrder') >= 0) {
                return true;
            }
            return false;
        }

        function canManageShareOrder(share) {
            if (vm.loadShareType == 0) {
                return true;
            }
            var authTypes = share['auth_types'];
            if (_.indexOf(authTypes, 'ShareInfo') >= 0 || _.indexOf(authTypes, 'ShareManage') >= 0) {
                return true;
            }
            return false;
        }

        function editInfo() {
            if (vm.loading) {
                return false;
            }
            if (!vm.user_nickname) {
                alert("昵称不能为空");
            }
            if (!vm.user_desc) {
                alert("简介不能为空");
            }
            vm.loading = true;

            var url = "/users/update_user_intronew";
            $http.post(url,
                {'user_intro': vm.user_desc, 'user_id': vm.user_id, 'user_nickname': vm.user_nickname}
            ).success(function (data) {
                    if (data.success) {
                        vm.user_info_show = false;
                    }
                    vm.loading = false;
                }).error(function () {
                    alert("修改失败");
                    vm.loading = false;
                });

        }

        function editPwd() {
            if (vm.loading) {
                return false;
            }
            if (!vm.first_pwd || !vm.second_pwd) {
                alert("密码不能为空");
            }
            if (vm.first_pwd != vm.second_pwd) {
                alert("密码不一致");
            }
            vm.loading = true;

            var url = "/users/setpasswordnew.json";
            $http.post(url,
                {password: vm.first_pwd}
            ).success(function (data) {
                    if (data['success']) {
                        vm.first_pwd = '';
                        vm.second_pwd = '';
                        vm.user_pwd_show = false;
                    } else {
                        if (data['reason'] == 'not_login') {
                            alert('当前用户不存在');
                        }
                        if (data['reason'] == 'password_empty') {
                            alert('密码为空');
                        }
                        if (data['reason'] == 'server_error') {
                            alert('系统出错，请联系客服。');
                        }
                    }
                    vm.loading = false;
                }).error(function () {
                    alert("修改失败");
                    vm.loading = false;
                });

        }

        function delShare(share) {
            vm.prepareDeleteShare = share;
            vm.inDeleteShare = true;
            vm.showLayer = true;
            if (share['order_count'] > 0) {
                vm.deleteShareTipInfo = '该分享中有' + share['order_count'] + '个订单，确定删除该分享吗？';
            } else {
                vm.deleteShareTipInfo = '确定删除该分享吗？';
            }
        }

        function doDeleteShare() {
            var id = vm.prepareDeleteShare.id;
            if (vm.loading) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/delete_share/" + id;
            $http.get(url).success(function (data) {
                if (data.success) {
                    vm.mine.tmpShares = _.reject(vm.mine.tmpShares, function (item) {
                        return item.id == id;
                    });
                }
                vm.loading = false;
            }).error(function () {
                alert("删除失败,请联系客服!");
                vm.loading = false;
            });
            vm.inDeleteShare = false;
            vm.showLayer = false;
        }

        function reOpenShare(id) {
            alert("亲，服务器国庆放假维护升级中..");
            return false;
            if (vm.loading) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/cloneShare/" + id;
            $http.get(url).success(function (data) {
                if (data.success) {
                    vm.mine.sharesIng = [];
                    vm.mine.sharesIngPage = 1;
                    vm.mine.sharesIngOver = false;
                }
                alert('已经重新开团,点击进行中查看!');
                vm.loading = false;
            }).error(function () {
                alert("重新开团失败,请联系客服!");
                vm.loading = false;
            });
        }

        function stopShare(id) {
            if (vm.loading) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/stopShare/" + id;
            $http.get(url).success(function (data) {
                if (data.success) {
                    vm.mine.tmpShares = _.reject(vm.mine.tmpShares, function (item) {
                        return item.id == id;
                    });
                    vm.mine.sharesEnd = [];
                    vm.mine.sharesEndPage = 1;
                    vm.mine.sharesEndOver = false;
                }
                alert('已经截团,点击已结束查看!');
                vm.loading = false;
            }).error(function () {
                alert("截团失败,请联系管理员!");
                vm.loading = false;
            });
        }

        function getLoadSharesUrl() {
            var url = '/weshares/';
            if (vm.loadShareType == '0') {
                url = url + 'my_shares_list_api/';
            }
            if (vm.loadShareType == '1') {
                url = url + 'my_auth_shares_list_api/';
            }
            if (vm.focus == 'left') {
                url = url + '1/' + vm.mine.sharesIngPage;
            }
            if (vm.focus == 'middle') {
                url = url + '2/' + vm.mine.sharesEndPage;
            }
            if (vm.focus == 'right') {
                url = url + '3/' + vm.mine.sharesBalancePage;
            }
            return url;
        }

        function loadShareData(tab) {
            if (tab) {
                vm.focus = tab;
                vm.mine.tmpSharesOver = false;
                vm.loading = true;
                vm.handleShareDataMap[vm.focus]['resetData']();
            }
            var checkFunc = vm.handleShareDataMap[vm.focus]['checkFunc'];
            var callBack = vm.handleShareDataMap[vm.focus]['callBack'];
            if (checkFunc()) {
                var url = getLoadSharesUrl();
                $http.get(url).success(function (data) {
                    vm.loading = false;
                    callBack(data);
                }).error(function () {
                    vm.loading = false;
                });
            } else {
                vm.loading = false;
            }
        }

        function mySearchShareNextPage() {
            if (vm.loading) {
                return false;
            }
            var url = '/weshares/search_shares_api/' + vm.mine.searchSharesPage + '?keyword=' + vm.searchText;
            vm.loading = true;
            $http.get(url).success(function (data) {
                vm.loading = false;
                if (data.length == 0) {
                    vm.mine.searchSharesOver = true;
                } else {
                    vm.mine.searchShares = vm.mine.searchShares.concat(data);
                    vm.mine.searchSharesPage += 1;
                }
            }).error(function () {
                vm.loading = false;
            });
        }

        function myShareNextPage(tab) {
            if (vm.loading) {
                return false;
            }
            vm.loading = true;
            loadShareData(tab);
        }

        function myOrderNextPage() {
            if (vm.loading || vm.mine.orderOver) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/my_order_list_api/" + vm.mine.orderPage;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.mine.orderOver = true;
                } else {
                    vm.mine.order = vm.mine.order.concat(data);
                    vm.mine.orderPage += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }

        function goToShare(id) {
            window.location.href = '/weshares/view/' + id;
        }

        function goToComment() {
            window.location.href = '/weshares/u_comment/' + vm.uid;
        }

        function changeAvatar() {
            window.location.href = '/users/change_avatar?ref=/weshares/user_share_info';
        }

        function attendNextPage() {
            if (vm.loading || vm.attends.over) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_attends/" + vm.uid + '/' + vm.attends.page;
            $http.get(url).success(function (data) {
                if (data.length == 0) {
                    vm.attends.over = true;
                } else {
                    vm.attends.list = vm.attends.list.concat(data);
                    vm.attends.page += 1;
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
        }

        function shareNextPage() {
            if (vm.loading || vm.shares.over) {
                return false;
            }
            vm.loading = true;
            var url = "/weshares/get_other_shares/" + vm.uid + '/' + vm.shares.page;
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

        function sub() {
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
                } else {
                    alert('取消失败');
                }
                vm.loading = false;
            }).error(function () {
                vm.loading = false;
            });
            vm.isShowUnSubButton = false;
        }

        function showUnSubButton() {
            vm.isShowUnSubButton = !vm.isShowUnSubButton;
        }

        function viewUser(uid) {
            window.location.href = '/weshares/user_share_info/' + uid;
        }

        vm.handleShareDataMap = {
            'left': {
                'resetData': function () {
                    vm.mine.tmpShares = vm.mine.sharesIng;
                },
                'checkFunc': function () {
                    if (vm.mine.sharesIngOver) {
                        vm.mine.tmpShares = vm.mine.sharesIng;
                        return false;
                    }
                    return true;
                },
                'callBack': function (data) {
                    if (data.length == 0) {
                        vm.mine.sharesIngOver = true;
                        vm.mine.tmpSharesOver = true;
                    } else {
                        vm.mine.sharesIng = vm.mine.sharesIng.concat(data);
                        vm.mine.sharesIngPage += 1;
                        vm.mine.tmpSharesOver = false;
                    }
                    vm.mine.tmpShares = vm.mine.sharesIng;
                }
            },
            'middle': {
                'resetData': function () {
                    vm.mine.tmpShares = vm.mine.sharesEnd;
                },
                'checkFunc': function () {
                    if (vm.mine.sharesEndOver) {
                        vm.mine.tmpShares = vm.mine.sharesEnd;
                        return false;
                    }
                    return true;
                },
                'callBack': function (data) {
                    if (data.length == 0) {
                        vm.mine.sharesEndOver = true;
                        vm.mine.tmpSharesOver = true;
                    } else {
                        vm.mine.sharesEnd = vm.mine.sharesEnd.concat(data);
                        vm.mine.sharesEndPage += 1;
                        vm.mine.tmpSharesOver = false;
                    }
                    vm.mine.tmpShares = vm.mine.sharesEnd;
                }
            },
            'right': {
                'resetData': function () {
                    vm.mine.tmpShares = vm.mine.sharesBalance;
                },
                'checkFunc': function () {
                    if (vm.mine.sharesBalanceOver) {
                        vm.mine.tmpShares = vm.mine.sharesBalance;
                        return false;
                    }
                    return true;
                },
                'callBack': function (data) {
                    if (data.length == 0) {
                        vm.mine.sharesBalanceOver = true;
                        vm.mine.tmpSharesOver = true;
                    } else {
                        vm.mine.sharesBalance = vm.mine.sharesBalance.concat(data);
                        vm.mine.sharesBalancePage += 1;
                        vm.mine.tmpSharesOver = false;
                    }
                    vm.mine.tmpShares = vm.mine.sharesBalance;
                }
            }
        };
    }
})(window, window.angular);