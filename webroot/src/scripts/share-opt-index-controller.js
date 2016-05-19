(function (window, angular) {
  angular.module('weshares')
    .controller('ShareOptIndexController', ShareOptIndexController);

  function ShareOptIndexController($rootScope, $http, $log, $q, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.loadData = loadData;
    vm.loadNextPage = loadNextPage;
    vm.onLoadedError = onLoadedError;
    vm.getShareImage = getShareImage;
    vm.bottomTimeStamp = 0;
    vm.shares = [];
    activate();

    function activate() {
      vm.loadData(vm.bottomTimeStamp).then(function (data) {
        $rootScope.loadingPage = false;
      }, function (data) {
        $rootScope.loadingPage = false;
      });
    }

    function loadNextPage() {
      if (vm.loading || vm.shares.over) {
        return false;
      }

      vm.loading = true;
      vm.loadData(vm.bottomTimeStamp).success(function (data) {
        vm.loading = false;
      }).error(function (data) {
        vm.onLoadedError();
        vm.loading = false;
      });
    }

    function loadData(time) {
      var deferred = $q.defer();
      $http.get("/share_opt/fetch_opt_list_data.json?limit=5&type=0&time="+time).success(function (data) {
        if (data.error) {
          deferred.reject(data.error);
        }

        var list = data['opt_logs'];
        _.each(list, function (optLog) {
          vm.bottomTimeStamp = optLog.time;
        });
        $log.log(list);
        vm.shares = vm.shares.concat(list);

        deferred.resolve(vm.shares);
      }).error(function () {
        deferred.reject('Greeting ' + name + ' is not allowed.');
      });
      return deferred.promise;
    }

    function onLoadedError() {
      //$loadingDiv.find('div').text('数据加载中...');
      //if (data.error) {
      //  loadDataFlag = 0;
      //  $loadingDiv.find('div').text('没有获取到(更多)有效数据!!!');
      //  return;
      //}
    }

    function getShareImage(share) {
      if (_.isEmpty(share) || _.isEmpty(share.images)) {
        return vm.staticFilePath + '/static/img/default_product_banner.png';
      }
      return vm.staticFilePath + share.images[0];
    }
  }
})(window, window.angular);