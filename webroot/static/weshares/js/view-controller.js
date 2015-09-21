(function (window, angular) {

  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl)

  function ChooseOfflineStore($vm, $log, $http, $templateCache, $timeout) {
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
    $http({
      method: 'GET',
      url: '/tuan_buyings/get_offline_address.json?type=-1',
      cache: $templateCache
    }).success(function (data) {
      $vm.offlineStores = data['address'];
    });
    $vm.changeOfflineStoreArea = changeOfflineStoreArea;
    $vm.showOfflineStoreDetail = showOfflineStoreDetail;
    $vm.chooseOfflineStore = chooseOfflineStore;
    $vm.showChooseOfflineStore = showChooseOfflineStore;
    $vm.mapPanTo = mapPanTo;
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

    function mapPanTo(offlineStore) {
      var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
      $vm.offlineStoreMap.panTo(point);
    }

    function showMap(offlineStore) {
      var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
      if ($vm.offlineStoreMap == null) {
        $vm.offlineStoreMap = new BMap.Map("offline-store-map");
        $vm.offlineStoreMap.centerAndZoom(point, 15);
      } else {
        $timeout(function () {
          $vm.mapPanTo(offlineStore);
        }, 200);
      }
      $vm.offlineStoreMap.clearOverlays();
      var marker = new BMap.Marker(point);        // 创建标注
      $vm.offlineStoreMap.addOverlay(marker);
    }

    function showOfflineStoreDetail(offlineStore) {
      $vm.currentOfflineStore = offlineStore;
      $vm.showOfflineStoreDetailView = true;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = false;
      $vm.showBalanceView = false;
      showMap($vm.currentOfflineStore);
    }

    function changeOfflineStoreArea(code) {
      $vm.currentAreaCode = code;
    }
  }

  function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils) {
    var vm = this;
    vm.showShareDetailView = true;
    vm.subShareTipTxt = '+关注';
    vm.showUnReadMark = false;
    vm.readMoreBtnText = '全文';
    vm.hideMoreShareInfo = false;
    vm.shouldShowReadMoreBtn = false;

    ChooseOfflineStore(vm, $log, $http, $templateCache, $timeout);
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.commentData = {};
    vm.submitTempCommentData = {};
    vm.viewImage = viewImage;
    vm.increaseProductNum = increaseProductNum;
    vm.decreaseProductNum = decreaseProductNum;
    vm.getOrderDisplayName = getOrderDisplayName;
    vm.isCreator = isCreator;
    vm.isProxy = isProxy;
    vm.isOwner = isOwner;
    vm.isOrderReceived = isOrderReceived;
    vm.getConsigneeInfo = getConsigneeInfo;
    vm.validateAddress = validateAddress;
    vm.validateProducts = validateProducts;
    vm.buyProducts = buyProducts;
    vm.validateMobile = validateMobile;
    vm.validateUserName = validateUserName;
    vm.validateUserAddress = validateUserAddress;
    vm.validateOrderData = validateOrderData;
    vm.submitOrder = submitOrder;
    vm.confirmReceived = confirmReceived;
    vm.toUserShareInfo = toUserShareInfo;
    vm.checkProductNum = checkProductNum;
    vm.getProductLeftNum = getProductLeftNum;
    vm.toShareOrderList = toShareOrderList;
    vm.createMyShare = createMyShare;
    vm.toUpdate = toUpdate;
    vm.stopShare = stopShare;
    vm.showShareDetail = showShareDetail;
    vm.calOrderTotalPrice = calOrderTotalPrice;
    vm.getStatusName = getStatusName;
    vm.getShipCode = getShipCode;
    vm.isShowShipCode = isShowShipCode;
    vm.showCommentDialog = showCommentDialog;
    vm.submitComment = submitComment;
    vm.getOrderComment = getOrderComment;
    vm.getReplyComments = getReplyComments;
    vm.showReplies = showReplies;
    vm.reloadCommentData = reloadCommentData;
    vm.showCommentListDialog = showCommentListDialog;
    vm.getOrderCommentLength = getOrderCommentLength;
    vm.initWeshareData = initWeshareData;
    vm.sortOrders = sortOrders;
    vm.closeCommentDialog = closeCommentDialog;
    vm.notifyUserToComment = notifyUserToComment;
    vm.loadSharerAllComments = loadSharerAllComments;
    vm.getFormatDate = getFormatDate;
    vm.notifyFans = notifyFans;
    vm.notifyType = notifyType;
    vm.sendNewShareMsg = sendNewShareMsg;
    vm.sendNotifyShareMsg = sendNotifyShareMsg;
    vm.validNotifyMsgContent = validNotifyMsgContent;
    vm.subSharer = subSharer;
    vm.pageLoaded = pageLoaded;
    vm.getRecommendInfo = getRecommendInfo;
    vm.isCurrentUserRecommend = isCurrentUserRecommend;
    vm.toRecommendUserInfo = toRecommendUserInfo;
    vm.cloneShare = cloneShare;
    vm.resetNotifyContent = resetNotifyContent;
    vm.defaultNotifyHasBuyMsgContent = defaultNotifyHasBuyMsgContent;
    vm.closeRecommendDialog = closeRecommendDialog;
    vm.submitRecommend = submitRecommend;
    vm.validRecommendContent=validRecommendContent;
    vm.checkHasUnRead = checkHasUnRead;
    vm.getProcessPrepaidStatus = getProcessPrepaidStatus;
    vm.handleReadMoreBtn = handleReadMoreBtn;
    vm.checkShareInfoHeight = checkShareInfoHeight;
    function pageLoaded(){
      $rootScope.loadingPage = false;
    }
    activate();
    function activate() {
      vm.initWeshareData();
    }

    function getDate(strDate) {
      var date = eval('new Date(' + strDate.replace(/\d+(?=-[^-]+$)/,
        function (a) { return parseInt(a, 10) - 1; }).match(/\d+/g) + ')');
      return date;
    }

    function getFormatDate(dateStr){
      var date = getDate(dateStr);
      var formatedDate = $filter('date')(date, 'MM-dd HH:mm');
      return formatedDate;
    }

    function loadSharerAllComments(sharer_id){
      $http({method: 'GET', url: '/weshares/load_share_comments/' + sharer_id+'.json', cache: $templateCache}).
        success(function (data, status) {
          vm.sharerAllComments = data['share_all_comments'];
          vm.sharerAllCommntesUser = data['share_comment_all_users'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function initWeshareData() {
      var rebateLogId = angular.element(document.getElementById('weshareView')).attr('data-rebate-log-id');
      var recommendUserId = angular.element(document.getElementById('weshareView')).attr('data-recommend-id');
      vm.rebateLogId = rebateLogId || 0;
      vm.recommendUserId = recommendUserId || 0;
      var weshareId = angular.element(document.getElementById('weshareView')).attr('data-weshare-id');
      var fromType = angular.element(document.getElementById('weshareView')).attr('data-from-type');
      //first share
      var initSharedOfferId = angular.element(document.getElementById('weshareView')).attr('data-shared-offer');
      var followSharedType = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-type');
      var followSharedNum = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-coupon-num');
      vm.sharedOfferId = initSharedOfferId;
      if (initSharedOfferId) {
        vm.isSharePacket = true;
      }
      vm.weshare = {};
      vm.orderTotalPrice = 0;
      $scope.$watch('vm.selectShipType', function (val) {
        if (val != -1) {
          vm.chooseShipType = false;
        }
      });
      $scope.$watchCollection('vm.checkedOfflineStore', function (val) {
        if (val) {
          vm.chooseOfflineStoreError = false;
        }
      });
      $http({method: 'GET', url: '/weshares/detail/' + weshareId+'.json', cache: $templateCache}).
        success(function (data, status) {
          vm.weshare = data['weshare'];
          vm.commentData = data['comment_data'];
          vm.orderComments = vm.commentData['order_comments'];
          if (vm.weshare.addresses && vm.weshare.addresses.length == 1) {
            vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
          }else if (vm.weshare.addresses && vm.weshare.addresses.length > 1) {
            vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
            vm.weshare.selectedAddressId = -1;
          }
          vm.isManage = data['is_manage'];
          vm.recommendData = data['recommendData'];
          vm.ordersDetail = data['ordersDetail'];
          vm.shipTypes = data['ordersDetail']['ship_types'];
          vm.rebateLogs = data['ordersDetail']['rebate_logs'];
          vm.currentUser = data['current_user'] || {};
          vm.weixinInfo = data['weixininfo'];
          vm.consignee = data['consignee'];
          vm.myCoupons = data['my_coupons'];
          vm.weshareSettings = data['weshare_ship_settings'];
          vm.supportPysZiti = data['support_pys_ziti'];
          vm.selectShipType = getSelectTypeDefaultVal(vm.weshareSettings);
          vm.userSubStatus = data['sub_status'];
          vm.submitRecommendData = {};
          vm.submitRecommendData.recommend_content = vm.weshare.creator.nickname+'我认识，很靠谱！';
          vm.submitRecommendData.recommend_user = vm.currentUser.id;
          vm.submitRecommendData.recommend_share = vm.weshare.id;
          vm.sortOrders();
          if (vm.consignee && vm.consignee.offlineStore) {
            vm.checkedOfflineStore = vm.consignee.offlineStore;
          }
          if (vm.myCoupons) {
            vm.useCouponId = vm.myCoupons.CouponItem.id;
            vm.userCouponReduce = vm.myCoupons.Coupon.reduced_price;
          }
          vm.userShareSummery = data['user_share_summery'];
          if (vm.consignee) {
            vm.buyerName = vm.consignee.name;
            vm.buyerMobilePhone = vm.consignee.mobilephone;
            vm.buyerAddress = vm.consignee.address;
            vm.buyerPatchAddress = vm.consignee.remark_address;
          }
          setWeiXinShareParams();
          //from paid done
          if (fromType == 1) {
            if (_.isEmpty(initSharedOfferId)) {
              vm.showNotifyShareDialog = true;
              vm.showLayer = true;
            } else {
              //check is new user buy it
              var userInfo = vm.ordersDetail.users[vm.currentUser.id];
              if (userInfo) {
                vm.showNotifyShareOfferDialog = true;
                vm.sharedOfferMsg = '恭喜发财，大吉大利！';
                vm.showLayer = true;
              }
            }
          }
          //follow share
          if (followSharedType) {
            if (followSharedType == 'got') {
              vm.showNotifyGetPacketDialog = true;
              vm.getPacketNum = followSharedNum + '元';
              vm.showLayer = true;
              $timeout(function () {
                vm.showLayer = false;
                vm.showNotifyGetPacketDialog = false;
              }, 10000);
            }
          }
          //load all comments
          vm.loadSharerAllComments(vm.weshare.creator.id);
          vm.checkHasUnRead();
          vm.checkShareInfoHeight();
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function sortOrders() {
      if (!vm.isCreator()) {
        vm.ordersDetail.orders = _.sortBy(vm.ordersDetail.orders, function (order) {
          if (order.status == 9 && order.creator == vm.currentUser.id) {
            return -2147483646;
          } else if (order.creator == vm.currentUser.id) {
            return -2147483647;
          } else {
            return order.id;
          }
        });
      }
    }

    function getSelectTypeDefaultVal(shipSettings) {
      if (vm.weshareSettings.kuai_di.status == 1) {
        vm.shipFee = vm.weshareSettings.kuai_di.ship_fee;
        return 0;
      }
      if (vm.weshareSettings.self_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
        return 1;
      }
      if (vm.weshareSettings.pys_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
        return 2;
      }
      return -1;
    }

    function isProxy(){
      return !_.isEmpty(vm.currentUser) && vm.currentUser.is_proxy == 1
    }

    function isCreator() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.id == vm.weshare.creator.id;
    }

    function isOwner(order) {
      return !_.isEmpty(vm.currentUser) && !_.isEmpty(order) && vm.currentUser.id == order.creator;
    }

    function isOrderReceived(order) {
      return !_.isEmpty(order) && order.status == 3;
    }

    function getOrderDisplayName(orderId) {
      var carts = vm.ordersDetail.order_cart_map[orderId];
      return _.map(carts, function (cart) {
        return cart.name + 'X' + cart.num;
      }).join(', ');
    }

    function showShareDetail() {
      vm.showOfflineStoreDetailView = false;
      vm.chooseOfflineStoreView = false;
      vm.showBalanceView = false;
      vm.showShareDetailView = true;
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
      if(totalPrice != 0){
        if (vm.userCouponReduce) {
          totalPrice -= vm.userCouponReduce;
        }
        vm.shipFee = parseInt(getShipFee());
        vm.shipSetId = getShipSetId();
        totalPrice += vm.shipFee;
        vm.orderTotalPrice = totalPrice / 100;
      }else{
        vm.orderTotalPrice = 0;
      }
    }

    function getOrderComment(order_id) {
      return vm.commentData['order_comments'][order_id];
    }

    function getOrderCommentLength() {
      if (vm.sharerAllComments) {
        return vm.sharerAllComments.length;
      }
      return 0;
    }

    function getReplyComments(comment_id) {
      return vm.commentData['comment_replies'][comment_id];
    }

    function showReplies(comment_id) {
      var replies = vm.commentData['comment_replies'][comment_id];
      if (!replies || replies.length == 0) {
        return false;
      }
      return true;
    }

    function getRecommendInfo(order){
      var recommendId = vm.rebateLogs[order['cate_id']];
      var recommend = vm.ordersDetail['users'][recommendId]['nickname'];
      return recommend+'推荐';
    }

    function isCurrentUserRecommend(order) {
      if (vm.isCreator()) {
        return true;
      }
      if (vm.currentUser['is_proxy'] == 0) {
        return false;
      }
      var recommendId = vm.rebateLogs[order['cate_id']];
      if (vm.currentUser['id'] == recommendId) {
        return true;
      }
      return false;
    }

    function toRecommendUserInfo(order){
      var recommendId = vm.rebateLogs[order['cate_id']];
      window.location.href = '/weshares/user_share_info/' + recommendId;
    }

    function getShipSetId() {
      if (vm.selectShipType == 0) {
        return vm.weshareSettings.kuai_di.id;
      }
      if (vm.selectShipType == 1) {
        return vm.weshareSettings.self_ziti.id;
      }
      if (vm.selectShipType == 2) {
        return vm.weshareSettings.pys_ziti.id;
      }
    }

    function getShipFee() {
      if (vm.selectShipType == 0) {
        return vm.weshareSettings.kuai_di.ship_fee;
      }
      if (vm.selectShipType == 1) {
        return vm.weshareSettings.self_ziti.ship_fee;
      }
      if (vm.selectShipType == 2) {
        return vm.weshareSettings.pys_ziti.ship_fee;
      }
    }

    function increaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      //check product is reach limit
      if (product.limit > 0 && product.num == product.limit) {
        alert('亲，最多可购' + product.limit + '份.');
      } else {
        product.num = product.num + 1;
      }
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
      vm.showShareDetailView = false;
      vm.showBalanceView = true;
      vm.chooseShipType = false;
    }

    function submitOrder(paymentType) {
      if (!vm.validateOrderData()) {
        return false;
      }
      var products = _.filter(vm.weshare.products, function (product) {
        return product.num && (product.num > 0);
      });
      products = _.map(products, function (product) {
        return {id: product.id, num: product.num};
      });
      vm.shipFee = vm.shipFee || 0;
      var ship_info = {
        ship_type: vm.selectShipType,
        ship_fee: vm.shipFee,
        ship_set_id: vm.shipSetId
      };
      if (vm.selectShipType == 1) {
        ship_info['address_id'] = vm.weshare.selectedAddressId;
      }
      if (vm.selectShipType == 2) {
        ship_info['address_id'] = vm.checkedOfflineStore.id;
      }
      var orderData = {
        weshare_id: vm.weshare.id,
        rebate_log_id: vm.rebateLogId,
        products: products,
        ship_info: ship_info,
        buyer: {
          name: vm.buyerName,
          mobilephone: vm.buyerMobilePhone,
          address: vm.buyerAddress,
          patchAddress: vm.buyerPatchAddress
        }
      };
      if (vm.useCouponId) {
        orderData['coupon_id'] = vm.useCouponId;
      }
      if (vm.submitProcessing) {
        return;
      }
      vm.submitProcessing = true;
      $http.post('/weshares/makeOrder', orderData).success(function (data) {
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
            alert('提交失败.请联系客服..');
          }
        }
      }).error(function () {
        vm.submitProcessing = false;
      });
    }

    function cloneShare(){
      vm.cloneShareProcessing = true;
      $http.post('/weshares/cloneShare/'+vm.weshare.id).success(function (data) {
        if (data.success) {
          //redirect view
          window.location.href = '/weshares/view/' + data['shareId'];
        } else {
          vm.cloneShareProcessing = false;
          if (data['reason']) {
            alert(data['reason']);
          } else {
            alert('提交失败');
          }
        }
      }).error(function () {
        vm.cloneShareProcessing = false;
      });
    }

    function reloadCommentData() {
      $http({method: 'GET', url: '/weshares/loadComment/' + vm.weshare.id+'.json'}).
        success(function (data) {
          vm.commentData = data;
        }).
        error(function (data) {
          $log.log(data);
        });
    }

    function notifyUserToComment(order) {
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = 0;
      vm.submitTempCommentData.share_id = vm.weshare.id;
      vm.submitComment();
      order.notify = true;
    }

    function submitComment() {
      $http.post('/weshares/comment', vm.submitTempCommentData).success(function (data) {
        if (data.success) {
          if(data.type=='notify'){
            $window.alert('已经通知TA');
            return true;
          }
          var order_id = data['order_id'];
          vm.reloadCommentData();
          if (vm.commentOrder.id == order_id && vm.commentOrder.status == 3) {
            vm.commentOrder.status = 9;
          }
        } else {
          alert('提交失败');
        }
      }).error(function () {
        alert('提交失败');
      });
      vm.closeCommentDialog();
    }

    function resetNotifyContent(){
      if(vm.sendNotifyType == 0){
        vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
      }else{
        vm.notify.content = '';
      }
    }

    function defaultNotifyHasBuyMsgContent(){
      var msgContent = '';
      if (Object.keys(vm['ordersDetail']['users']).length > 10) {
        var usersCount = Object.keys(vm['ordersDetail']['users']).length;
        var index = 0;
        for (var userId in vm['ordersDetail']['users']) {
          var user = vm['ordersDetail']['users'][userId];
          index++;
          if (index > 10) {
            break
          }
          if (index == 10) {
            msgContent =  msgContent + user['nickname'];
          }else{
            msgContent = msgContent + user['nickname'] + '，';
          }
        }
        msgContent = msgContent+'...等'+usersCount+'人都已经报名'+vm.weshare.creator.nickname+'分享的'+vm.weshare.title+'啦，就差你啦。';
      }else{
        msgContent = _.reduce(vm.ordersDetail.users, function(memo, user){ return memo + user['nickname']+'，'; }, '');
        msgContent = msgContent+'都已经报名'+vm.weshare.creator.nickname+'分享的'+vm.weshare.title+'啦，就差你啦。';
      }
      return msgContent;
    }

    function notifyFans(){
      vm.showNotifyView = true;
      vm.showLayer = true;
      vm.sendNotifyType = 0;
      vm.notify = {};
      vm.notify.type = vm.sendNotifyType;
      vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
    }

    function notifyType(){
      if(vm.ordersDetail.orders&&vm.ordersDetail.orders.length>0){
        return 1;
      }
      return 0;
    }

    function sendNewShareMsg(){
      if (confirm('是否要发送消息，发送次数过多会对用户形成骚扰?')) {
        $http({
          method: 'GET',
          url: '/weshares/send_new_share_msg/' + vm.weshare.id
        }).success(function (data) {
          // With the data succesfully returned, call our callback
          if (data['success']) {
            alert('发送成功');
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function subSharer() {
      if (vm.hasProcessSubSharer) {
        return;
      }
      $http({
        method: 'GET',
        url: '/weshares/subscribe_sharer/' + vm.weshare.creator.id + '/' + vm.currentUser.id+'.json'
      }).success(function (data) {
        // With the data succesfully returned, call our callback
        if (data['success']) {
          vm.hasProcessSubSharer = true;
          vm.subShareTipTxt = '已关注';
        }
      }).error(function () {
        vm.hasProcessSubSharer = false;
      });
    }

    function validNotifyMsgContent(){
      if(_.isEmpty(vm.notify.content)){
        vm.notifyMsgHasError = true;
        return;
      }
      vm.notifyMsgHasError = false;
    }

    function sendNotifyShareMsg(){
      if(_.isEmpty(vm.notify.content)){
        vm.notifyMsgHasError = true;
        return;
      }
      vm.notifyMsgHasError = false;
      if (confirm('是否要发送消息，发送次数过多会对用户形成骚扰?')) {
        $http({
          method: 'POST',
          url: '/weshares/send_buy_percent_msg/' + vm.weshare.id,
          data: {content: vm.notify.content, type: vm.sendNotifyType}
        }).success(function (data) {
          // With the data succesfully returned, call our callback
          if (data['success']) {
            vm.showNotifyView = false;
            vm.showLayer = false;
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function showCommentListDialog() {
      vm.showCommentListDialogView=true;
      vm.showLayer = true;
    }

    function closeCommentDialog() {
      vm.showCommentingDialog = false;
      vm.showLayer=false;
      vm.submitTempCommentData = {};
    }

    function showCommentDialog(order, comment) {
      vm.showCommentingDialog = true;
      vm.showLayer = true;
      var reply_comment_id = 0;
      var comment_tip_info = '';
      if (comment) {
        reply_comment_id = comment.id || 0;
        var reply_username = comment.username;
        if (comment.plain_username) {
          reply_username = comment.plain_username;
        }
        if (reply_username == vm.currentUser.nickname) {
          comment_tip_info = '爱心评价';
        } else {
          comment_tip_info = '回复' + reply_username + '：';
        }
      } else {
        //check is creator
        if(vm.currentUser.id == vm.weshare.creator.id){
          var order_username = vm.ordersDetail.users[order.creator]['nickname'];
          comment_tip_info = '回复' + order_username + '说：';
        }else{
          comment_tip_info = '回复' + vm.weshare.creator.nickname + '说：';
        }
      }
      vm.submitTempCommentData = {};
      vm.commentTipInfo = comment_tip_info;
      vm.commentOrder = order;
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = reply_comment_id;
      vm.submitTempCommentData.share_id = vm.weshare.id;
    }

    function getProcessPrepaidStatus(status){
      if(status==25){
        return '金额待定';
      }
      if(status==26){
        return '待补款';
      }
      if(status==27){
        return '已补款';
      }
      if(status==28){
        return '待退差价';
      }
      if(status==29){
        return '差价已退';
      }
    }

    function getStatusName(status, orderType) {
      if (status == 1) {
        return '待发货';
      }
      if (status == 2) {
        if (orderType == 'kuai_di') {
          return '待签收';
        }
        return '待取货';
      }
      if (status == 3) {
        return '待评价';
      }
      return '已完成';
    }

    function validateOrderData() {
      var productsHasError = vm.validateProducts();
      if (productsHasError) {
        alert('请输入商品数量');
        return;
      }
      if (vm.selectShipType == -1) {
        alert('请选择快递方式');
        vm.chooseShipType = true;
        return false;
      }
      if (vm.selectShipType == 2 && !vm.checkedOfflineStore) {
        alert('请选择自提点');
        vm.chooseOfflineStoreError = true;
        return false;
      }
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
      return true;
    }

    function confirmReceived(order) {
      if (_.isEmpty(order) || vm.isOrderReceived(order)) {
        return;
      }
      $http.post('/weshares/confirmReceived/' + order.id).success(function (data) {
        if (data.success) {
          order.status = 3;
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

    function getShipCode(order) {
      var shipMark = order['ship_mark'];
      var code = order['ship_code'];
      if (shipMark == 'kuai_di') {
        var ship_company = order['ship_type'];
        return vm.shipTypes[ship_company] + ': ' + code;
      }
      if (shipMark == 'pys_zi_ti') {
        return '提货码: ' + code;
      }
      return '';
    }

    function closeRecommendDialog() {
      vm.showShareDialog = true;
      vm.showRecommendDialog = false;
    }

    function submitRecommend() {
      if (vm.validRecommendContent()) {
        return false;
      }
      $http.post('/weshares/recommend', vm.submitRecommendData).success(function (data) {

      }).error(function (e) {
        $log.log(e);
      });
      vm.closeRecommendDialog();
    }

    function validRecommendContent() {
      vm.recommendContentHasError = false;
      if(_.isEmpty(vm.submitRecommendData.recommend_content)){
        vm.recommendContentHasError = true;
      }
      return vm.recommendContentHasError;
    }

    function checkHasUnRead(){
      $http.get('/share_opt/check_opt_has_new.json').success(function(data){
        if(data['has_new']){
          vm.showUnReadMark = true;
        }
      });
    }

    function isShowShipCode(order) {
      if (order['ship_mark'] == 'kuai_di' || order['ship_mark'] == 'pys_zi_ti') {
        if (order.status != 1 && vm.isOwner(order)) {
          return true;
        }
      }
      return false;
    }

    //vm.handleReadMoreBtn = handleReadMoreBtn;
    //vm.checkShareInfoHeight = checkShareInfoHeight;

    function handleReadMoreBtn() {
      vm.hideMoreShareInfo = !vm.hideMoreShareInfo;
      if (vm.hideMoreShareInfo) {
        vm.readMoreBtnText = '全文';
      } else {
        vm.readMoreBtnText = '收起';
      }
    }

    function checkShareInfoHeight(){
      vm.shareDescInfoElement = angular.element(document.getElementById('share-description'));
      vm.shareDescInfoElement.ready(function(){
        var height = vm.shareDescInfoElement[0].offsetHeight;
        if(height > 65){
          vm.shouldShowReadMoreBtn = true;
          vm.hideMoreShareInfo = true;
        }else{
          vm.shouldShowReadMoreBtn = false;
          vm.hideMoreShareInfo = false;
        }
      });
    }

    function setWeiXinShareParams() {
      var url = 'http://www.tongshijia.com/weshares/view/' + vm.weshare.id;
      //creator
      var to_timeline_title = '';
      var to_friend_title = '';
      var imgUrl = '';
      var desc = '';
      var share_string = 'we_share';
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
        if (vm.isProxy()) {
          url = url + '?recommend=' + vm.currentUser['id'];
        }
        if(!vm.isProxy()&&vm.recommendUserId!=0){
          url = url + '?recommend=' + vm.recommendUserId;
        }
        to_timeline_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || userInfo.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description;
      } else if (vm.currentUser) {
        //default custom
        if (vm.isProxy()) {
          url = url + '?recommend=' + vm.currentUser['id'];
        }
        if(!vm.isProxy()&&vm.recommendUserId!=0){
          url = url + '?recommend=' + vm.recommendUserId;
        }
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
      if (vm.isSharePacket && userInfo) {
        url = 'http://www.tongshijia.com/weshares/view/' + vm.weshare.id;
        imgUrl = 'http://www.tongshijia.com/static/weshares/images/share_icon.jpg';
        var title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_timeline_title = title;
        to_friend_title = title;
        url = url + '?shared_offer_id=' + vm.sharedOfferId;
        if (vm.isProxy()) {
          url = url + '&recommend=' + vm.currentUser['id'];
        }
        if(!vm.isProxy()&&vm.recommendUserId!=0){
          url = url + '&recommend=' + vm.recommendUserId;
        }
        desc = vm.weshare.creator.nickname + '我认识，很靠谱！送你一个爱心礼包，一起来参加。';
      }
      var to_friend_link = url;
      var to_timeline_link = url;
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