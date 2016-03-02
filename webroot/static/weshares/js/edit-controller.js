(function (window, angular, wx) {

  angular.module('weshares')
    .constant('wx', wx)
    .controller('WesharesEditCtrl', WesharesEditCtrl);


  function WesharesEditCtrl($scope, $rootScope, $log, $http, wx, Utils, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.chooseAndUploadImage = chooseAndUploadImage;
    vm.uploadImage = uploadImage;
    vm.deleteImage = deleteImage;
    vm.toggleProduct = toggleProduct;
    vm.toggleAddress = toggleAddress;
    vm.nextStep = nextStep;
    vm.backStep = backStep;
    vm.saveWeshare = saveWeshare;
    vm.validateTitle = validateTitle;
    vm.validateProductName = validateProductName;
    vm.validateProductPrice = validateProductPrice;
    vm.validateAddress = validateAddress;
    vm.saveCacheData = saveCacheData;
    vm.validateShipFee = validateShipFee;
    vm.validateRebatePercent = validateRebatePercent;
    vm.validateTagName = validateTagName;
    vm.toggleTag = toggleTag;
    vm.saveTag = saveTag;
    vm.dataCacheKey = 'cache_share_data';
    vm.pageLoaded = pageLoaded;
    vm.hideEditTagView = hideEditTagView;
    vm.editTagView = editTagView;
    vm.checkUserCanSetTag = checkUserCanSetTag;
    vm.validateSendInfo = validateSendInfo;
    vm.validatePinTuan = validatePinTuan;
    vm.showChooseCityView = showChooseCityView;
    vm.hideChooseCityView = hideChooseCityView;
    vm.initCityData = initCityData;
    vm.toggleAreaProvinceCheckStatus = toggleAreaProvinceCheckStatus;
    vm.setDefaultShipSettingData = setDefaultShipSettingData;
    vm.setDefaultProxyRebatePercent = setDefaultProxyRebatePercent;
    vm.setDefaultDeliveryTemplate = setDefaultDeliveryTemplate;
    vm.setDeliveryTemplates = setDeliveryTemplates;
    vm.removeDeliveryTemplate = removeDeliveryTemplate;
    vm.addDeliveryTemplate = addDeliveryTemplate;
    vm.deliveryTemplateChooseCity = deliveryTemplateChooseCity;
    vm.resetProvinceAreaCheckStatus = resetProvinceAreaCheckStatus;
    vm.showDeliveryTemplateProvinceNames = showDeliveryTemplateProvinceNames;
    vm.setAreaCheckStatus = setAreaCheckStatus;
    vm.canSetTagUser = [633345, 544307, 802852, 867587, 804975];
    vm.showEditShareView = true;
    vm.showEditTagView = false;
    vm.currentDeliveryTemplate = null;
    function pageLoaded() {
      $rootScope.loadingPage = false;
    }
    function setDefaultShipSettingData(){
      vm.self_ziti_data = {status: 1, ship_fee: 0, tag: 'self_ziti'};
      vm.kuai_di_data = {status: -1, ship_fee: '', tag: 'kuai_di'};
      vm.pys_ziti_data = {status: -1, ship_fee: 0, tag: 'pys_ziti'};
      vm.pin_tuan_data = {status: -1, ship_fee: 500, tag: 'pin_tuan'};
    }
    function setDefaultProxyRebatePercent(){
      vm.proxy_rebate_percent = {status: 0, percent: 0};
    }
    function setDefaultDeliveryTemplate(){
      vm.defaultDeliveryTemplate = {
        "start_units": 1,
        "start_fee": 0,
        "add_units": 1,
        "add_fee": 0,
        "is_default": 1
      };
    }
    function setDeliveryTemplates(){
      vm.deliveryTemplates = [];
    }
    function removeDeliveryTemplate(deliveryTemplate){
      vm.deliveryTemplates = _.without(vm.deliveryTemplates, deliveryTemplate);
    }
    function addDeliveryTemplate(){
      vm.deliveryTemplates.push({
        "start_units": 1,
        "start_fee": 0,
        "add_units": 1,
        "add_fee": 0,
        "is_default": 0
      });
    }
    activate();
    function activate() {
      vm.initCityData();
      vm.resetProvinceAreaCheckStatus();
      vm.showEditShareInfo = true;
      vm.showShippmentInfo = false;
      vm.setDefaultDeliveryTemplate();
      vm.setDeliveryTemplates();
      var weshareId = angular.element(document.getElementById('weshareEditView')).attr('data-id');
      var sharerShipType = angular.element(document.getElementById('weshareEditView')).attr('data-ship-type');
      var userId = angular.element(document.getElementById('weshareEditView')).attr('data-user-id');
      var canUseOfflineAddress = angular.element(document.getElementById('weshareEditView')).attr('data-can-user-offline-address');
      vm.currentUserId = userId;
      vm.sharerShipType = sharerShipType;
      vm.canUseOfflineAddress = canUseOfflineAddress;
      if (!vm.canUseOfflineAddress) {
        vm.showCreateShareTipInfo = true;
        vm.showLayer = true;
      }
      if (weshareId) {
        //update
        $http.get('/weshares/get_share_info/' + weshareId).success(function (data) {
          vm.weshare = data;
          vm.weshare.tags = vm.weshare['tags_list'];
          setDefaultData();
          setDefaultShipSettingData();
          setDefaultProxyRebatePercent();
          if (data['ship_type']['self_ziti']) {
            vm.self_ziti_data = data['ship_type']['self_ziti'];
          }
          if (data['ship_type']['kuai_di']) {
            vm.kuai_di_data = data['ship_type']['kuai_di'];
          }
          if (data['ship_type']['pys_ziti']) {
            vm.pys_ziti_data = data['ship_type']['pys_ziti'];
          }
          if (data['ship_type']['pin_tuan']) {
            vm.pin_tuan_data = data['ship_type']['pin_tuan'];
          }
          vm.kuaidi_show_ship_fee = vm.kuai_di_data.ship_fee / 100;
          if (data['proxy_rebate_percent']) {
            vm.proxy_rebate_percent = data['proxy_rebate_percent'];
          }
          if (!_.isEmpty(data['deliveryTemplate']['delivery_templates'])) {
            vm.deliveryTemplates = data['deliveryTemplate']['delivery_templates'];
          }
          if (!_.isEmpty(data['deliveryTemplate']['default_delivery_template'])) {
            vm.defaultDeliveryTemplate = data['deliveryTemplate']['default_delivery_template'];
          }
        }).error(function (data) {
        });
      } else {
        //保存的时候 记住数据
        $scope.$watchCollection('vm.weshare', vm.saveCacheData);
        setDefaultShipSettingData();
        setDefaultProxyRebatePercent();
        vm.kuaidi_show_ship_fee = '';
        //add
        vm.weshare = {
          title: '',
          description: '',
          images: [],
          products: [
            {name: '', store: '', tbd: 0, tag_id: '0', deleted: 0}
          ],
          send_info: '',
          addresses: [
            {address: '', deleted: 0}
          ],
          tags: []
        };
        //reset cache data
        var $cacheData = PYS.storage.load(vm.dataCacheKey);
        if ($cacheData) {
          if (!$cacheData['id'])
            vm.weshare = $cacheData;
        } else {
          PYS.storage.save(vm.dataCacheKey, {}, 1);
        }
        setDefaultData();
        //load tags
        $http.get('/weshares/get_tags.json').success(function (data) {
          vm.weshare.tags = data.tags;
        }).error(function (data) {
        });
      }
      vm.messages = [];
      function setDefaultData() {
        if (!vm.weshare.addresses || vm.weshare.addresses.length == 0) {
          vm.weshare.addresses = [{address: '', deleted: 0}];
        }
        if (!vm.weshare.send_info) {
          vm.weshare.send_info = '';
        }
        if (!vm.weshare.tags) {
          vm.weshare.tags = [{name: '', deleted: 0}];
        }
      }
    }

    function chooseAndUploadImage() {
      wx.chooseImage({
        success: function (res) {
          //_.each(res.localIds, vm.uploadImage);
          //alert(res.localIds);
          //for(var local_id in res.localIds){
          //  vm.uploadImage(local_id);
          //}
          vm.uploadImage(res.localIds);
        },
        fail: function (res) {
          vm.messages.push({name: 'choose image failed', detail: res});
        }
      });
    }

    function saveCacheData() {
      PYS.storage.save(vm.dataCacheKey, vm.weshare, 1);
    }

    function uploadImage(localIds) {
      var i = 0, len = localIds.length;

      function upload() {
        wx.uploadImage({
          localId: localIds[i],
          isShowProgressTips: 1,
          success: function (res) {
            i++;
            $http.get('/downloads/download_wx_img?media_id=' + res.serverId).success(function (data, status, headers, config) {
              vm.messages.push({name: 'download image success', detail: data});
              var imageUrl = data['download_url'];
              if (!imageUrl || imageUrl == 'false') {
                return;
              }
              vm.weshare.images.push(imageUrl);
            }).error(function (data, status, headers, config) {
              vm.messages.push({name: 'download image failed', detail: data});
            });
            if (i < len) {
              upload();
            }
          },
          fail: function (res) {
            vm.messages.push({name: 'upload image failed', detail: res});
          }
        });
      }

      upload();
    }

    function deleteImage(image) {
      vm.weshare.images = _.without(vm.weshare.images, image);
    }

    function toggleProduct(product, add) {
      if (add) {
        vm.weshare.products.push({name: '', store: '', deleted: 0});
      }
      else {
        if (product.id && product.id > 0) {
          product.deleted = 1;
        } else {
          vm.weshare.products = _.without(vm.weshare.products, product);
        }
      }
    }

    function toggleAddress(address, isLast) {
      if (isLast) {
        vm.weshare.addresses.push({address: '', deleted: 0});
      } else {
        if (address.id && address.id > 0) {
          address.deleted = 1;
        } else {
          vm.weshare.addresses = _.without(vm.weshare.addresses, address);
        }
      }
    }

    function toggleTag(tag, isLast) {
      if (isLast) {
        vm.weshare.tags.push({name: '', deleted: 0});
      } else {
        if (tag.id && tag.id > 0) {
          tag.deleted = 1;
        } else {
          vm.weshare.tags = _.without(vm.weshare.tags, tag);
        }
      }
    }

    function backStep() {
      vm.showShippmentInfo = false;
      vm.showEditShareInfo = true;
    }

    function nextStep() {
      var titleHasError = vm.validateTitle();
      var productHasError = false;
      _.each(vm.weshare.products, function (product) {
        var nameHasError = vm.validateProductName(product);
        var priceHasError = vm.validateProductPrice(product);
        productHasError = productHasError || nameHasError || priceHasError;
      });
      if (titleHasError || productHasError) {
        return;
      }
      vm.showShippmentInfo = true;
      vm.showEditShareInfo = false;
    }

    function saveWeshare() {
      vm.weshare.addresses = _.filter(vm.weshare.addresses, function (address) {
        return !_.isEmpty(address.address);
      });
      if (vm.validateAddress()) {
        if (_.isEmpty(vm.weshare.addresses)) {
          vm.weshare.addresses = [
            {address: '', deleted: 0}
          ];
        }
        return false;
      }
      vm.kuai_di_data.ship_fee = vm.kuai_di_data.ship_fee || 0;
      if (vm.validateShipFee(vm.kuai_di_data.ship_fee)) {
        return false;
      }
      if (vm.validateRebatePercent()) {
        return false;
      }
      if (vm.validateSendInfo()) {
        return false;
      }
      if (vm.validatePinTuan()) {
        return false;
      }
      if (vm.isInProcess) {
        alert('正在保存....');
        return;
      }
      var deliveryTemplates = vm.deliveryTemplates.concat(vm.defaultDeliveryTemplate);
      vm.isInProcess = true;
      vm.kuai_di_data.ship_fee = vm.kuai_di_data.ship_fee;
      vm.weshare.ship_type = [vm.self_ziti_data, vm.kuai_di_data, vm.pys_ziti_data, vm.pin_tuan_data];
      vm.weshare.proxy_rebate_percent = vm.proxy_rebate_percent;
      vm.weshare['delivery_templates'] = deliveryTemplates;
      $http.post('/weshares/save', vm.weshare).success(function (data, status, headers, config) {
        if (data.success) {
          PYS.storage.clear();
          window.location.href = '/weshares/view/' + data['id'];
        } else {
          window.location.href = '/weshares/user_share_info/';
        }
      }).error(function (data, status, headers, config) {
        window.location.href = '/weshares/add';
      });
    }
    function toggleAreaProvinceCheckStatus(areaId){
      var areaCheckStatus = vm.areaCheckStatus[areaId];
      _.each(vm.provinceData[areaId], function(_, key){
        vm.provinceCheckStatus[key] = areaCheckStatus;
      });
    }

    function hideChooseCityView() {
      vm.isShowChooseCity = false;
      vm.showShippmentInfo = true;
    }

    function hideEditTagView() {
      vm.weshare.tags = _.filter(vm.weshare.tags, function (tag) {
        return tag.id && tag.id > 0;
      });
      vm.showEditTagView = false;
      vm.showEditShareView = true;
    }

    function editTagView() {
      vm.showEditShareView = false;
      vm.showEditTagView = true;
      if (!vm.weshare.tags || vm.weshare.tags.length == 0) {
        vm.weshare.tags = [{name: '', deleted: 0}];
      }
    }

    function inArray(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    }

    function checkUserCanSetTag() {
      if (inArray(vm.currentUserId, vm.canSetTagUser)) {
        return true;
      }
      return false;
    }

    function saveTag() {
      if (vm.isSaveingTag) {
        alert('正在保存....');
        return;
      }
      var tagHasError = false;
      _.each(vm.weshare.tags, function (tag) {
        var tagNameHasError = vm.validateTagName(tag);
        tagHasError = tagHasError || tagNameHasError;
      });
      if (tagHasError) {
        return false;
      }
      vm.isSaveingTag = true;
      $http.post('/weshares/save_tags', vm.weshare.tags).success(function (data) {
        vm.isSaveingTag = false;
        vm.showEditTagView = false;
        vm.showEditShareView = true;
        vm.weshare.tags = data.tags;
        $log.log(data);
      }).error(function (data) {
        vm.isSaveingTag = false;
        $log.log(data);
      });
    }

    function validateShipFee() {
      if (Utils.isNumber(vm.kuaidi_show_ship_fee)) {
        vm.kuai_di_data.ship_fee = vm.kuaidi_show_ship_fee * 100;
      }
      if (!Utils.isNumber(vm.kuai_di_data.ship_fee) && vm.kuai_di_data.status == 1) {
        vm.shipFeeHasError = true;
      } else {
        vm.shipFeeHasError = false;
      }
      return vm.shipFeeHasError;
    }

    function validateTitle() {
      vm.weshareTitleHasError = _.isEmpty(vm.weshare.title) || vm.weshare.title.length > 128;
      return vm.weshareTitleHasError;
    }

    function validateSendInfo() {
      vm.weshareSendInfoHasError = _.isEmpty(vm.weshare.send_info) || vm.weshare.send_info.length > 68;
      return vm.weshareSendInfoHasError;
    }

    function validatePinTuan() {
      vm.pinTuanHasError = false;
      if (vm.pin_tuan_data.status == 1 && vm.pin_tuan_data.limit <= 0) {
        vm.pinTuanHasError = true;
      }
      return vm.pinTuanHasError;
    }

    function validateProductName(product) {
      product.nameHasError = _.isEmpty(product.name) || product.name.length > 40;
      return product.nameHasError;
    }

    function validateTagName(tag) {
      tag.nameHasError = _.isEmpty(tag.name) || tag.name.length > 20;
      return tag.nameHasError;
    }

    function validateProductPrice(product) {
      product.priceHasError = !product.price || !Utils.isNumber(product.price);
      return product.priceHasError;
    }

    function validateAddress() {
      if (vm.self_ziti_data.status == -1) {
        vm.addressError = false;
        return vm.addressError;
      }
      vm.addressError = vm.self_ziti_data.status == 1 && _.isEmpty(vm.weshare.addresses);
      if (!vm.addressError) {
        var tempAddressError = false;
        _.each(vm.weshare.addresses, function (address) {
          tempAddressError = tempAddressError || _.isEmpty(address.address);
        });
        vm.addressError = vm.addressError || tempAddressError;
      }
      return vm.addressError;
    }

    function showDeliveryTemplateProvinceNames(deliveryTemplate){
      if (!_.isEmpty(deliveryTemplate['regions'])) {
        var nameStr = _.reduce(deliveryTemplate['regions'], function (memo, checkedProvince) {
          return checkedProvince['province_name'] + ',' + memo;
        }, '');
        return nameStr;
      }
      return '选择地区';
    }

    function deliveryTemplateChooseCity(){
      vm.hideChooseCityView();
      vm.currentDeliveryTemplate['regions'] = [];
      var checkedProvinces = _.map(vm.provinceCheckStatus, function(checked, provinceId){ if(checked){return provinceId} });
      checkedProvinces = _.filter(checkedProvinces, function(provinceId){return provinceId;});
      var checkedProvinceData = _.map(checkedProvinces, function(provinceId){
        return {"province_id":provinceId, "province_name": vm.provinceIdNameMap[provinceId]};
      });
      vm.currentDeliveryTemplate['regions'] = checkedProvinceData;
    }

    function resetProvinceAreaCheckStatus(){
      vm.areaCheckStatus = {
        "1": false,
        "2": false,
        "3": false,
        "4": false,
        "5": false,
        "6": false,
        "7": false,
        "8": false
      };
      vm.provinceCheckStatus = {
        "310100": false,
        "320000": false,
        "330000": false,
        "340000": false,
        "360000": false,
        "110100": false,
        "120100": false,
        "130000": false,
        "140000": false,
        "150000": false,
        "370000": false,
        "410000": false,
        "420000": false,
        "430000": false,
        "350000": false,
        "440000": false,
        "450000": false,
        "460000": false,
        "210000": false,
        "220000": false,
        "230000": false,
        "610000": false,
        "620000": false,
        "630000": false,
        "640000": false,
        "650000": false,
        "500100": false,
        "510000": false,
        "520000": false,
        "530000": false,
        "540000": false,
        "710000": false,
        "810000": false,
        "820000": false
      };
    }

    function initCityData() {
      vm.areaData = [{"name": "华东", "id": "1", "showChild": false}, {
        "name": "华北",
        "id": "2",
        "showChild": false
      }, {"name": "华中", "id": "3", "showChild": false}, {"name": "华南", "id": "4", "showChild": false}, {
        "name": "东北",
        "id": "5",
        "showChild": false
      }, {"name": "西北", "id": "6", "showChild": false}, {"name": "西南", "id": "7", "showChild": false}, {
        "name": "港澳台",
        "id": "8",
        "showChild": false
      }];
      vm.provinceIdNameMap = {
        "310100": "上海",
        "320000": "江苏",
        "330000": "浙江",
        "340000": "安徽",
        "360000": "江西",
        "110100": "北京",
        "120100": "天津",
        "130000": "河北",
        "140000": "山西",
        "150000": "内蒙古",
        "370000": "山东",
        "410000": "河南",
        "420000": "湖北",
        "430000": "湖南",
        "350000": "福建",
        "440000": "广东",
        "450000": "广西",
        "460000": "海南",
        "210000": "辽宁",
        "220000": "吉林",
        "230000": "黑龙江",
        "610000": "陕西",
        "620000": "甘肃",
        "630000": "青海",
        "640000": "宁夏",
        "650000": "新疆",
        "500100": "重庆",
        "510000": "四川",
        "520000": "贵州",
        "530000": "云南",
        "540000": "西藏",
        "710000": "台湾",
        "810000": "香港",
        "820000": "澳门"
      };
      vm.areaIds = ["1", "2", "3", "4", "5", "6", "7", "8"];
      vm.provinceData = {
        "1": {
          "310100": "上海",
          "320000": "江苏",
          "330000": "浙江",
          "340000": "安徽",
          "360000": "江西"
        },
        "2": {
          "110100": "北京",
          "120100": "天津",
          "130000": "河北",
          "140000": "山西",
          "150000": "内蒙古",
          "370000": "山东"
        },
        "3": {
          "410000": "河南",
          "420000": "湖北",
          "430000": "湖南"
        },
        "4": {
          "350000": "福建",
          "440000": "广东",
          "450000": "广西",
          "460000": "海南"
        },
        "5": {
          "210000": "辽宁",
          "220000": "吉林",
          "230000": "黑龙江"
        },
        "6": {
          "610000": "陕西",
          "620000": "甘肃",
          "630000": "青海",
          "640000": "宁夏",
          "650000": "新疆"
        },
        "7": {
          "500100": "重庆",
          "510000": "四川",
          "520000": "贵州",
          "530000": "云南",
          "540000": "西藏"
        },
        "8": {
          "710000": "台湾",
          "810000": "香港",
          "820000": "澳门"
        }
      };
    }

    function setAreaCheckStatus(areaId){
      var areaChildProvinces = vm.provinceData[areaId];
      var provinceIds = _.keys(areaChildProvinces);
      var checkStatusResult = _.reduce(provinceIds, function (memo, provinceId) {
        return memo && vm.provinceCheckStatus[provinceId];
      }, true);
      vm.areaCheckStatus[areaId] = checkStatusResult;
    }

    function showChooseCityView(deliveryTemplate) {
      vm.resetProvinceAreaCheckStatus();
      vm.isShowChooseCity = true;
      vm.showShippmentInfo = false;
      vm.currentDeliveryTemplate = deliveryTemplate;
      var regions = deliveryTemplate['regions'];
      _.each(regions, function (item) {
        vm.provinceCheckStatus[item['province_id']] = true;
      });
      _.each(vm.areaIds, function (areaId) {
        vm.setAreaCheckStatus(areaId);
      });
    }


    function validateRebatePercent() {
      if (!Utils.isNumber(vm.proxy_rebate_percent.percent)) {
        vm.rebatePercentHasError = true;
      } else {
        vm.rebatePercentHasError = false;
      }
      return vm.rebatePercentHasError;
    }
  }

})(window, window.angular, window.wx);