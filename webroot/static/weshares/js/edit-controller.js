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
    vm.canSetTagUser = [633345, 544307, 802852, 867587, 804975];
    vm.showEditShareView = true;
    vm.showEditTagView = false;
    function pageLoaded() {
      $rootScope.loadingPage = false;
    }

    activate();
    function activate() {
      vm.showShippmentInfo = false;
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
          vm.self_ziti_data = data['ship_type']['self_ziti'];
          vm.kuai_di_data = data['ship_type']['kuai_di'];
          vm.pys_ziti_data = data['ship_type']['pys_ziti'];
          vm.pin_tuan_data = data['ship_type']['pin_tuan'];
          vm.kuaidi_show_ship_fee = vm.kuai_di_data.ship_fee / 100;
          vm.proxy_rebate_percent = data['proxy_rebate_percent'] || vm.proxy_rebate_percent;
        }).error(function (data) {
        });
      } else {
        //保存的时候 记住数据
        $scope.$watchCollection('vm.weshare', vm.saveCacheData);
        vm.self_ziti_data = {status: 1, ship_fee: 0, tag: 'self_ziti'};
        vm.kuai_di_data = {status: -1, ship_fee: '', tag: 'kuai_di'};
        vm.pys_ziti_data = {status: -1, ship_fee: 0, tag: 'pys_ziti'};
        vm.pin_tuan_data = {status: -1, ship_fee: 500, tag: 'pin_tuan'};
        vm.proxy_rebate_percent = {status: 0, percent: 0};
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
          if(!$cacheData['id'])
          vm.weshare = $cacheData;
        }else{
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

    function toggleProduct(product, isLast) {
      if (isLast) {
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
      vm.isInProcess = true;
      vm.kuai_di_data.ship_fee = vm.kuai_di_data.ship_fee;
      vm.weshare.ship_type = [vm.self_ziti_data, vm.kuai_di_data, vm.pys_ziti_data, vm.pin_tuan_data];
      vm.weshare.proxy_rebate_percent = vm.proxy_rebate_percent;
      $http.post('/weshares/save', vm.weshare).success(function (data, status, headers, config) {
        if (data.success) {
          PYS.storage.clear();
          window.location.href = '/weshares/view/' + data['id'];
        } else {
          var uid = data['uid'];
          window.location.href = '/weshares/user_share_info/' + uid;
        }
      }).error(function (data, status, headers, config) {
        window.location.href = '/weshares/add';
      });
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
      vm.weshareTitleHasError = _.isEmpty(vm.weshare.title) || vm.weshare.title.length > 50;
      return vm.weshareTitleHasError;
    }

    function validateSendInfo() {
      vm.weshareSendInfoHasError = _.isEmpty(vm.weshare.send_info) || vm.weshare.send_info.length > 50;
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
      product.nameHasError = _.isEmpty(product.name) || product.name.length > 20;
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