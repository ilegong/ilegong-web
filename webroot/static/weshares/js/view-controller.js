(function (window, angular) {

  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl)

  function ChooseOfflineStore($vm, $log, $http, $templateCache){
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
    $http({method: 'GET', url: '/tuan_buyings/get_offline_address.json?type=-1', cache: $templateCache}).success(function(data) {
      $vm.offlineStores = data['address'];
    });
    $vm.changeOfflineStoreArea = changeOfflineStoreArea;
    $vm.showOfflineStoreDetail = showOfflineStoreDetail;
    $vm.chooseOfflineStore = chooseOfflineStore;
    $vm.showChooseOfflineStore  = showChooseOfflineStore;
    $vm.showShareDetail = showShareDetail;

    function showShareDetail(){
      $vm.showOfflineStoreDetailView = false;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = true;
    }

    function showChooseOfflineStore(){
      $vm.showOfflineStoreDetailView = false;
      $vm.chooseOfflineStoreView = true;
      $vm.showShareDetailView = false;
    }

    function chooseOfflineStore(offlineStore){
      $vm.showOfflineStoreDetailView = false;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = true;
      $vm.checkedOfflineStore=offlineStore;
    }

    function showOfflineStoreDetail(offlineStore){
      $vm.currentOfflineStore = offlineStore;
      $vm.showOfflineStoreDetailView = true;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = false;
    }
    function changeOfflineStoreArea(code){
      $vm.currentAreaCode = code;
    }
  }

  function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, Utils) {
    var vm = this;
    vm.showShareDetailView = true;
    ChooseOfflineStore(vm, $log, $http, $templateCache);
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.viewImage = viewImage;
    vm.increaseProductNum = increaseProductNum;
    vm.decreaseProductNum = decreaseProductNum;
    vm.getOrderDisplayName = getOrderDisplayName;
    vm.isCreator = isCreator;
    vm.isOwner = isOwner;
    vm.isOrderReceived = isOrderReceived;
    vm.getConsigneeInfo = getConsigneeInfo;
    vm.validateAddress = validateAddress;
    vm.validateProducts = validateProducts;
    vm.buyProducts = buyProducts;
    vm.validateMobile = validateMobile;
    vm.validateUserName = validateUserName;
    vm.validateUserAddress = validateUserAddress;
    vm.submitOrder = submitOrder;
    vm.confirmReceived = confirmReceived;
    vm.toUserShareInfo = toUserShareInfo;
    vm.checkProductNum = checkProductNum;
    vm.getProductLeftNum = getProductLeftNum;
    vm.toShareOrderList = toShareOrderList;
    vm.createMyShare = createMyShare;
    vm.toUpdate = toUpdate;
    vm.stopShare = stopShare;

    activate();
    function activate() {
      var weshareId = angular.element(document.getElementById('weshareView')).attr('data-weshare-id');
      var fromType = angular.element(document.getElementById('weshareView')).attr('data-from-type');
      //first share
      var initSharedOfferId = angular.element(document.getElementById('weshareView')).attr('data-shared-offer');
      //var followSharedOfferId = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-offer');
      var followSharedType = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-type');
      var followSharedNum = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-coupon-num');
      vm.sharedOfferId = initSharedOfferId;
      if (initSharedOfferId) {
        vm.isSharePacket = true;
      }
      vm.weshare = {};
      vm.orderTotalPrice = 0;
      $scope.$watch('vm.selectShipType', function (val) {
        if(val!=-1){
          vm.chooseShipType = false;
        }
      });
      $scope.$watchCollection('vm.checkedOfflineStore',function(val){
        if(val){
          vm.chooseOfflineStoreError = false;
        }
      });
      $http({method: 'GET', url: '/weshares/detail/' + weshareId, cache: $templateCache}).
        success(function (data, status) {
          vm.weshare = data['weshare'];
          if (vm.weshare.addresses.length == 1) {
            vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
          }
          else if (vm.weshare.addresses.length > 1) {
            vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
            vm.weshare.selectedAddressId = -1;
          }
          vm.ordersDetail = data['ordersDetail'];
          vm.currentUser = data['current_user'] || {};
          vm.weixinInfo = data['weixininfo'];
          vm.consignee = data['consignee'];
          vm.myCoupons = data['my_coupons'];
          vm.weshareSettings = data['weshare_ship_settings'];
          if(vm.consignee&&vm.consignee.offlineStore){
            vm.checkedOfflineStore = vm.consignee.offlineStore;
          }
          vm.selectShipType = -1;
          if (vm.myCoupons) {
            vm.useCouponId = vm.myCoupons.CouponItem.id;
            vm.userCouponReduce = vm.myCoupons.Coupon.reduced_price;
          }
          vm.userShareSummery = data['user_share_summery'];
          if (vm.consignee) {
            vm.buyerName = vm.consignee.name;
            vm.buyerMobilePhone = vm.consignee.mobilephone;
            vm.buyerAddress = vm.consignee.address;
          }
          setWeiXinShareParams();
          //from paid done
          if (fromType == 1) {
            if (_.isEmpty(initSharedOfferId)) {
              vm.showNotifyShareDialog = true;
            } else {
              vm.showNotifyShareOfferDialog = true;
              vm.sharedOfferMsg = '恭喜发财，大吉大利！';
            }
            vm.showLayer = true;
          }
          //follow share
          if (followSharedType) {
            if (followSharedType == 'got') {
              vm.showNotifyGetPacketDialog = true;
              vm.getPacketNum = followSharedNum+'元';
              vm.showLayer = true;
              $timeout(function(){
                vm.showLayer = false;
                vm.showNotifyGetPacketDialog = false;
              },10000);
            }
          }
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function isCreator() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.id == vm.weshare.creator.id;
    }

    function isOwner(order) {
      return !_.isEmpty(vm.currentUser) && !_.isEmpty(order) && vm.currentUser.id == order.creator;
    }

    function isOrderReceived(order) {
      return !_.isEmpty(order) && order.status == 2;
    }

    function getOrderDisplayName(orderId) {
      var carts = vm.ordersDetail.order_cart_map[orderId];
      return _.map(carts, function (cart) {
        return cart.name + 'X' + cart.num;
      }).join(', ');
    }

    function toUserShareInfo($uid) {
      window.location.href = '/weshares/user_share_info/' + $uid;
    }

    function viewImage(url) {
      wx.previewImage({
        current: url,
        urls: vm.weshare.images
      });
    }

    function getConsigneeInfo(order) {
      if (_.isEmpty(order)) {
        return '';
      }
      return _.reject([order.consignee_name, order.consignee_mobilephone], function (e) {
        return _.isEmpty(e)
      }).join(',');
    }

    function calOrderTotalPrice() {
      var products = _.filter(vm.weshare.products, function (product) {
        return product.num > 0;
      });
      var totalPrice = 0;
      _.each(products, function (product) {
        totalPrice += product.price * product.num;
      });
      if (vm.userCouponReduce) {
        totalPrice -= vm.userCouponReduce;
      }
      vm.orderTotalPrice = totalPrice / 100;
    }

    function increaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      product.num = product.num + 1;
      calOrderTotalPrice();
      vm.validateProducts();
    }

    function decreaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      if (product.num >= 1) {
        product.num = product.num - 1;
      }
      calOrderTotalPrice();
      vm.validateProducts();
    }

    function validateMobile() {
      vm.buyerMobilePhoneHasError = !Utils.isMobileValid(vm.buyerMobilePhone);
      return vm.buyerMobilePhoneHasError;
    }

    function validateUserName() {
      vm.usernameHasError = _.isEmpty(vm.buyerName);
      return vm.usernameHasError;
    }

    function validateAddress() {
      vm.addressHasError = vm.weshare.addresses.length > 0 && vm.weshare.selectedAddressId == -1;
      return vm.addressHasError;
    }

    function validateProducts() {
      vm.productsHasError = _.all(vm.weshare.products, function (product) {
        return !product.num || product.num <= 0;
      });
      return vm.productsHasError;
    }


    function validateUserAddress() {
      vm.userAddressHasError = _.isEmpty(vm.buyerAddress);
      return vm.userAddressHasError;
    }

    function getProductLeftNum(product) {
      if (vm.ordersDetail.summery.details[product.id]) {
        var product_buy_num = vm.ordersDetail.summery.details[product.id]['num'];
        var store_num = product.store;
        return store_num - product_buy_num;
      }
      return product.store;
    }

    function checkProductNum(product) {
      var store_num = product.store;
      if (store_num == 0) {
        return true;
      }
      if (vm.ordersDetail.summery.details[product.id]) {
        var product_buy_num = vm.ordersDetail.summery.details[product.id]['num'];
        return product_buy_num < store_num;
      }
      return true;
    }

    function buyProducts() {
      var productsHasError = vm.validateProducts();
      if (productsHasError) {
        alert('请输入商品数量');
        return;
      }
      if(vm.selectShipType==-1){
        alert('请选择快递方式');
        vm.chooseShipType = true;
        return;
      }
      if(vm.selectShipType==2&&!vm.checkedOfflineStore){
        alert('请选择自提点');
        vm.chooseOfflineStoreError = true;
        return;
      }
      vm.chooseShipType = false;
      vm.showBuyingDialog = true;
      vm.showLayer = true;
    }

    function submitOrder(paymentType) {
      vm.validateUserName();
      vm.validateMobile();
      if (vm.buyerMobilePhoneHasError || vm.usernameHasError) {
        return false;
      }
      //kuai di
      if (vm.selectShipType == 0) {
        if (vm.validateUserAddress()) {
          return false;
        }
      }
      //self ziti
      if (vm.selectShipType == 1) {
        var addressHasError = vm.validateAddress();
        if (addressHasError) {
          return false;
        }
      }
      var products = _.filter(vm.weshare.products, function (product) {
        return product.num && (product.num > 0);
      });
      products = _.map(products, function (product) {
        return {id: product.id, num: product.num};
      });
      var ship_info = {
        ship_type: vm.selectShipType
      };
      if (vm.selectShipType == 1) {
        ship_info['address_id'] = vm.weshare.selectedAddressId;
      }
      if (vm.selectShipType == 2) {
        ship_info['address_id'] = vm.checkedOfflineStore.id;
      }
      var orderData = {
        weshare_id: vm.weshare.id,
        products: products,
        ship_info : ship_info,
        buyer: {name: vm.buyerName, mobilephone: vm.buyerMobilePhone, address: vm.buyerAddress}
      };
      if (vm.useCouponId) {
        orderData['coupon_id'] = vm.useCouponId;
      }
      if (vm.submitProcessing) {
        return;
      }
      vm.submitProcessing = true;
      $http.post('/weshares/makeOrder/', orderData).success(function (data) {
        if (data.success) {
          //pay
          window.location.href = '/weshares/pay/' + data.orderId + '/' + paymentType;
        } else {
          vm.submitProcessing = false;
          vm.showBuyingDialog = false;
          vm.showLayer = false;
          if (data['reason']) {
            alert(data['reason']);
          } else {
            alert('提交失败');
          }
        }
      }).error(function () {
        vm.submitProcessing = false;
      });
    }

    function confirmReceived(order) {
      if (_.isEmpty(order) || vm.isOrderReceived(order)) {
        return;
      }
      $http.post('/weshares/confirmReceived/' + order.id).success(function (data) {
        if (data.success) {
          order.status = 2;
        }
        else {
        }
      }).error(function (e) {
        $log.log(e);
      });
    }

    function stopShare() {
      var confirmResult = window.confirm('是否截止分享?');
      if (!confirmResult) {
        return false;
      }
      $http.post('/weshares/stopShare/' + vm.weshare.id).success(function (data) {
        if (data.success) {
          vm.weshare.status = 1;
        }
      }).error(function (e) {
        $log.log(e);
      });
    }

    function createMyShare() {
      var wx_article = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=209712305&idx=1&sn=ddb8431d97100d7e6305c2bc46d9ae75#rd';
      if (vm.currentUser['mobilephone'] && vm.currentUser['payment']) {
        window.location.href = '/weshares/add';
      } else {
        window.location.href = wx_article;
      }
    }

    function toShareOrderList() {
      window.location.href = '/weshares/share_order_list/' + vm.weshare.id;
    }

    function toUpdate() {
      window.location.href = '/weshares/update/' + vm.weshare.id;
    }

    function setWeiXinShareParams() {
      var url = 'http://www.tongshijia.com/weshares/view/' + vm.weshare.id;
      //creator
      var to_timeline_title = '';
      var to_friend_title = '';
      var imgUrl = '';
      var desc = '';
      var share_string = 'we_share';
      var to_friend_link = url;
      var to_timeline_link = url;
      //member
      var userInfo = vm.ordersDetail.users[vm.currentUser.id];
      if (vm.currentUser.id == vm.weshare.creator.id) {
        to_timeline_title = vm.weshare.creator.nickname + '分享:' + vm.weshare.title;
        to_friend_title = vm.weshare.creator.nickname + '分享:' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
        if (vm.ordersDetail.summery.all_buy_user_count >= 5) {
          desc += '已经有' + vm.ordersDetail.summery.all_buy_user_count + '人报名，';
        }
        desc += vm.weshare.description;
      } else if (userInfo) {
        to_timeline_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || userInfo.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description;
      } else if (vm.currentUser) {
        //default custom
        to_timeline_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.currentUser.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description;
      } else {
        to_timeline_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        to_friend_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description;
      }
      if (vm.weixinInfo) {
        share_string = vm.weixinInfo.share_string;
      }
      //share packet
      if (vm.isSharePacket) {
        imgUrl = 'http://www.tongshijia.com/static/weshares/images/redpacket/hbss_icon.jpg';
        var title = vm.weshare.creator.nickname+'给我一个红包大家一起抢';
        to_timeline_title = title;
        to_friend_title = title;
        url = url+ '?shared_offer_id=' + vm.sharedOfferId;
        to_friend_link = url;
        to_timeline_link = url;
        desc = vm.currentUser.nickname+'报名了小宝妈分享的鸡蛋。小宝妈我认识，很靠谱';
      }
      if (wx) {
        wx.ready(function () {
          wx.onMenuShareAppMessage({
            title: to_friend_title,
            desc: desc,
            link: to_friend_link,
            imgUrl: imgUrl,
            success: function () {
              // 用户确认分享后执行的回调函数
              if (share_string != '0') {
                setTimeout(function () {
                  $http.post('/wx_shares/log_share', {trstr: share_string, share_type: "appMsg"}).
                    success(function (data, status, headers, config) {
                      // this callback will be called asynchronously
                      // when the response is available
                    }).
                    error(function (data, status, headers, config) {
                      // called asynchronously if an error occurs
                      // or server returns response with an error status.
                    });
                }, 500);
              }
            }
          });
          wx.onMenuShareTimeline({
            title: to_timeline_title,
            link: to_timeline_link,
            imgUrl: imgUrl,
            success: function () {
              if (share_string != '0') {
                setTimeout(function () {
                  $http.post('/wx_shares/log_share', {trstr: share_string, share_type: "timeline"}).
                    success(function (data, status, headers, config) {
                      // this callback will be called asynchronously
                      // when the response is available
                    }).
                    error(function (data, status, headers, config) {
                      // called asynchronously if an error occurs
                      // or server returns response with an error status.
                    });
                }, 500);
              }
            }
          });
        });
      }
      return;
    }
  }
})(window, window.angular);