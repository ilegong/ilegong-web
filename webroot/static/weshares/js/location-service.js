/*加载省市县数据*/
(function (window, angular) {
  angular.module('module.services')
    .service('LocationService', LocationService);
  function LocationService($http) {
    var me = this;
    me.provinceData = [{"id":"110100","name":"\u5317\u4eac","parent_id":"2"},{"id":"120100","name":"\u5929\u6d25","parent_id":"2"},{"id":"130000","name":"\u6cb3\u5317","parent_id":"2"},{"id":"140000","name":"\u5c71\u897f","parent_id":"2"},{"id":"150000","name":"\u5185\u8499\u53e4","parent_id":"2"},{"id":"210000","name":"\u8fbd\u5b81","parent_id":"5"},{"id":"220000","name":"\u5409\u6797","parent_id":"5"},{"id":"230000","name":"\u9ed1\u9f99\u6c5f","parent_id":"5"},{"id":"310100","name":"\u4e0a\u6d77","parent_id":"1"},{"id":"320000","name":"\u6c5f\u82cf","parent_id":"1"},{"id":"330000","name":"\u6d59\u6c5f","parent_id":"1"},{"id":"340000","name":"\u5b89\u5fbd","parent_id":"1"},{"id":"350000","name":"\u798f\u5efa","parent_id":"4"},{"id":"360000","name":"\u6c5f\u897f","parent_id":"1"},{"id":"370000","name":"\u5c71\u4e1c","parent_id":"2"},{"id":"410000","name":"\u6cb3\u5357","parent_id":"3"},{"id":"420000","name":"\u6e56\u5317","parent_id":"3"},{"id":"430000","name":"\u6e56\u5357","parent_id":"3"},{"id":"440000","name":"\u5e7f\u4e1c","parent_id":"4"},{"id":"450000","name":"\u5e7f\u897f","parent_id":"4"},{"id":"460000","name":"\u6d77\u5357","parent_id":"4"},{"id":"500100","name":"\u91cd\u5e86","parent_id":"7"},{"id":"510000","name":"\u56db\u5ddd","parent_id":"7"},{"id":"520000","name":"\u8d35\u5dde","parent_id":"7"},{"id":"530000","name":"\u4e91\u5357","parent_id":"7"},{"id":"540000","name":"\u897f\u85cf","parent_id":"7"},{"id":"610000","name":"\u9655\u897f","parent_id":"6"},{"id":"620000","name":"\u7518\u8083","parent_id":"6"},{"id":"630000","name":"\u9752\u6d77","parent_id":"6"},{"id":"640000","name":"\u5b81\u590f","parent_id":"6"},{"id":"650000","name":"\u65b0\u7586","parent_id":"6"},{"id":"710000","name":"\u53f0\u6e7e","parent_id":"8"},{"id":"810000","name":"\u9999\u6e2f","parent_id":"8"},{"id":"820000","name":"\u6fb3\u95e8","parent_id":"8"}];
    function loadCityData(provinceId) {
      //vm.calOrderTotalPrice();
      $http({method: 'GET', url: '/locations/get_city.json?provinceId=' + provinceId, cache: $templateCache}).
        success(function (data) {
          me.cityData = data;
        }).
        error(function (data, status) {
        });
    }
    function loadCountyData(cityId) {
      $http({method: 'GET', url: '/locations/get_county.json?cityId=' + cityId, cache: $templateCache}).
        success(function (data, status) {
          me.countyData = data;
        }).
        error(function (data, status) {
        });
    }

    return {
      'provinceData': this.provinceData,
      'cityData': this.cityData,
      'countyData': this.countyData,
      'loadCityData': loadCityData,
      'loadCountyData': loadCountyData

    };
  }
})(window, window.angular);
