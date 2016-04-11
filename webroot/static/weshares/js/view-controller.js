(function (window, angular) {

  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl);

  function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath, shipTypes, ShareOrder, OfflineStore) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.showShareDetailView = true;
    //vm.subShareTipTxt = '+关注';
    vm.faqTipText = '私信';
    vm.showUnReadMark = false;
    vm.readMoreBtnText = '全文';
    vm.hideMoreShareInfo = false;
    vm.shouldShowReadMoreBtn = false;
    vm.startNewGroupShare = false;
    vm.chooseOfflineAddress = null;
    vm.isGroupShareType = false;
    OfflineStore.ChooseOfflineStore(vm);
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.shipTypes = shipTypes;
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
    vm.validateLocation = validateLocation;
    vm.buyProducts = buyProducts;
    vm.startGroupShare = startGroupShare;
    vm.validateMobile = validateMobile;
    vm.validateUserName = validateUserName;
    vm.validateUserAddress = validateUserAddress;
    vm.validateOrderData = validateOrderData;
    vm.submitOrder = submitOrder;
    vm.confirmReceived = confirmReceived;
    vm.toUserShareInfo = toUserShareInfo;
    vm.toShareDetailView = toShareDetailView;
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
    vm.showAutoCommentDialog = showAutoCommentDialog;
    vm.submitComment = submitComment;
    vm.getOrderComment = getOrderComment;
    vm.getReplyComments = getReplyComments;
    vm.showReplies = showReplies;
    vm.reloadCommentData = reloadCommentData;
    vm.showCommentListDialog = showCommentListDialog;
    vm.getOrderCommentLength = getOrderCommentLength;
    vm.initWeshareData = initWeshareData;
    vm.sortOrders = sortOrders;
    vm.combineShareBuyData = combineShareBuyData;
    vm.closeCommentDialog = closeCommentDialog;
    vm.notifyUserToComment = notifyUserToComment;
    vm.loadSharerAllComments = loadSharerAllComments;
    vm.loadOrderDetail = loadOrderDetail;
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
    vm.validRecommendContent = validRecommendContent;
    vm.checkHasUnRead = checkHasUnRead;
    vm.getProcessPrepaidStatus = getProcessPrepaidStatus;
    vm.toggleTag = toggleTag;
    vm.supportGroupBuy = supportGroupBuy;
    vm.offlineAddressData = null;
    vm.loadOfflineAddressData = loadOfflineAddressData;
    vm.setShipFee = setShipFee;
    vm.newGroupShare = newGroupShare;
    vm.redirectFaq = redirectFaq;
    vm.checkUserHasStartGroupShare = checkUserHasStartGroupShare;
    vm.chatToUser = chatToUser;
    vm.calProxyRebateFee = calProxyRebateFee;
    vm.loadOrderCommentData = loadOrderCommentData;
    vm.isShareManager = isShareManager;
    vm.isShowExpressInfoBtn = isShowExpressInfoBtn;
    vm.isShowCallLogisticsBtn = isShowCallLogisticsBtn;
    vm.isShowCancelLogisticsBtn = isShowCancelLogisticsBtn;
    vm.handleCallLogistics = handleCallLogistics;
    vm.handleCancelLogistics = handleCancelLogistics;
    vm.showOrderExpressInfo = showOrderExpressInfo;
    vm.getLogisticsBtnText = getLogisticsBtnText;
    vm.showLogisticsStatusInfo = showLogisticsStatusInfo;
    vm.getLogisticsStatusText = getLogisticsStatusText;
    vm.initProvince = initProvince;
    vm.loadCityData = loadCityData;
    vm.loadCountyData = loadCounty;
    vm.calculateShipFee = calculateShipFee;
    vm.unSubSharer = unSubSharer;
    vm.childShareDetail = null;
    vm.currentUserOrderCount = 0;
    vm.totalBuyCount = 0;
    vm.rebateFee = 0;


    function pageLoaded() {
      $rootScope.loadingPage = false;
    }

    activate();

    function activate() {
      vm.initWeshareData();
      vm.initProvince();
    }

    function getDate(strDate) {
      var date = eval('new Date(' + strDate.replace(/\d+(?=-[^-]+$)/,
        function (a) {
          return parseInt(a, 10) - 1;
        }).match(/\d+/g) + ')');
      return date;
    }

    function getFormatDate(dateStr) {
      var date = getDate(dateStr);
      var formatedDate = $filter('date')(date, 'MM-dd HH:mm');
      return formatedDate;
    }

    function loadOrderCommentData(share_id) {
      $http({method: 'GET', url: '/weshares/get_share_comment_data/' + share_id + '.json'}).
        success(function (data, status) {
          vm.commentData = data['comment_data'];
          vm.orderComments = vm.commentData['order_comments'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function loadSharerAllComments(sharer_id) {
      $http({method: 'GET', url: '/weshares/load_share_comments/' + sharer_id + '.json', cache: $templateCache}).
        success(function (data, status) {
          vm.sharerAllComments = data['share_all_comments'];
          vm.sharerAllCommntesUser = data['share_comment_all_users'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    /**
     * 获取线下自提点和简单的购买数据购买数据
     * @param share_id
     */
    function loadOfflineAddressData(share_id) {
      $http({
        method: 'GET',
        url: '/weshares/get_offline_address_detail/' + share_id + '.json',
        cache: $templateCache
      }).
        success(function (data, status) {
          vm.offlineAddressData = data;
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function checkUserHasStartGroupShare(userId) {
      for (key in vm.childShareDetail) {
        var itemShare = vm.childShareDetail[key];
        if (itemShare['creator'] == userId) {
          return true;
        }
      }
      return false;
    }

    function chatToUser(userId) {
      if (userId == vm.currentUser.id) {
        window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + userId;
        return;
      }
      window.location.href = '/share_faq/faq/' + vm.weshare.id + '/' + userId;
    }

    function loadOrderDetail(share_id) {
      var fromType = angular.element(document.getElementById('weshareView')).attr('data-from-type');
      //first share
      var initSharedOfferId = angular.element(document.getElementById('weshareView')).attr('data-shared-offer');
      var followSharedType = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-type');
      var followSharedNum = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-coupon-num');
      vm.sharedOfferId = initSharedOfferId;
      if (initSharedOfferId) {
        vm.isSharePacket = true;
      }
      $http({
        method: 'GET',
        url: '/weshares/get_share_user_order_and_child_share/' + share_id + '.json',
        cache: $templateCache
      }).
        success(function (data, status) {
          vm.ordersDetail = data['ordersDetail'];
          vm.childShareDetail = data['childShareData']['child_share_data'];
          vm.childShareDetailUsers = data['childShareData']['child_share_user_infos'];
          vm.childShareDetailUsersLevel = data['childShareData']['child_share_level_data'];
          //vm.shipTypes = data['ordersDetail']['ship_types'];
          vm.rebateLogs = data['ordersDetail']['rebate_logs'];
          vm.logisticsOrderData = data['logisticsOrderData'];
          //vm.sortOrders();
          vm.combineShareBuyData();
          setWeiXinShareParams();
          //check user is auto comment
          if (vm.autoPopCommentData['comment_order_info']) {
            vm.showAutoCommentDialog();
          } else {
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
          }
          //process page order info
          vm.shareOrder = new ShareOrder();
          vm.shareOrder.shareId = share_id;
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function initWeshareData() {
      var rebateLogId = angular.element(document.getElementById('weshareView')).attr('data-rebate-log-id');
      var recommendUserId = angular.element(document.getElementById('weshareView')).attr('data-recommend-id');
      //auto poup comment dialog
      var commentOrderId = angular.element(document.getElementById('weshareView')).attr('data-comment-order-id');
      var replayCommentId = angular.element(document.getElementById('weshareView')).attr('data-replay-comment-id');
      vm.rebateLogId = rebateLogId || 0;
      vm.recommendUserId = recommendUserId || 0;
      var weshareId = angular.element(document.getElementById('weshareView')).attr('data-weshare-id');
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
      var shareDetailUrl = '/weshares/detail/' + weshareId + '.json?comment_order_id=' + commentOrderId + '&reply_comment_id=' + replayCommentId;
      $http({
        method: 'GET', url: shareDetailUrl, params: {
          "comment_order_id": commentOrderId,
          "reply_comment_id": replayCommentId
        }, cache: $templateCache
      }).
        success(function (data, status) {
          vm.weshare = data['weshare'];
          if (vm.weshare.type == 1) {
            vm.isGroupShareType = true;
          }
          vm.toggleState = {0: {open: true, statusText: '收起'}};
          _.each(vm.weshare.tags, function (value, key) {
            vm.toggleState[key] = {
              open: true,
              statusText: '收起'
            };
          });
          if (vm.weshare.addresses && vm.weshare.addresses.length == 1) {
            vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
          } else if (vm.weshare.addresses && vm.weshare.addresses.length > 1) {
            vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
            vm.weshare.selectedAddressId = -1;
          }
          vm.isManage = data['is_manage'];
          vm.canManageShare = data['can_manage_share'];
          vm.canEditShare = data['can_edit_share'];
          vm.recommendData = data['recommendData'];
          vm.currentUser = data['current_user'] || {};
          vm.weixinInfo = data['weixininfo'];
          vm.consignee = data['consignee'];
          vm.myCoupons = data['my_coupons'];
          vm.weshareSettings = data['weshare_ship_settings'];
          vm.supportPysZiti = data['support_pys_ziti'];
          vm.selectShipType = getSelectTypeDefaultVal();
          vm.userSubStatus = data['sub_status'];
          vm.totalBuyCount = data['all_buy_count'];
          vm.favourableConfig = data['favourable_config'];
          vm.autoPopCommentData = data['prepare_comment_data'];
          vm.dliveryTemplate = data['weshare']['deliveryTemplate'];
          vm.submitRecommendData = {};
          vm.submitRecommendData.recommend_content = vm.weshare.creator.nickname + '我认识，很靠谱！';
          vm.submitRecommendData.recommend_user = vm.currentUser.id;
          vm.submitRecommendData.recommend_share = vm.weshare.id;
          if (vm.consignee) {
            if(vm.consignee.offlineStore){
              vm.checkedOfflineStore = vm.consignee.offlineStore;
            }
            vm.selectedProvince = vm.consignee.province_id;
            vm.selectedCity = vm.consignee.city_id;
            if(vm.consignee.city_id){
              vm.loadCityData(vm.consignee.province_id);
            }
            vm.selectedCounty = vm.consignee.county_id;
            if(vm.consignee.county_id){
              vm.loadCountyData(vm.consignee.city_id);
            }
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
          if (vm.isCreator() || vm.canManageShare) {
            vm.faqTipText = '反馈消息';
          }
          //vm.checkShareInfoHeight();
          //load all comments
          vm.loadOrderDetail(weshareId);
          vm.loadOrderCommentData(weshareId);
        }).
        error(function (data, status) {
          $log.log(data);
        });
      vm.checkHasUnRead();
    }

    /**
     * 订单数据和拼团数据组合
     */
    function combineShareBuyData() {
      var insertIndex = vm.currentUserOrderCount;
      for (childShareItem in vm.childShareDetail) {
        vm.ordersDetail.orders.splice(insertIndex, 0, vm.childShareDetail[childShareItem]);
        insertIndex++;
      }
    }

    function sortOrders() {
      if (!vm.isCreator()) {
        vm.ordersDetail.orders = _.sortBy(vm.ordersDetail.orders, function (order) {
          if (order.status == 9 && order.creator == vm.currentUser.id) {
            vm.currentUserOrderCount = vm.currentUserOrderCount + 1;
            return -2147483646;
          } else if (order.creator == vm.currentUser.id) {
            vm.currentUserOrderCount = vm.currentUserOrderCount + 1;
            return -2147483647;
          } else {
            return order.id;
          }
        });
      }
    }

    function getSelectTypeDefaultVal() {
      if (vm.weshareSettings.pin_tuan && vm.weshareSettings.pin_tuan.status == 1) {
        vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
        return 3;
      }
      if (vm.weshareSettings.kuai_di && vm.weshareSettings.kuai_di.status == 1) {
        vm.shipFee = vm.weshareSettings.kuai_di.ship_fee;
        return 0;
      }
      if (vm.weshareSettings.self_ziti && vm.weshareSettings.self_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
        return 1;
      }
      if (vm.weshareSettings.pys_ziti && vm.weshareSettings.pys_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
        return 2;
      }
      return -1;
    }

    function isProxy() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.is_proxy == 1
    }

    function isCreator() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.id == vm.weshare.creator.id;
    }

    function isShareManager() {
      return vm.isCreator() || vm.canManageShare;
    }

    function isOwner(order) {
      //note may be a child share item
      return !_.isEmpty(vm.currentUser) && !_.isEmpty(order) && vm.currentUser.id == order.creator;
    }

    function isOrderReceived(order) {
      return !_.isEmpty(order) && order.status == 3;
    }

    function getOrderDisplayName(orderId) {
      var carts = [];
      if (vm.ordersDetail && vm.ordersDetail.order_cart_map && vm.ordersDetail.order_cart_map[orderId]) {
        carts = vm.ordersDetail.order_cart_map[orderId];
      } else {
        carts = vm.shareOrder.order_cart_map[orderId];
      }
      return _.map(carts, function (cart) {
        return cart.name + 'X' + cart.num;
      }).join(', ');
    }

    function redirectFaq() {
      if (vm.isCreator() || vm.canManageShare) {
        window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + vm.weshare.creator.id;
      } else {
        window.location.href = '/share_faq/faq/' + vm.weshare.id + '/' + vm.weshare.creator.id;
      }
    }

    function showShareDetail() {
      vm.showOfflineStoreDetailView = false;
      vm.chooseOfflineStoreView = false;
      vm.showBalanceView = false;
      vm.showStartGroupShareView = false;
      vm.showShareDetailView = true;
      vm.startNewGroupShare = false;
    }

    function toShareDetailView($share_id) {
      window.location.href = '/weshares/view/' + $share_id;
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

    function calProxyRebateFee(totalPrice) {
      if (!vm.currentUser || vm.currentUser['is_proxy'] == 0) {
        return;
      }
      if (!vm.weshare.proxy_rebate_percent || vm.weshare.proxy_rebate_percent.percent <= 0) {
        return;
      }
      vm.rebateFee = (totalPrice * vm.weshare.proxy_rebate_percent.percent / 100).toFixed(2);
    }

    function calOrderTotalPrice() {
      var submit_products = [];
      _.each(vm.weshare.products, function (product) {
        if (product.num && (product.num > 0)) {
          submit_products.push(product);
        }
      });
      var totalPrice = 0;
      _.each(submit_products, function (product) {
        totalPrice += product.price * product.num;
      });
      if (totalPrice != 0) {
        if (vm.favourableConfig) {
          //折扣
          if (vm.favourableConfig['discount']) {
            totalPrice = totalPrice * vm.favourableConfig['discount'];
          }
        }
        calProxyRebateFee(totalPrice / 100);
        if (vm.userCouponReduce) {
          totalPrice -= vm.userCouponReduce;
        }
        vm.shipFee = parseInt(getShipFee());
        vm.shipSetId = getShipSetId();
        totalPrice += vm.shipFee;
        vm.orderTotalPrice = totalPrice / 100;
        if (vm.rebateFee > 0) {
          vm.orderTotalPrice -= vm.rebateFee;
        }
      } else {
        vm.orderTotalPrice = 0;
        vm.rebateFee = 0;
      }
    }

    function getOrderComment(order_id) {
      if (vm.commentData['order_comments']) {
        if (vm.commentData['order_comments'][order_id]) {
          return vm.commentData['order_comments'][order_id];
        }
      }
      if (vm.shareOrder['orderComments']) {
        return vm.shareOrder['orderComments'][order_id];
      }
      return null;
    }

    function getOrderCommentLength() {
      if (vm.sharerAllComments) {
        if (vm.shareOrder['orderComments']) {
          return (vm.shareOrder['orderComments'].length || 0) + vm.sharerAllComments.length;
        }
        return vm.sharerAllComments.length;
      }
      return 0;
    }

    function getReplyComments(comment_id) {
      if (vm.commentData['comment_replies']) {
        if (vm.commentData['comment_replies'][comment_id]) {
          return vm.commentData['comment_replies'][comment_id];
        }
      }
      if (vm.shareOrder['orderCommentReplies']) {
        return vm.shareOrder['orderCommentReplies'][comment_id];
      }
    }

    function showReplies(comment_id) {
      if (!vm.commentData && !vm.shareOrder['orderCommentReplies']) {
        return false;
      }
      var selfAllReplies = vm.commentData['comment_replies'];
      if (selfAllReplies) {
        if(selfAllReplies[comment_id]){
          return true
        }
      }
      var referAllReplies = vm.shareOrder['orderCommentReplies'];
      if (referAllReplies) {
        if(referAllReplies[comment_id]){
          return true;
        }
      }
      return false;;
    }

    function getRecommendInfo(order) {
      var recommendId = 0;
      var recommend = '';
      if (vm.rebateLogs && vm.rebateLogs[order['cate_id']]) {
        recommendId = vm.rebateLogs[order['cate_id']];
        recommend = vm.ordersDetail['users'][recommendId]['nickname'];
      } else {
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
        recommend = vm.shareOrder['users'][recommendId]['nickname'];
      }
      return recommend + '推荐';
    }

    function isCurrentUserRecommend(order) {
      if (vm.isCreator()) {
        return true;
      }
      if (vm.currentUser && vm.currentUser['is_proxy'] == 0) {
        return false;
      }
      var recommendId = 0;
      if (vm.rebateLogs && vm.rebateLogs[order['cate_id']]) {
        recommendId = vm.rebateLogs[order['cate_id']];
      } else {
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
      }
      if (vm.currentUser && vm.currentUser['id'] == recommendId) {
        return true;
      }
      return false;
    }

    function toRecommendUserInfo(order) {
      var recommendId = 0;
      if (vm.rebateLogs[order['cate_id']]) {
        recommendId = vm.rebateLogs[order['cate_id']];
      } else {
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
      }
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
      if (vm.selectShipType == 3) {
        return vm.weshareSettings.pin_tuan.id;
      }
    }

    function setShipFee() {
      if (vm.selectShipType == 0) {
        vm.shipFee = getShipFee();
      }
      if (vm.selectShipType == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
      }
      if (vm.selectShipType == 2) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
      }
      if (vm.selectShipType == 3) {
        vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
      }
    }

    function getShipFee() {
      if (vm.selectShipType == 0) {
        //return vm.weshareSettings.kuai_di.ship_fee;
        var goodNum = 0;
        var goodWeight = 0;
        _.each(vm.weshare.products, function (product) {
          if (product.num && (product.num > 0)) {
            goodNum = goodNum + parseInt(product.num);
            if(product.weight && (product.weight > 0)){
              goodWeight = goodWeight + parseInt(product.weight)*parseInt(product.num);
            }
          }

        });
        return vm.calculateShipFee(vm.dliveryTemplate, vm.selectedProvince, goodNum, goodWeight);
      }
      if (vm.selectShipType == 1) {
        return vm.weshareSettings.self_ziti.ship_fee;
      }
      if (vm.selectShipType == 2) {
        return vm.weshareSettings.pys_ziti.ship_fee;
      }
      if (vm.selectShipType == 3) {
        return vm.weshareSettings.pin_tuan.ship_fee;
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
      return _.all(vm.weshare.products, function (product) {
        return !product.num || product.num <= 0;
      });
      return vm.productsHasError;
    }

    function validateLocation(){
      if(vm.selectShipType==0 && (!vm.selectedProvince || !vm.selectedCity)){
        vm.locationHasError = true;
      }else{
        vm.locationHasError = false;
      }
      return vm.locationHasError;
    }


    function validateUserAddress() {
      vm.userAddressHasError = _.isEmpty(vm.buyerAddress);
      return vm.userAddressHasError;
    }

    function getProductLeftNum(product) {
      if (vm.ordersDetail && vm.ordersDetail['summery'].details[product.id]) {
        var product_buy_num = parseInt(vm.ordersDetail['summery'].details[product.id]['num']);
        var store_num = product.store;
        return store_num - product_buy_num;
      }
      return product.store;
    }

    function checkProductNum(product) {
      var store_num = product.store;
      //sold out
      if (store_num == -1) {
        return false;
      }
      if (store_num == 0) {
        return true;
      }
      if (vm.ordersDetail && vm.ordersDetail['summery'].details[product.id]) {
        var product_buy_num = parseInt(vm.ordersDetail['summery'].details[product.id]['num']);
        return product_buy_num < store_num;
      }
      return true;
    }

    function buyProducts() {
      vm.showShareDetailView = false;
      vm.showBalanceView = true;
      vm.chooseShipType = false;
      // 默认显示的是快递标签页.. 那么, 没有快递选项怎么处理?
      //showKuaiDiTabPageFunc();

      //vm.showTabPage = true;
      //vm.showSelectKuaiDiAddressPage = false;
      //vm.showEditKuaiDiAddressPage = false;
      //vm.showSelectSelfZitiAddressPage = false;
      //vm.showEditSelfZitiAddressPage = false;
    }


    function startGroupShare() {
      if (vm.checkUserHasStartGroupShare(vm.currentUser.id)) {
        alert('您已经发起一次拼团了');
        return false;
      }
      vm.selectShipType = 3;
      vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
      vm.showGroupShareTipDialog = false;
      vm.showLayer = false;
      vm.showShareDetailView = false;
      vm.showStartGroupShareView = true;
      vm.chooseShipType = false;
      vm.startNewGroupShare = true;
    }

    function submitOrder(paymentType) {
      if (!vm.validateOrderData()) {
        return false;
      }
      var submit_products = [];
      _.each(vm.weshare.products, function (product) {
        if (product.num && (product.num > 0)) {
          submit_products.push(product);
        }
      });
      submit_products = _.map(submit_products, function (product) {
        return {id: product.id, num: product.num};
      });
      vm.shipFee = vm.shipFee || 0;
      var ship_info = {
        ship_type: vm.selectShipType,
        ship_fee: vm.shipFee,
        ship_set_id: vm.shipSetId
      };
      //self ziti
      if (vm.selectShipType == 1) {
        ship_info['address_id'] = vm.weshare.selectedAddressId;
      }
      //offline store
      if (vm.selectShipType == 2) {
        ship_info['address_id'] = vm.checkedOfflineStore.id;
      }
      //邻里拼团
      if (vm.selectShipType == 3 && !vm.startNewGroupShare) {
        if (!vm.chooseOfflineAddress) {
          vm.offlineAddressHasError = true;
          return;
        }
        vm.buyerAddress = vm.childShareDetail[vm.chooseOfflineAddress]['address'];
        ship_info['weshare_id'] = vm.chooseOfflineAddress;
      }
      var orderData = {
        weshare_id: vm.weshare.id,
        rebate_log_id: vm.rebateLogId,
        products: submit_products,
        ship_info: ship_info,
        remark: vm.buyerRemark,
        is_group_share: vm.isGroupShareType,
        start_new_group_share: vm.startNewGroupShare,
        buyer: {
          name: vm.buyerName,
          mobilephone: vm.buyerMobilePhone,
          address: vm.buyerAddress,
          patchAddress: vm.buyerPatchAddress,
          provinceId: vm.selectedProvince,
          cityId: vm.selectedCity,
          countyId: vm.selectedCounty
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

    //发起拼团
    function newGroupShare() {
      if (vm.checkUserHasStartGroupShare(vm.currentUser.id)) {
        alert('您已经发起一次拼团了');
        return false;
      }
      //valid address
      if (vm.validateUserAddress()) {
        return false;
      }
      var cloneShareData = {
        weshare_id: vm.weshare.id,
        address: vm.buyerAddress,
        business_remark: vm.buyerRemark
      };
      $http.post('/weshares/start_new_group_share', cloneShareData).success(function (data) {
        if (data.success) {
          window.location.href = '/weshares/view/' + data.shareId;
        } else {
          alert('提交失败.请联系客服..');
        }
      }).error(function () {
      });
    }

    //重新开团
    function cloneShare() {
      if (vm.cloneShareProcessing) {
        return;
      }

      vm.cloneShareProcessing = true;
      $http.post('/weshares/cloneShare/' + vm.weshare.id).success(function (data) {
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
      vm.loadOrderCommentData(vm.weshare.id);
    }

    function notifyUserToComment(order) {
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = 0;
      vm.submitTempCommentData.share_id = order.member_id;
      vm.submitComment();
      order.notify = true;
    }

    function submitComment() {
      $http.post('/weshares/comment', vm.submitTempCommentData).success(function (data) {
        if (data.success) {
          if (data.type == 'notify') {
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

    function resetNotifyContent() {
      if (vm.sendNotifyType == 0) {
        vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
      } else {
        vm.notify.content = '';
      }
    }

    function defaultNotifyHasBuyMsgContent() {
      var msgContent = '';
      if (vm.totalBuyCount > 10) {
        var index = 0;
        for (var userId in vm.shareOrder['users']) {
          var user = vm.shareOrder['users'][userId];
          index++;
          if (index > 10) {
            break;
          }
          if (index == 10) {
            msgContent = msgContent + user['nickname'];
          } else {
            msgContent = msgContent + user['nickname'] + '，';
          }
        }
        msgContent = msgContent + '...等' + vm.totalBuyCount + '人都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
      } else {
        msgContent = _.reduce(vm.shareOrder.users, function (memo, user) {
          return memo + user['nickname'] + '，';
        }, '');
        msgContent = msgContent + '都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
      }
      return msgContent;
    }

    function notifyFans() {
      vm.showNotifyView = true;
      vm.showLayer = true;
      vm.sendNotifyType = 0;
      vm.notify = {};
      vm.notify.type = vm.sendNotifyType;
      vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
    }

    function notifyType() {
      if (vm.ordersDetail) {
        if (vm.ordersDetail.orders && vm.ordersDetail.orders.length > 0) {
          return 1;
        }
      }
      if (vm.shareOrder) {
        if (vm.shareOrder.orders && vm.shareOrder.orders.length > 0) {
          return 1;
        }
      }
      return 0;
    }

    function sendNewShareMsg() {
      if (confirm('是否要发送消息，发送次数过多会对用户形成骚扰?')) {
        $http({
          method: 'GET',
          url: '/weshares/send_new_share_msg/' + vm.weshare.id
        }).success(function (data) {
          // With the data succesfully returned, call our callback
          if (data['success']) {
            var msg = '发送成功';
            if (data['msg']) {
              msg = data['msg'];
            }
            alert(msg);
          } else {
            if (data['reason'] == 'user_bad') {
              alert('发送失败，你已经被封号，请联系管理员..');
            }
            if (data['msg']) {
              alert(data['msg']);
            }
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function unSubSharer() {
      if (vm.hasProcessSubSharer) {
        return;
      }
      $http({
        method: 'GET',
        url: '/weshares/unsubscribe_sharer/' + vm.weshare.creator.id + '/' + vm.currentUser.id
      }).success(function (data) {
        vm.hasProcessSubSharer = false;
        if (data['success']) {
          vm.userSubStatus = !vm.userSubStatus;
        }
      }).error(function (data) {
        vm.hasProcessSubSharer = true;
      });
    }

    function subSharer() {
      if (vm.hasProcessSubSharer) {
        return;
      }
      $http({
        method: 'GET',
        url: '/weshares/subscribe_sharer/' + vm.weshare.creator.id + '/' + vm.currentUser.id + '/1/' + vm.weshare.id + '.json'
      }).success(function (data) {
        // With the data succesfully returned, call our callback
        vm.hasProcessSubSharer = false;
        if (data['success']) {
          //vm.subShareTipTxt = '已关注';
          vm.userSubStatus = !vm.userSubStatus;
        } else {
          alert('请先关注朋友说微信公众号！');
          window.location.href = "https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=403992659&idx=1&sn=714a1a5f0bb4940f895e60f2f3995544";
        }
      }).error(function () {
        vm.hasProcessSubSharer = false;
      });
    }

    function validNotifyMsgContent() {
      if (_.isEmpty(vm.notify.content)) {
        vm.notifyMsgHasError = true;
        return;
      }
      vm.notifyMsgHasError = false;
    }

    function sendNotifyShareMsg() {
      if (_.isEmpty(vm.notify.content)) {
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
          if (data['msg']) {
            alert(data['msg']);
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function showCommentListDialog() {
      vm.loadSharerAllComments(vm.weshare.creator.id);
      vm.showCommentListDialogView = true;
      vm.showLayer = true;
    }

    function closeCommentDialog() {
      vm.showCommentingDialog = false;
      vm.showLayer = false;
      vm.submitTempCommentData = {};
    }

    function showAutoCommentDialog() {
      var order = vm.autoPopCommentData['comment_order_info'];
      if (order) {
        vm.showCommentingDialog = true;
        vm.showLayer = true;
        var reply_comment_id = 0;
        var comment_tip_info = '';
        var comment = vm.autoPopCommentData['comment_info'];
        if (comment) {
          reply_comment_id = comment.id || 0;
          var reply_username = comment.username;
          if (reply_username == vm.currentUser.nickname) {
            comment_tip_info = '爱心评价';
          } else {
            comment_tip_info = '回复' + reply_username + '：';
          }
        } else {
          //check is creator
          if (vm.currentUser.id == vm.weshare.creator.id) {
            var order_username = order['creator_nickname'];
            comment_tip_info = '回复' + order_username + '说：';
          } else {
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
    }

    //open comment dialog
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
        if (vm.currentUser.id == vm.weshare.creator.id) {
          var order_username;
          if (vm.ordersDetail.users[order.creator]) {
            order_username = vm.ordersDetail.users[order.creator]['nickname'];
          } else {
            order_username = vm.shareOrder.users[order.creator]['nickname'];
          }
          comment_tip_info = '回复' + order_username + '说：';
        } else {
          comment_tip_info = '回复' + vm.weshare.creator.nickname + '说：';
        }
      }
      vm.submitTempCommentData = {};
      vm.commentTipInfo = comment_tip_info;
      vm.commentOrder = order;
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = reply_comment_id;
      vm.submitTempCommentData.share_id = order.member_id;
    }

    function getProcessPrepaidStatus(status) {
      if (status == 25) {
        return '金额待定';
      }
      if (status == 26) {
        return '待补款';
      }
      if (status == 27) {
        return '已补款';
      }
      if (status == 28) {
        return '待退差价';
      }
      if (status == 29) {
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
      if(vm.validateLocation()){
        return false;
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
        window.location.href = '/weshares/add?from=share_view';
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
        var ship_type_name = order['ship_type_name'];
        if (!ship_type_name) {
          var ship_company = order['ship_type'];
          ship_type_name = vm.shipTypes[ship_company]
        }
        return ship_type_name + ': ' + code;
      }
      if (shipMark == 'pys_ziti') {
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
        if (data['msg']) {
          alert(data['msg']);
        }
      }).error(function (e) {
        $log.log(e);
      });
      vm.closeRecommendDialog();
    }

    function validRecommendContent() {
      vm.recommendContentHasError = false;
      if (_.isEmpty(vm.submitRecommendData.recommend_content)) {
        vm.recommendContentHasError = true;
      }
      return vm.recommendContentHasError;
    }

    function checkHasUnRead() {
      $http.get('/share_opt/check_opt_has_new.json').success(function (data) {
        if (data['has_new']) {
          vm.showUnReadMark = true;
        }
      });
    }

    function isShowShipCode(order) {
      if (order['ship_mark'] == 'kuai_di' || order['ship_mark'] == 'pys_ziti') {
        if (order.status != 1 && vm.isOwner(order)) {
          var code = order['ship_code'];
          if (code) {
            return true;
          }
        }
      }
      return false;
    }

    function isShowCancelLogisticsBtn(order) {
      if (vm.isShowCallLogisticsBtn(order)) {
        var orderId = order['id'];
        var logisticsOrder = vm.logisticsOrderData[orderId];
        if (logisticsOrder && logisticsOrder['status'] == 1) {
          return true;
        }
      }
      return false;
    }

    function isShowCallLogisticsBtn(order) {
      //草莓可以呼叫闪送
      if (vm.weshare.id == 1305 || vm.weshare.id == 1585) {
        if (order['ship_mark'] == 'pys_ziti' && order['status'] == 2 && vm.isOwner(order)) {
          return true;
        }
      }
      return false;
    }

    function isShowExpressInfoBtn(order) {
      if (order['ship_mark'] == 'kuai_di') {
        if (order.status > 1 && vm.isOwner(order)) {
          return true;
        }
      }
      return false;
    }

    function handleCancelLogistics(order) {
      var orderId = order['id'];
      if (vm.logisticsOrderData[orderId]) {
        var logisticsOrder = vm.logisticsOrderData[orderId];
        if (logisticsOrder['status'] == 1 && logisticsOrder['business_order_id']) {
          var logisticsOrderId = logisticsOrder['id'];
          $http.get('/logistics/cancel_rr_logistics_order/' + logisticsOrderId).success(function (data) {
            if (data['status'] == 1) {
              vm.logisticsOrderData[orderId]['status'] = 4;
              vm.logisticsOrderData[orderId]['business_order_id'] = data['orderNo'];
            } else {
              alert(data['msg']);
            }
          }).error(function () {
            alert('呼叫失败，请联系客服。');
          });
        } else {
          alert("快递已接单，不能取消。");
        }
        return;
      }
    }

    function handleCallLogistics(order) {
      var orderId = order['id'];
      //订单已经叫过快递
      if (vm.logisticsOrderData[orderId]) {
        var logisticsOrder = vm.logisticsOrderData[orderId];
        if (logisticsOrder['status'] == 5 || (logisticsOrder['status'] == 1 && !logisticsOrder['business_order_id'])) {
          var logisticsOrderId = logisticsOrder['id'];
          $http.get('/logistics/re_confirm_rr_logistics_order/' + logisticsOrderId).success(function (data) {
            if (data['status'] == 1) {
              vm.logisticsOrderData[orderId]['status'] = 1;
              vm.logisticsOrderData[orderId]['business_order_id'] = data['orderNo'];
            } else {
              alert(data['msg']);
            }
          }).error(function () {
            alert('呼叫失败，请联系客服。');
          });
        } else {
          if (logisticsOrder['status'] == 1) {
            alert('等待快递员接单');
          }
          if (logisticsOrder['status'] == 2) {
            alert('快递已接单，请耐心等候');
          }
        }
        return;
      }
      window.location.href = '/logistics/rr_logistics/' + orderId;
    }

    function getLogisticsStatusText(order) {
      var orderId = order['id'];
      if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
        var logisticsOrder = vm.logisticsOrderData[orderId];
        if (logisticsOrder['status'] == 5) {
          return '快递已经取消(超时或者自己取消)';
        }
      }
      return '';
    }

    function showLogisticsStatusInfo(order) {
      //草莓可以呼叫闪送
      if (vm.isShowCallLogisticsBtn(order)) {
        var orderId = order['id'];
        if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
          var logisticsOrder = vm.logisticsOrderData[orderId];
          if (logisticsOrder['status'] == 5) {
            return true;
          }
        }
      }
      return false;
    }

    function getLogisticsBtnText(order) {
      var orderId = order['id'];
      //订单已经叫过快递
      if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
        var logisticsOrder = vm.logisticsOrderData[orderId];
        if (logisticsOrder['status'] == 1) {
          if (logisticsOrder['business_order_id']) {
            return '待接单';
          }
          return '重新叫快递';
        }
        if (logisticsOrder['status'] == 2) {
          return '已接单';
        }
        if (logisticsOrder['status'] == 3) {
          return '已取货';
        }
        if (logisticsOrder['status'] == 4) {
          return '已签收';
        }
        if (logisticsOrder['status'] == 5) {
          return '重新叫快递';
        }
      }
      return '叫快递';
    }

    function showOrderExpressInfo(order) {
      window.location.href = '/weshares/express_info/' + order.id;
      return;
    }

    function calculateShipFee(deliveryTemplate, provinceId, goodNum, goodWeight) {
      var shipFee = 0;
      if (provinceId && goodNum > 0) {
        var template = null;
        if (deliveryTemplate['delivery_templates'] && deliveryTemplate['delivery_templates'].length > 0) {
          for (var i = 0; i < deliveryTemplate['delivery_templates'].length; i++) {
            var deliveryTemplateItem = deliveryTemplate['delivery_templates'][i];
            var regions = deliveryTemplateItem['regions'];
            if (regions && regions.length > 0) {
              for (var j = 0; j < regions.length; j++) {
                var region = regions[j];
                if (region['province_id'] == provinceId) {
                  template = deliveryTemplateItem;
                }
                break;
              }
              if (template) {
                break;
              }
            }
          }
        }
        if (!template) {
          template = deliveryTemplate['default_delivery_template'];
        }
        var startUnits = parseInt(template['start_units']);
        var startFee = parseInt(template['start_fee']);
        var addUnits = parseInt(template['add_units']);
        var addFee = parseInt(template['add_fee']);
        var unitType = parseInt(template['unit_type']);
        var cal_val = goodNum;
        if(unitType == 1){
          cal_val = goodWeight;
        }
        var gapVal = cal_val - startUnits;
        if (gapVal <= 0) {
          shipFee = startFee;
        } else {
          shipFee = (startFee + (Math.ceil(gapVal / addUnits) * addFee));
        }
      }
      return shipFee;
    }

    function supportGroupBuy() {
      if (vm.weshareSettings && vm.weshareSettings.pin_tuan && vm.weshareSettings.pin_tuan.status == 1) {
        return true;
      }
      return false;
    }

    function toggleTag(tag) {
      var currentToggleState = vm.toggleState[tag];
      currentToggleState['open'] = !currentToggleState['open'];
      currentToggleState['statusText'] = currentToggleState['open'] ? '收起' : '展开';
    }

    function initProvince() {
      vm.provinceData = [{"id":"110100","name":"\u5317\u4eac","parent_id":"2"},{"id":"120100","name":"\u5929\u6d25","parent_id":"2"},{"id":"130000","name":"\u6cb3\u5317","parent_id":"2"},{"id":"140000","name":"\u5c71\u897f","parent_id":"2"},{"id":"150000","name":"\u5185\u8499\u53e4","parent_id":"2"},{"id":"210000","name":"\u8fbd\u5b81","parent_id":"5"},{"id":"220000","name":"\u5409\u6797","parent_id":"5"},{"id":"230000","name":"\u9ed1\u9f99\u6c5f","parent_id":"5"},{"id":"310100","name":"\u4e0a\u6d77","parent_id":"1"},{"id":"320000","name":"\u6c5f\u82cf","parent_id":"1"},{"id":"330000","name":"\u6d59\u6c5f","parent_id":"1"},{"id":"340000","name":"\u5b89\u5fbd","parent_id":"1"},{"id":"350000","name":"\u798f\u5efa","parent_id":"4"},{"id":"360000","name":"\u6c5f\u897f","parent_id":"1"},{"id":"370000","name":"\u5c71\u4e1c","parent_id":"2"},{"id":"410000","name":"\u6cb3\u5357","parent_id":"3"},{"id":"420000","name":"\u6e56\u5317","parent_id":"3"},{"id":"430000","name":"\u6e56\u5357","parent_id":"3"},{"id":"440000","name":"\u5e7f\u4e1c","parent_id":"4"},{"id":"450000","name":"\u5e7f\u897f","parent_id":"4"},{"id":"460000","name":"\u6d77\u5357","parent_id":"4"},{"id":"500100","name":"\u91cd\u5e86","parent_id":"7"},{"id":"510000","name":"\u56db\u5ddd","parent_id":"7"},{"id":"520000","name":"\u8d35\u5dde","parent_id":"7"},{"id":"530000","name":"\u4e91\u5357","parent_id":"7"},{"id":"540000","name":"\u897f\u85cf","parent_id":"7"},{"id":"610000","name":"\u9655\u897f","parent_id":"6"},{"id":"620000","name":"\u7518\u8083","parent_id":"6"},{"id":"630000","name":"\u9752\u6d77","parent_id":"6"},{"id":"640000","name":"\u5b81\u590f","parent_id":"6"},{"id":"650000","name":"\u65b0\u7586","parent_id":"6"},{"id":"710000","name":"\u53f0\u6e7e","parent_id":"8"},{"id":"810000","name":"\u9999\u6e2f","parent_id":"8"},{"id":"820000","name":"\u6fb3\u95e8","parent_id":"8"}];
    }

    function loadCityData(provinceId) {
      vm.calOrderTotalPrice();
      $http({method: 'GET', url: '/locations/get_city.json?provinceId=' + provinceId, cache: $templateCache}).
        success(function (data, status) {
          vm.cityData = data;
        }).
        error(function (data, status) {
        });
    }

    function loadCounty(cityId) {
      $http({method: 'GET', url: '/locations/get_county.json?cityId=' + cityId, cache: $templateCache}).
        success(function (data, status) {
          vm.countyData = data;
        }).
        error(function (data, status) {
        });
    }


    //设置微信分享的参数
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
        if (vm.totalBuyCount >= 5) {
          desc += '已经有' + vm.totalBuyCount + '人报名，';
        }
        desc += vm.weshare.description.substr(0, 20);
      } else if (userInfo) {
        if (vm.isProxy()) {
          url = url + '?recommend=' + vm.currentUser['id'];
        }
        if (!vm.isProxy() && vm.recommendUserId != 0) {
          url = url + '?recommend=' + vm.recommendUserId;
        }
        to_timeline_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || userInfo.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
      } else if (vm.currentUser) {
        //default custom
        if (vm.weshare.type !== 4) {
          if (vm.isProxy()) {
            url = url + '?recommend=' + vm.currentUser['id'];
          }
          if (!vm.isProxy() && vm.recommendUserId != 0) {
            url = url + '?recommend=' + vm.recommendUserId;
          }
        }
        to_timeline_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.currentUser.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
      } else {
        to_timeline_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        to_friend_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
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
        if (!vm.isProxy() && vm.recommendUserId != 0) {
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
