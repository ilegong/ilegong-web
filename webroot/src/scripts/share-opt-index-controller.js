(function (window, angular) {
  angular.module('weshares')
    .controller('ShareOptIndexController', ShareOptIndexController);

  function ShareOptIndexController($rootScope, $http, $log, $q, $window, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.loadData = loadData;
    vm.loadNextPage = loadNextPage;
    vm.onLoadOver = onLoadOver;
    vm.getShareImage = getShareImage;
    vm.scrollToTop = scrollToTop;
    activate();

    function activate() {
      vm.shares = [];
      $http.get('/users/get_id_and_proxies').success(function (data) {
        if (data.uid != null) {
          $rootScope.uid = data.uid;
          $rootScope.proxies = _.map(data.proxies, function (pid) {
            return {id: parseInt(pid)};
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
      if (vm.loading || vm.loadOver) {
        return false;
      }

      vm.loading = true;
      vm.loadData().then(function (shares) {
        vm.shares = vm.shares.concat(shares);
        vm.loading = false;
        $rootScope.loadingPage = false;
      }, function (detail) {
        vm.onLoadOver(detail);
        vm.loading = false;
        $rootScope.loadingPage = false;
      });
    }

    function loadData() {
      var deferred = $q.defer();
      var time = _.min(_.map(vm.shares, function (s) {
        return s.NewOptLog.time;
      }));
      $log.log('try to load data before ' + time);
      $http.get("/share_opt/fetch_opt_list_data.json?limit=10&type=0&time=" + time).success(function (data) {
        if (data.error) {
          deferred.reject(data.error);
        }
        var existingShareIds = _.map(vm.shares, function (s) {
          return s.Weshare.id
        });
        var shares = _.map(_.reject(data['opt_logs'], function (s) {
          return _.isEmpty(s) || _.isEmpty(s.Weshare) || _.contains(existingShareIds, s.Weshare.id);
        }), function (s) {
          s.NewOptLog.time = new Date(s.NewOptLog.time).getTime();
          return s;
        });
        if (_.isEmpty(shares)) {
          deferred.reject('no more data');
        }

        $log.log(shares);
        var shareIds = _.map(shares, function (s) {
          return s.Weshare.id;
        });
        $http.get('/weshares/summaries', {params: {shareIds: JSON.stringify(shareIds)}}).success(function (summaries) {
          _.each(summaries, function (summary) {
            var share = _.find(shares, function (s) {
              return s.Weshare.id == summary.share_id
            });
            share.summary = summary;
          });
        }).error(function (data, e) {
          $log.log('Failed to get summaries: ' + e);
        });
        deferred.resolve(shares);
      }).error(function (data, e) {
        deferred.reject('Failed to load data');
      });
      return deferred.promise;
    }

    function onLoadOver(detail) {
      vm.loadOver = true;
      $log.log('load over, detail: ' + detail);
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

    function scrollToTop() {
      $window.scrollTo(0, 0);
    }
  }
})(window, window.angular);