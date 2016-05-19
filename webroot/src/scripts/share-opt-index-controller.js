(function (window, angular) {
  angular.module('weshares')
    .controller('ShareOptIndexController', ShareOptIndexController);

  function ShareOptIndexController($rootScope, $http, $log, $q, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.loadData = loadData;
    vm.loadNextPage = loadNextPage;
    vm.onLoadedError = onLoadedError;
    vm.bottomTimeStamp = 0;
    vm.shares = [];
    activate();

    function activate() {
      vm.loadData().then(function (data) {
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
      var data = {
        "type": 0,
        "time": vm.bottomTimeStamp,
        "limit": 5
      };

      vm.loadData(data).success(function (data) {
        vm.loading = false;
      }).error(function (data) {
        vm.onLoadedError();
        vm.loading = false;
      });
    }

    function loadData(params) {
      var deferred = $q.defer();
      $http.get("/share_opt/newfetch_opt_list_data.json", params).success(function (data) {
        if (data.error) {
          deferred.resolve('Hello, ' + name + '!');
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
  }
})(window, window.angular);