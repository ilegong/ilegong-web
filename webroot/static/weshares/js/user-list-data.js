(function (window, angular) {
  angular.module('UserInfo')
    .controller('UserListCtrl', UserListCtrl);

  function UserListCtrl($http, $templateCache) {
    var vm = this;
    vm.loadData = loadData;
    vm.getUserLevelText = getUserLevelText;
    vm.hasSub = hasSub;
    vm.noMoreData = false;
    vm.loadingData = false;
    vm.page = 1;
    vm.users = [];
    vm.level_map =  {};
    vm.sub_user_ids = [];
    vm.levelTextMap = ['分享达人',
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

    function getUserLevelText(uid){
      var level = vm.level_map[uid];
      return 'V'+level+vm.levelTextMap[level];
    }

    function hasSub(uid){
      return _.indexOf(vm.sub_user_ids, uid) >= 0;
    }

    function loadData() {
      vm.loadingData = true;
      var url = "/weshares/get_u_list_data/"+vm.dataType+"/"+ vm.userId + "/" + vm.page + ".json";
      $http({method: 'GET', url: url, cache: $templateCache}).
        success(function (data, status) {
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