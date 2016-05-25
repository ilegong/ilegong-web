(function (window, angular) {
  angular.module('weshares')
    .controller('UserListCtrl', UserListCtrl);

  function UserListCtrl($http, $log, $templateCache, $rootScope) {
    var vm = this;
    vm.loadData = loadData;
    vm.hasSub = hasSub;
    vm.toggleHideShowUnSubBtn = toggleHideShowUnSubBtn;
    vm.unSub = unSub;
    vm.subUser = subUser;
    vm.viewUser = viewUser;
    vm.search = search;
    vm.noMoreData = false;
    vm.loadingData = false;
    vm.page = 1;
    vm.users = [];
    vm.sub_user_ids = [];
    vm.flag_show_un_sub = {};
    vm.processSubmit = false;
    vm.queryWord = '';
    vm.searchWord = '';
    vm.levelTextMap = [
      '分享达人',
      '实习团长',
      '正式团长',
      '优秀团长',
      '高级团长',
      '资深团长',
      '首席团长'];
    active();
    function active() {
      vm.userId = angular.element(document.getElementById('userListView')).attr('data-uid');
      vm.me = angular.element(document.getElementById('userListView')).attr('data-me');
      vm.dataType = angular.element(document.getElementById('userListView')).attr('data-type');
      $rootScope.loadingPage = false;
      $http.get('/users/get_id_and_proxies').success(function (data) {
        if (data.uid != null) {
          $rootScope.uid = data.uid;
          $rootScope.proxies = _.map(data.proxies, function (pid) {
            return parseInt(pid);
          })
        }
        else {
          $log.log('User not logged in');
        }
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
      });
    }

    function toggleHideShowUnSubBtn(uid) {
      if (!vm.flag_show_un_sub[uid]) {
        vm.flag_show_un_sub[uid] = true;
        return;
      }
      vm.flag_show_un_sub[uid] = !vm.flag_show_un_sub[uid];
    }

    function viewUser(uid){
      window.location.href = '/weshares/user_share_info/'+uid;
    }

    function unSub(uid) {
      vm.processSubmit = true;
      $http({method: 'GET', url: '/weshares/unsubscribe_sharer/' + uid + '/' + vm.userId}).success(function (data) {
        if (data['success']) {
          afterUnSub(uid);
        }
        vm.processSubmit = false;
      }).error(function () {
        vm.processSubmit = false;
      });
    }

    function subUser(uid) {
      vm.processSubmit = true;
      $http({method: 'GET', 'url': '/weshares/subscribe_sharer/' + uid + '/' + vm.userId}).success(function (data) {
        if (data['success']) {
          vm.sub_user_ids.push(uid);
        } else {
          if (data['reason'] == 'not_sub') {
            alert('请先关注我们的服务号');
            window.location.href = data['url'];
          }
        }
        vm.processSubmit = false;
      }).error(function () {
        vm.processSubmit = false;
      });
    }

    function afterUnSub(uid) {
      vm.sub_user_ids = _.without(vm.sub_user_ids, uid);
      delete vm.flag_show_un_sub[uid];
      if (vm.dataType == 1) {
        //remove data
        vm.users = _.filter(vm.users, function (item) {
          return item['User']['id'] != uid;
        });
      }
    }

    function hasSub(uid) {
      return _.indexOf(vm.sub_user_ids, uid) >= 0;
    }

    function search(){
      vm.queryWord = vm.searchWord;
      vm.users = [];
      vm.sub_user_ids = [];
      vm.page = 1;
      vm.pageInfo = {};
      vm.noMoreData = false;
      vm.loadingData = false;
      loadData();
    }

    function loadData() {
      vm.loadingData = true;
      var url = "/weshares/get_u_list_data/" + vm.dataType + "/" + vm.userId + "/" + vm.page + ".json?query="+vm.queryWord;
      $http({method: 'GET', url: url, cache: $templateCache}).
        success(function (data) {
          vm.loadingData = false;
          if (data['page_info']) {
            vm.pageInfo = data['page_info'];
          }
          var users = _.map(data['users'], function(u){
            var uid = u.User.id;
            if(typeof(data['level_map'][uid])=='undefined'){
              u.User.level = -1;
            }else{
              u.User.level = data['level_map'][uid];
            }
            return u;
          });
          $log.log(data['users']);

          vm.users = vm.users.concat(data['users']);
          vm.sub_user_ids = vm.sub_user_ids.concat(data['sub_user_ids']);
          vm.page = vm.page + 1;
          if (vm.pageInfo['page_count'] < vm.page) {
            vm.noMoreData = true;
          }
        }).error(function (data, status) {
        });
    }

    function merge_options(obj1, obj2) {
      var obj3 = {};
      for (var attrname in obj1) {
        obj3[attrname] = obj1[attrname];
      }
      for (var attrname in obj2) {
        obj3[attrname] = obj2[attrname];
      }
      return obj3;
    }
  }
})(window, window.angular);