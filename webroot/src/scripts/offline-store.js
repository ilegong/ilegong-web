/*线下自提点*/
(function (window, angular) {
  angular.module('module.services')
    .service('OfflineStore', OfflineStore);

  function OfflineStore(){
    return {
      'ChooseOfflineStore' : ChooseOfflineStore
    };

    function ChooseOfflineStore($vm, $http,$templateCache) {
      $vm.areas = {
        110101: {
          'name': "东城区"
        },
        110108: {
          'name': "海淀区"
        },
        110102: {
          'name': "西城区"
        },
        110105: {
          'name': "朝阳区"
        },
        110106: {
          'name': "丰台区"
        },
        110114: {
          'name': "昌平区"
        },
        110113: {
          'name': "顺义区"
        },
        110115: {
          'name': "大兴区"
        },
        110112: {
          'name': "通州区"
        }
      };
      $vm.currentAreaCode = '110101';
      changeOfflineStoreArea($vm.currentAreaCode);
      //$http({
      //  method: 'GET',
      //  url: '/tuan_buyings/get_offline_address.json?type=-1',
      //  cache: $templateCache
      //}).success(function (data) {
      //  $vm.offlineStores = data['address'];
      //});
      $vm.offlineStores = {};
      $vm.changeOfflineStoreArea = changeOfflineStoreArea;
      $vm.showOfflineStoreDetail = showOfflineStoreDetail;
      $vm.chooseOfflineStore = chooseOfflineStore;
      $vm.showChooseOfflineStore = showChooseOfflineStore;
      //$vm.mapPanTo = mapPanTo;
      function showChooseOfflineStore() {
        $vm.showOfflineStoreDetailView = false;
        $vm.chooseOfflineStoreView = true;
        $vm.showShareDetailView = false;
        $vm.showBalanceView = false;
      }

      function chooseOfflineStore(offlineStore) {
        $vm.showOfflineStoreDetailView = false;
        $vm.chooseOfflineStoreView = false;
        $vm.showShareDetailView = false;
        $vm.showBalanceView = true;
        $vm.checkedOfflineStore = offlineStore;
      }

      //function mapPanTo(offlineStore) {
      //  var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
      //  $vm.offlineStoreMap.panTo(point);
      //}

      //function showMap(offlineStore) {
      //  var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
      //  if ($vm.offlineStoreMap == null) {
      //    $vm.offlineStoreMap = new BMap.Map("offline-store-map");
      //    $vm.offlineStoreMap.centerAndZoom(point, 15);
      //  } else {
      //    $timeout(function () {
      //      $vm.mapPanTo(offlineStore);
      //    }, 200);
      //  }
      //  $vm.offlineStoreMap.clearOverlays();
      //  var marker = new BMap.Marker(point);        // 创建标注
      //  $vm.offlineStoreMap.addOverlay(marker);
      //}

      function showOfflineStoreDetail(offlineStore) {
        if (!offlineStore['mapImg']) {
          var pointStr = offlineStore['location_long'] + ',' + offlineStore['location_lat'];
          var reversePointStr = offlineStore['location_lat'] + ',' + offlineStore['location_long'];
          var pointName = offlineStore['alias'];
          offlineStore['mapImg'] = 'http://api.map.baidu.com/staticimage?width=400&height=200&center=' + pointStr + '&markers=' + pointName + '|' + pointStr + '&zoom=18&markerStyles=l,A,0xff0000';
          offlineStore['mapDetailUrl'] = 'http://api.map.baidu.com/marker?location=' + reversePointStr + '&title=' + pointName + '&content=' + pointName + '&output=html';
          //http://api.map.baidu.com/marker?location=39.916979519873,116.41004950566&title=我的位置&content=百度奎科大厦&output=html
        }
        $vm.currentOfflineStore = offlineStore;
        $vm.showOfflineStoreDetailView = true;
        $vm.chooseOfflineStoreView = false;
        $vm.showShareDetailView = false;
        $vm.showBalanceView = false;
        //showMap($vm.currentOfflineStore);
      }

      function changeOfflineStoreArea(code) {
        $vm.currentAreaCode = code;
        $http({
          method: 'GET',
          url: '/commonApi/get_offline_store/' + code + '.json',
          cache: $templateCache
        }).success(function (data) {
          $vm.offlineStores[code] = data;
        }).error(function () {
        });
      }
    }
  }
})(window, window.angular);