(function (window, angular) {
  angular.module('UserInfo')
    .controller('UserListCtrl', UserListCtrl);

  function UserListCtrl($http, $templateCache) {
    var vm = this;
    vm.loadData = loadData;
    vm.getUserLevelText = getUserLevelText;
    vm.hasSub = hasSub;
    vm.toggleHideShowUnSubBtn = toggleHideShowUnSubBtn;
    vm.unSub = unSub;
    vm.subUser = subUser;
    vm.noMoreData = false;
    vm.loadingData = false;
    vm.page = 1;
    vm.users = [];
    vm.level_map = {};
    vm.sub_user_ids = [];
    vm.flag_show_un_sub = {};
    vm.processSubmit = false;
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
    }

    function toggleHideShowUnSubBtn(uid) {
      if (!vm.flag_show_un_sub[uid]) {
        vm.flag_show_un_sub[uid] = true;
        return;
      }
      vm.flag_show_un_sub[uid] = !vm.flag_show_un_sub[uid];
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
            window.location.href = "https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=403992659&idx=1&sn=714a1a5f0bb4940f895e60f2f3995544";
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

    function getUserLevelText(uid) {
      var level = vm.level_map[uid];
      return 'V' + level + vm.levelTextMap[level];
    }

    function hasSub(uid) {
      return _.indexOf(vm.sub_user_ids, uid) >= 0;
    }

    function loadData() {
      vm.loadingData = true;
      var url = "/weshares/get_u_list_data/" + vm.dataType + "/" + vm.userId + "/" + vm.page + ".json";
      $http({method: 'GET', url: url, cache: $templateCache}).
        success(function (data) {
          vm.loadingData = false;
          if (data['page_info']) {
            vm.pageInfo = data['page_info'];
          }
          vm.users = vm.users.concat(data['users']);
          vm.sub_user_ids = vm.sub_user_ids.concat(data['sub_user_ids']);
          vm.level_map = merge_options(vm.level_map, data['level_map']);
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