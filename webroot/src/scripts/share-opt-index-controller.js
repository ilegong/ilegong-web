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

      $http.get('/users/get_id_and_proxies').success(function (data) {
        if (data.uid != null) {
          vm.uid = data.uid;
          vm.proxies = _.map(data.proxies, function (pid) {
            return {id: parseInt(pid), showUnSubscribeBtn: false};
          })
        }
        else {
          $log.log('User not logged in');
        }
      }).error(function (data, e) {
        $log.log('Failed to get proxies: ' + e);
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
      $http.get("/share_opt/fetch_opt_list_data.json?limit=5&type=0&time=" + time).success(function (data) {
        if (data.error) {
          deferred.reject(data.error);
        }

        var shares = _.filter(data['opt_logs'], function (s) {
          return !_.isEmpty(s) && !_.isEmpty(s.Weshare);
        });
        $log.log(shares);
        _.each(shares, function (optLog) {
          vm.bottomTimeStamp = optLog.time;
          $log.log(optLog.time);
        });

        var shareIds = _.map(shares, function (s) {
          return s.Weshare.id;
        });
        $http.get('/weshares/summaries', {params: {shareIds: JSON.stringify(shareIds)}}).success(function (summaries) {
          $log.log(summaries);
          _.each(summaries, function(summary){
            var share = _.find(shares, function(s){return s.Weshare.id == summary.share_id});
            share.summary = summary;
          });
        }).error(function (data, e) {
          $log.log('Failed to get index products: ' + e);
        });

        vm.shares = vm.shares.concat(shares);
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
      if (_.isEmpty(share) || _.isEmpty(share.Weshare.images)) {
        return vm.staticFilePath + '/static/img/default_product_banner.png';
      }
      return share.Weshare.images[0];
    }
  }
})(window, window.angular);