(function (window, angular, wx) {

  angular.module('weshares')
    .constant('wx', wx)
    .controller('WesharesEditCtrl', WesharesEditCtrl);


  function WesharesEditCtrl($scope, $rootScope, $log, $http, $timeout, wx, Utils, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.chooseAndUploadImage = chooseAndUploadImage;
    vm.uploadImage = uploadImage;
    vm.deleteImage = deleteImage;
    vm.toggleProduct = toggleProduct;
    vm.addAddress = addAddress;
    vm.nextStep = nextStep;
    vm.backStep = backStep;
    vm.saveWeshare = saveWeshare;
    vm.validateTitle = validateTitle;
    vm.validateTitleAndAlert = validateTitleAndAlert;
    vm.validateProductName = validateProductName;
    vm.validateProductNameAndAlert = validateProductNameAndAlert;
    vm.validateProductPrice = validateProductPrice;
    vm.validateProductPriceAndAlert = validateProductPriceAndAlert;
    vm.validateProductWeight = validateProductWeight;
    vm.validateProductWeightAndAlert = validateProductWeightAndAlert;
    vm.validateAddress = validateAddress;
    vm.saveCacheData = saveCacheData;
    vm.validateShipFee = validateShipFee;
    vm.validateRebatePercent = validateRebatePercent;
    vm.dataCacheKey = 'cache_share_data';
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
    vm.validateDeliveryTemplateData = validateDeliveryTemplateData;
    vm.toggleBoxZitiChecked = toggleBoxZitiChecked;
    vm.toggleBoxKuidiChecked = toggleBoxKuidiChecked;
    vm.deleteAddress = deleteAddress;
    vm.getUnitTypeText = getUnitTypeText;
    vm.currentDeliveryTemplate = null;
    vm.setDefaultImage = setDefaultImage;
    vm.onError = onError;
    vm.getAvailableProducts = getAvailableProducts;
    vm.getAvailableAddresses = getAvailableAddresses;
    vm.hideOfflineStore = false;

    function setDefaultShipSettingData() {
      vm.self_ziti_data = {status: 1, ship_fee: 0, tag: 'self_ziti'};
      vm.kuai_di_data = {status: -1, ship_fee: '', tag: 'kuai_di'};
      vm.pys_ziti_data = {status: -1, ship_fee: 0, tag: 'pys_ziti'};
      vm.pin_tuan_data = {status: -1, ship_fee: 500, tag: 'pin_tuan'};
    }

    function setDefaultProxyRebatePercent() {
      vm.proxy_rebate_percent = {status: 0, percent: 0};
    }

    function setDefaultDeliveryTemplate() {
      vm.defaultDeliveryTemplate = {
        "start_units": 1,
        "start_fee": 0,
        "add_units": 1,
        "add_fee": 0,
        "is_default": 1
      };
    }

    function setDeliveryTemplates() {
      vm.deliveryTemplates = [];
    }

    function removeDeliveryTemplate(deliveryTemplate) {
      vm.deliveryTemplates = _.without(vm.deliveryTemplates, deliveryTemplate);
    }

    function addDeliveryTemplate() {
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
      vm.deliveryTemplateType = 0;
      vm.setDefaultDeliveryTemplate();
      vm.setDeliveryTemplates();
      var weshareEditView = document.getElementById('weshareEditView');
      var weshareId = angular.element(weshareEditView).attr('data-id');
      var sharerShipType = angular.element(weshareEditView).attr('data-ship-type');
      var userId = angular.element(weshareEditView).attr('data-user-id');
      vm.currentUserId = userId;
      vm.sharerShipType = sharerShipType;
      if (window.location.host == 'sh.tongshijia.com') {
        vm.hideOfflineStore = true;
      }
      if (weshareId) {
        //update
        $http.get('/weshares/get_share_info/' + weshareId).success(function (data) {
          vm.weshare = data;
          vm.weshare.description = vm.weshare.description.replace(new RegExp('<br />', 'g'), '\r\n');
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
            vm.defaultDeliveryTemplate['is_default'] = 1;
            vm.deliveryTemplateType = vm.defaultDeliveryTemplate['unit_type'];
          }
          $rootScope.loadingPage = false;
        }).error(function (data) {
          $rootScope.loadingPage = false;
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
            {name: '', store: '', tbd: 0, tag_id: '0', deleted: 0, weight: ''}
          ],
          send_info: '',
          addresses: [
            {address: '', deleted: 0, name: '', phone: ''}
          ]
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
        $rootScope.loadingPage = false;
      }
      vm.messages = [];
      function setDefaultData() {
        if (!vm.weshare.addresses || vm.weshare.addresses.length == 0) {
          vm.weshare.addresses = [{address: '', deleted: 0, name: '', phone: ''}];
        }
        if (!vm.weshare.send_info) {
          vm.weshare.send_info = '';
        }
      }

      setWxParams();
    }

    function getUnitTypeText() {
      if (vm.deliveryTemplateType == 0) {
        return '件';
      }
      if (vm.deliveryTemplateType == 1) {
        return 'kg';
      }
    }

    function chooseAndUploadImage() {
      wx.chooseImage({
        success: function (res) {
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
            }).error(function (data) {
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
        vm.weshare.products.push({name: '', store: '', deleted: 0, weight: ''});
      }
      else {
        if (product.id && product.id > 0) {
          product.deleted = 1;
        } else {
          vm.weshare.products = _.without(vm.weshare.products, product);
        }
      }
    }

    function deleteAddress(address) {
      if (address.id && address.id > 0) {
        address.deleted = 1;
      } else {
        vm.weshare.addresses = _.without(vm.weshare.addresses, address);
      }
    }

    function addAddress() {
      vm.weshare.addresses.push({address: '', deleted: 0, name: '', phone: ''});
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
        vm.onError('输入有误，请重新输入');
        return;
      }
      vm.showShippmentInfo = true;
      vm.showEditShareInfo = false;
    }

    function saveWeshare() {
      vm.weshare.addresses = _.filter(vm.weshare.addresses, function (address) {
        return !_.isEmpty(address.address);
      });
      vm.kuai_di_data.ship_fee = vm.kuai_di_data.ship_fee || 0;
      vm.weshare.ship_type = [vm.self_ziti_data, vm.kuai_di_data, vm.pys_ziti_data, vm.pin_tuan_data];
      if (!validateShipSetting(vm.weshare.ship_type)) {
        alert('至少选择一种物流方式');
        resetZitiAddressData();
        return false;
      }
      if (vm.validateAddress()) {
        resetZitiAddressData();
        alert('请输入完整自提地址信息');
        return false;
      }
      var deliveryTemplates = vm.deliveryTemplates.concat(vm.defaultDeliveryTemplate);
      if (vm.validateShipFee(vm.kuai_di_data.ship_fee)) {
        return false;
      }
      if (vm.validateRebatePercent()) {
        alert('请设置团长佣金比例');
        return false;
      }
      if (vm.validateSendInfo()) {
        alert('输入到货/发货时间');
        return false;
      }
      if (vm.validatePinTuan()) {
        return false;
      }
      if (!vm.validateDeliveryTemplateData(deliveryTemplates)) {
        return false;
      }
      deliveryTemplates = _.map(deliveryTemplates, function (item) {
        item['unit_type'] = vm.deliveryTemplateType;
        return item;
      });
      if (vm.isInProcess) {
        alert('正在保存....');
        return;
      }
      vm.isInProcess = true;
      vm.weshare.proxy_rebate_percent = vm.proxy_rebate_percent;
      vm.weshare['delivery_templates'] = deliveryTemplates;
      $http.post('/weshares/save', vm.weshare).success(function (data) {
        if (data.success) {
          PYS.storage.clear();
          window.location.href = '/weshares/view/' + data['id'];
        } else {
          window.location.href = '/weshares/user_share_info/';
        }
      }).error(function () {
        window.location.href = '/weshares/add';
      });
    }

    function toggleAreaProvinceCheckStatus(areaId) {
      var areaCheckStatus = vm.areaCheckStatus[areaId];
      _.each(vm.provinceData[areaId], function (_, key) {
        vm.provinceCheckStatus[key] = areaCheckStatus;
      });
    }

    function hideChooseCityView() {
      vm.isShowChooseCity = false;
      vm.showShippmentInfo = true;
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

    function validateTitleAndAlert() {
      if (vm.validateTitle() && vm.weshare.title.length > 128) {
        vm.onError('标题太长，请重新输入');
      }
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

    function validateProductNameAndAlert(product) {
      if (vm.validateProductName(product) && product.name.length > 40) {
        vm.onError('名称太长，请重新输入');
      }
    }

    function validateProductWeight(product) {
      product.weightHasWarning = Utils.isNumber(product.weight) && product.weight > 20;
      return product.weightHasWarning;
    }

    function validateProductWeightAndAlert(product) {
      if (vm.validateProductWeight(product)) {
        alert('重量单位是公斤，您确定吗？');
      }
    }

    function validateProductPrice(product) {
      product.priceHasError = !product.price || !Utils.isNumber(product.price) || product.price < 0.01;
      return product.priceHasError;
    }

    function validateProductPriceAndAlert(product) {
      if (vm.validateProductPrice(product) && product.price && product.price < 0.01) {
        vm.onError('价格有误，请重新输入');
      }
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
          tempAddressError = tempAddressError || _.isEmpty(address.address) || _.isEmpty(address.name) || _.isEmpty(address.phone);
        });
        vm.addressError = vm.addressError || tempAddressError;
      }
      return vm.addressError;
    }

    function showDeliveryTemplateProvinceNames(deliveryTemplate) {
      if (!_.isEmpty(deliveryTemplate['regions'])) {
        var nameStr = _.reduce(deliveryTemplate['regions'], function (memo, checkedProvince) {
          return checkedProvince['province_name'] + ',' + memo;
        }, '');
        return nameStr;
      }
      return '选择地区';
    }

    function deliveryTemplateChooseCity() {
      vm.hideChooseCityView();
      vm.currentDeliveryTemplate['regions'] = [];
      var checkedProvinces = _.map(vm.provinceCheckStatus, function (checked, provinceId) {
        if (checked) {
          return provinceId
        }
      });
      checkedProvinces = _.filter(checkedProvinces, function (provinceId) {
        return provinceId;
      });
      var checkedProvinceData = _.map(checkedProvinces, function (provinceId) {
        return {"province_id": provinceId, "province_name": vm.provinceIdNameMap[provinceId]};
      });
      vm.currentDeliveryTemplate['regions'] = checkedProvinceData;
    }

    function resetProvinceAreaCheckStatus() {
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

    function setAreaCheckStatus(areaId) {
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

    function validateDeliveryTemplateData(deliveryTemplates) {
      for (var i = 0; i < deliveryTemplates.length; i++) {
        var deliveryTemplateItem = deliveryTemplates[i];
        if (Utils.isBlank(deliveryTemplateItem['start_units'])) {
          deliveryTemplateItem['start_units'] = 1;
        }
        if (Utils.isBlank(deliveryTemplateItem['start_fee'])) {
          deliveryTemplateItem['start_fee'] = 0;
        }
        if (Utils.isBlank(deliveryTemplateItem['add_units'])) {
          deliveryTemplateItem['add_units'] = 1;
        }
        if (Utils.isBlank(deliveryTemplateItem['add_fee'])) {
          deliveryTemplateItem['add_fee'] = 0;
        }
        if (deliveryTemplateItem['is_default'] == 0) {
          if (_.isEmpty(deliveryTemplateItem['regions'])) {
            alert('非默认运费设置，需要指定地区');
            return false;
          }
        }
        if (!Utils.isNumber(deliveryTemplateItem['start_units']) || !Utils.isNumber(deliveryTemplateItem['start_fee']) || !Utils.isNumber(deliveryTemplateItem['add_units']) || !Utils.isNumber(deliveryTemplateItem['add_fee'])) {
          alert('运费设置需要输入数字');
          return false;
        }
        if (deliveryTemplateItem['start_units'] < 1 || deliveryTemplateItem['add_units'] < 1) {
          alert('运费设置商品件数必须大于1');
          return false;
        }
      }
      return true;
    }

    function validateRebatePercent() {
      if (!Utils.isNumber(vm.proxy_rebate_percent.percent)) {
        vm.rebatePercentHasError = true;
      } else {
        vm.rebatePercentHasError = false;
      }
      return vm.rebatePercentHasError;
    }

    function validateShipSetting($settings) {
      var hasOne = _.find($settings, function (item) {
        return item.status == 1;
      });
      if (hasOne) {
        return true;
      }
      return false;
    }

    function resetZitiAddressData() {
      if (_.isEmpty(vm.weshare.addresses)) {
        vm.weshare.addresses = [
          {address: '', deleted: 0, name: '', phone: ''}
        ];
      }
    }

    function toggleBoxZitiChecked() {
      if (vm.self_ziti_data.status == 1) {
        vm.self_ziti_data.status = -1;
      } else {
        vm.self_ziti_data.status = 1;
      }
    }

    function toggleBoxKuidiChecked() {
      if (vm.kuai_di_data.status == 1) {
        vm.kuai_di_data.status = -1;
      } else {
        vm.kuai_di_data.status = 1;
      }
    }

    function setWxParams() {
      if (wx) {
        wx.ready(function () {
          var to_timeline_title = '朋友说—基于信任关系的分享平台';
          var to_friend_title = '朋友说—基于信任关系的分享平台';
          var to_friend_link = document.URL.split('?')[0];
          var to_timeline_link = document.URL.split('?')[0];
          var imgUrl = 'http://static.tongshijia.com/static/weshares/images/pys-logo.gif';
          var desc = '来 [朋友说] 分享好吃的、好玩的、有趣的';
          wx.onMenuShareAppMessage({
            title: to_friend_title,
            desc: desc,
            link: to_friend_link,
            imgUrl: imgUrl,
            success: function () {
              // 用户确认分享后执行的回调函数
            }
          });
          wx.onMenuShareTimeline({
            title: to_timeline_title,
            link: to_timeline_link,
            imgUrl: imgUrl,
            success: function () {
            }
          });
        });
      }
    }

    function setDefaultImage(image) {
      if (_.isEmpty(image)) {
        return;
      }
      vm.weshare.images = _.without(vm.weshare.images, image);
      vm.weshare.images.unshift(image);
    }

    function onError(message) {
      $rootScope.showErrorMessageLayer = true;
      $rootScope.errorMessage = message;
      $timeout(function () {
        $rootScope.showErrorMessageLayer = false;
      }, 2000);
    }

    function getAvailableProducts() {
      if(_.isEmpty(vm.weshare) || _.isEmpty(vm.weshare.products)){
        return [];
      }
      return _.filter(vm.weshare.products, function (p) {
        return p.deleted == 0;
      });
    }

    function getAvailableAddresses() {
      if(_.isEmpty(vm.weshare) || _.isEmpty(vm.weshare.addresses)){
        return [];
      }
      return _.filter(vm.weshare.addresses, function (a) {
        return a.deleted == 0;
      });
    }
  }

})(window, window.angular, window.wx);
