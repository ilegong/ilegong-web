(function (window, angular) {

    angular.module('weshares')
        .controller('WesharesViewCtrl', WesharesViewCtrl);

    function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, Utils, ShareOrder, OfflineStore) {
        var vm = this;
        vm.staticFilePath = Utils.staticFilePath();
        vm.showShareDetailView = true;
        vm.faqTipText = '私信';
        vm.showUnReadMark = false;
        vm.readMoreBtnText = '全文';
        vm.hideMoreShareInfo = false;
        vm.shouldShowReadMoreBtn = false;
        vm.shipTypes = Utils.shipTypes();
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
        vm.validateProducts = validateProducts;
        vm.buyProducts = buyProducts;
        vm.validateMobile = validateMobile;
        vm.validateUserName = validateUserName;
        vm.validateShipInfo = validateShipInfo;
        vm.validateOrderData = validateOrderData;
        vm.submitOrder = submitOrder;
        //vm.useCoupons = useCoupons;
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
        vm.showCommentListDialog = showCommentListDialog;
        vm.getOrderCommentLength = getOrderCommentLength;
        vm.initWeshareData = initWeshareData;
        vm.sortOrders = sortOrders;
        vm.closeCommentDialog = closeCommentDialog;
        vm.notifyUserToComment = notifyUserToComment;
        vm.loadOrderDetail = loadOrderDetail;
        vm.getFormatDate = getFormatDate;
        vm.notifyFans = notifyFans;
        vm.notifyType = notifyType;
        vm.sendNotifyShareMsg = sendNotifyShareMsg;
        vm.validNotifyMsgContent = validNotifyMsgContent;
        vm.cloneShare = cloneShare;
        vm.resetNotifyContent = resetNotifyContent;
        vm.defaultNotifyHasBuyMsgContent = defaultNotifyHasBuyMsgContent;
        vm.closeRecommendDialog = closeRecommendDialog;
        vm.submitRecommend = submitRecommend;
        vm.validRecommendContent = validRecommendContent;
        vm.checkHasUnRead = checkHasUnRead;
        vm.setShipFee = setShipFee;
        vm.redirectFaq = redirectFaq;
        vm.chatToUser = chatToUser;
        vm.isShareManager = isShareManager;
        vm.calculateShipFee = calculateShipFee;
        vm.updateBuyerData = updateBuyerData;
        vm.getShareSummeryData = getShareSummeryData;
        vm.filterProductByNum = filterProductByNum;
        vm.isShowExpressInfoBtn = isShowExpressInfoBtn;
        vm.showOrderExpressInfo = showOrderExpressInfo;
        vm.getBannerImage = getBannerImage;
        vm.getBannerLink = getBannerLink;
        vm.getRecommendWeshares = getRecommendWeshares;
        activate();

        OfflineStore.ChooseOfflineStore(vm, $http, $templateCache);

        function activate() {
            vm.currentUserOrderCount = 0;
            vm.orderPayTotalPrice = 0;
            vm.productTotalPrice = 0;
            vm.commentData = {};
            vm.orderComments = [];
            vm.inWeixin = Utils.isWeixin();
            vm.recommendWeshares = [];
            $rootScope.uid=-1;
            $rootScope.proxies = [];
            vm.initWeshareData();
        }

        function filterProductByNum(product) {
            return product.num > 0;
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

        function chatToUser(userId) {
            if (userId == vm.currentUser.id) {
                window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + userId;
                return;
            }
            window.location.href = '/share_faq/faq/' + vm.weshare.id + '/' + userId;
        }

        function loadOrderDetail(share_id) {
            var viewFrom = angular.element(document.getElementById('weshareView')).attr('data-from');
            vm.viewFrom = viewFrom;
            //first share
            var initSharedOfferId = angular.element(document.getElementById('weshareView')).attr('data-shared-offer');
            var followSharedType = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-type');
            var followSharedNum = parseFloat(angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-coupon-num'));
            vm.sharedOfferId = initSharedOfferId;
            //show get packet num
            if (followSharedType) {
                if (followSharedType == 'got' && followSharedNum > 0) {
                    vm.showNotifyGetPacketDialog = true;
                    vm.getPacketNum = followSharedNum + '元';
                    vm.showLayer = true;
                }
            }
            $http({
                method: 'GET',
                url: '/weshares/get_share_user_order/' + share_id + '.json',
                cache: $templateCache
            }).success(function (data, status) {
                vm.ordersDetail = data['ordersDetail'];
                if(!_.isEmpty(data['ordersDetail']['order_ids'])){
                  loadOrderCommentData(share_id);
                }
                //check user is auto comment
                if (vm.autoPopCommentData['comment_order_info']) {
                    vm.showAutoCommentDialog();
                }
                //process page order info
                vm.shareOrder = new ShareOrder();
                vm.shareOrder.shareId = share_id;
            }).error(function (data, status) {
                $log.log(data);
            });
        }

        function getShareSummeryData(shareId, userId) {
            $http.get('/weshares/summaries', {params: {shareIds: JSON.stringify([shareId])}}).success(function (summaries) {
                vm.weshare.summary = summaries[0];
            }).error(function (data, e) {
                $log.log('Failed to get summaries: ' + e);
            });
        }

        function initWeshareData() {
          var element = angular.element(document.getElementById('weshareView'));
            var rebateLogId = element.attr('data-rebate-log-id');
            var recommendUserId = element.attr('data-recommend-id');
            //auto poup comment dialog
            var commentOrderId = element.attr('data-comment-order-id');
            var replayCommentId = element.attr('data-replay-comment-id');
            vm.rebateLogId = rebateLogId || 0;
            vm.recommendUserId = recommendUserId || 0;
            var weshareId = element.attr('data-weshare-id');
            $rootScope.uid = element.attr('data-uid');
            vm.weshare = {};
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
            }).success(function (data, status) {
                handleShareData(data);
            }).error(function (data, status) {
                $log.log(data);
                $rootScope.loadingPage = false;
            });
            vm.checkHasUnRead();
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
            //|| vm.canManageShare
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
            vm.showEditConsigneeView = false;
            vm.showShareDetailView = true;
        }

        function toShareDetailView($share_id) {
            window.location.href = '/weshares/view/' + $share_id;
        }

        function toUserShareInfo($uid) {
            window.location.href = '/weshares/user_share_info/' + $uid;
        }

        function viewImage(url) {
            if(wx){
                wx.previewImage({
                    current: url,
                    urls: vm.weshare.images
                });
            }
        }

        function getConsigneeInfo(order) {
            if (_.isEmpty(order)) {
                return '';
            }
            return _.reject([order.consignee_name, order.consignee_mobilephone], function (e) {
                return _.isEmpty(e)
            }).join(',');
        }

        function calCanUserRebate(totalPrice){
            if (vm.rebateTotal == 0) {
                vm.canUseRebateTotal = 0;
            } else {
                totalPrice = totalPrice / 100;
                var rebateMoney = vm.rebateTotal / 100;
                if (rebateMoney > totalPrice / 2) {
                    rebateMoney = totalPrice / 2;
                }
                vm.canUseRebateTotal = parseInt((rebateMoney * 100) + '');
            }
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
                vm.productTotalPrice = totalPrice / 100;
                if (vm.userCouponReduce) {
                    totalPrice -= vm.userCouponReduce;
                }
                vm.shipFee = parseInt(getShipFee());
                vm.shipSetId = getShipSetId();
                calCanUserRebate(totalPrice);
                if (vm.useRebate == '1') {
                    totalPrice -= vm.canUseRebateTotal;
                }
                totalPrice += vm.shipFee;
                vm.orderPayTotalPrice = totalPrice / 100;
            } else {
                vm.productTotalPrice = 0;
                vm.orderPayTotalPrice = 0;
                vm.shipFee = 0;
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
            var orderCommentCount = 0;
            if (vm.shareOrder && vm.shareOrder['orderComments']) {
                orderCommentCount = orderCommentCount + Object.keys(vm.shareOrder['orderComments']).length;
            }
            return orderCommentCount;
        }

        function getReplyComments(comment_id) {
            if (vm.commentData['comment_replies']) {
                if (vm.commentData['comment_replies'][comment_id]) {
                    return vm.commentData['comment_replies'][comment_id];
                }
            }
            if (vm.shareOrder && vm.shareOrder['orderCommentReplies']) {
                return vm.shareOrder['orderCommentReplies'][comment_id];
            }
        }

        function showReplies(comment_id) {
            if (!vm.commentData && !vm.shareOrder['orderCommentReplies']) {
                return false;
            }
            var selfAllReplies = vm.commentData['comment_replies'];
            if (selfAllReplies) {
                if (selfAllReplies[comment_id]) {
                    return true
                }
            }
            var referAllReplies = vm.shareOrder['orderCommentReplies'];
            if (referAllReplies) {
                if (referAllReplies[comment_id]) {
                    return true;
                }
            }
            return false;
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

        function setShipFee() {
            vm.shipFee = getShipFee();
        }

        function getShipFee() {
            if (vm.selectShipType == 0) {
                //return vm.weshareSettings.kuai_di.ship_fee;
                var goodNum = 0;
                var goodWeight = 0;
                _.each(vm.weshare.products, function (product) {
                    if (product.num && (product.num > 0)) {
                        goodNum = goodNum + parseInt(product.num);
                        if (product.weight && (product.weight > 0)) {
                            goodWeight = goodWeight + parseInt(product.weight) * parseInt(product.num);
                        }
                    }
                });
                if (vm.expressShipInfo) {
                    return vm.calculateShipFee(vm.dliveryTemplate, vm.expressShipInfo['province_id'], goodNum, goodWeight);
                }
                return 0;
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
                if (product.store > 0) {
                    product.num = Math.min(product.num + 1, vm.getProductLeftNum(product));
                } else {
                    product.num = product.num + 1;
                }
            }
            if (vm.showBalanceView) {
                calOrderTotalPrice();
            }
            //vm.validateProducts();
        }

        function decreaseProductNum(product) {
            if (!Utils.isNumber(product.num)) {
                product.num = 0;
            }

            var minNum = 0;
            if (vm.showBalanceView) {
                var totalNum = _.reduce(_.filter(vm.weshare.products, function (p) {
                    return vm.filterProductByNum(p);
                }), function (memo, p) {
                    return memo + p.num
                }, 0);

                if (totalNum <= 1) {
                    minNum = 1;
                }
            }
            product.num = Math.max(minNum, product.num - 1);

            if (vm.showBalanceView) {
                calOrderTotalPrice();
            }
            //vm.validateProducts();
        }

        function validateMobile() {
            vm.buyerMobilePhoneHasError = !Utils.isMobileValid(vm.buyerMobilePhone);
            return vm.buyerMobilePhoneHasError;
        }

        function validateUserName() {
            vm.usernameHasError = _.isEmpty(vm.buyerName);
            return vm.usernameHasError;
        }

        function validateShipInfo() {
            if (vm.selectShipType == 0 && !vm.expressShipInfo) {
                alert('快递地址选择有误');
                return false;
            }
            if (vm.selectShipType == 1 && vm.selectedPickUpAddressId == -1) {
                alert('请选择自提地址');
                return false;
            }
            if (vm.selectShipType == 2 && !vm.checkedOfflineStore) {
                alert('请选择好邻居线下店');
                return false;
            }
            if (_.isEmpty(vm.buyerAddress)) {
                alert('请填写地址信息');
                return false;
            }
            if ((vm.selectShipType == 1 || vm.selectShipType == 2) && _.isEmpty(vm.buyerPatchAddress)) {
                alert('请填写详细地址信息');
                return false;
            }
            return true;
        }

        function validateProducts() {
            return _.all(vm.weshare.products, function (product) {
                return !product.num || product.num <= 0;
            });
        }

        function getProductLeftNum(product) {
            if (vm.productSummery && vm.productSummery.details[product.id]) {
                var product_buy_num = parseInt(vm.productSummery.details[product.id]['num']);
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
            if (vm.productSummery && vm.productSummery.details[product.id] && store_num > 0) {
                var product_buy_num = parseInt(vm.productSummery.details[product.id]['num']);
                return product_buy_num < store_num;
            }
            return true;
        }

        function buyProducts() {
            if (vm.validateProducts()) {
                alert('请选择报名商品！');
                var element = angular.element(document.getElementById('share-product-list'));
                window.scrollTo(0, element[0].offsetTop - 100)
                return false;
            }
            vm.showShareDetailView = false;
            vm.chooseShipType = false;
            vm.showBalanceView = true;
            vm.shouldInitUserConsigneeData = true;
            vm.showEditConsigneeView = true;
            vm.reloadConsigneeData = true;
        }

        function getBuyShipInfo() {
            vm.shipFee = vm.shipFee || 0;
            //快递信息
            var ship_info = {
                ship_type: vm.selectShipType,
                ship_fee: vm.shipFee,
                ship_set_id: vm.shipSetId,
                name: vm.buyerName,
                mobilephone: vm.buyerMobilePhone,
                address: vm.buyerAddress,
                patchAddress: vm.buyerPatchAddress
            };
            //快递
            if (vm.selectShipType == 0) {
                ship_info['consignee_id'] = vm.expressShipInfo['id'];
                ship_info['provinceId'] = vm.expressShipInfo['province_id'];
            }
            //自提
            if (vm.selectShipType == 1 || vm.selectShipType == 2) {
                //自有自提
                if (vm.selectShipType == 1) {
                    ship_info['consignee_id'] = vm.selectedPickUpAddressId;
                }
                //好邻居
                if (vm.selectShipType == 2) {
                    ship_info['consignee_id'] = vm.checkedOfflineStore.id;
                }
            }
            return ship_info;
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
            var ship_info = getBuyShipInfo();
            var orderData = {
                weshare_id: vm.weshare.id,
                from: vm.viewFrom,
                weshare_creator: vm.weshare.creator.id,
                rebate_log_id: vm.rebateLogId,
                products: submit_products,
                ship_info: ship_info,
                remark: vm.buyerRemark,
                useRebate: vm.useRebate
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

        //function useCoupons() {
        //    if(vm.useCouponCode == null || vm.myCoupons || (vm.weshare.id!=4507))
        //    {
        //        return false;
        //    }
        //    $http.post('/weshares/get_useful_coupons', {'sharer':vm.weshare.creator.id,'couponCode': vm.useCouponCode}).success(function (data) {
        //        if (data.ok == 0) {
        //            vm.userCouponReduce = data.num;
        //            vm.myCoupons = true;
        //            vm.orderPayTotalPrice -= data.num/100;
        //            vm.useCouponId = data.useCouponId;
        //            return;
        //            //pay
        //        } else {
        //            if (data['msg']) {
        //                alert(data['msg']);
        //            } else {
        //                alert('提交失败.请联系客服..');
        //            }
        //        }
        //    }).error(function () {
        //        vm.submitProcessing = false;
        //    });
        //    console.log(vm.coupon_code);
        //}


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
                        window.alert('已经通知TA');
                        return true;
                    }
                    var order_id = data['order_id'];
                    if (vm.commentOrder.id == order_id && vm.commentOrder.status == 3) {
                        vm.commentOrder.status = 9;
                    }
                  loadOrderCommentData(vm.weshare.id);
                } else {
                    alert('提交失败');
                }
            }).error(function () {
                alert('提交失败');
            });
            vm.closeCommentDialog();
        }

      function loadOrderCommentData(share_id) {
        $http({method: 'GET', url: '/weshares/get_share_comment_data/' + share_id + '/'+vm.weshare.creator.id+'.json'}).
          success(function (data, status) {
            vm.commentData = data['comment_data'];
            vm.orderComments = vm.commentData['order_comments'];
          }).
          error(function (data, status) {
            $log.log(data);
          });
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
            if(vm.notifyType() == 0){
                msgContent = '我发起了一个分享，时间有限，抓紧报名啦！';
                return msgContent;
            }else{
                if (vm.weshare.summary.order_count > 10) {
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
                    msgContent = msgContent + '...等' + vm.weshare.summary.order_count + '人都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
                } else {
                    msgContent = _.reduce(vm.shareOrder.users, function (memo, user) {
                        return memo + user['nickname'] + '，';
                    }, '');
                    msgContent = msgContent + '都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
                }
                return msgContent;
            }
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
            if (vm.shareOrder) {
                if (vm.shareOrder.orders && vm.shareOrder.orders.length > 0) {
                    return 1;
                }
            }
            return 0;
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
            if (vm.currentUser.id != order.creator && !vm.isCreator()) {
                return false;
            }
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

        //更新购买用户信息
        function updateBuyerData(type) {
            cleanBuyerData();
            //快递
            if (type == 0 && vm.expressShipInfo) {
                setBuyerData(vm.expressShipInfo);
            }
            //初始化自提信息
            if (type == 1 && vm.pickUpShipInfo) {
                setBuyerData(vm.pickUpShipInfo);
            }
            //初始化好邻居信息
            if (type == 2 && vm.offlineStoreShipInfo) {
                setBuyerData(vm.offlineStoreShipInfo);
            }
            // 更新运费
            calOrderTotalPrice();
            function setBuyerData(data) {
                vm.buyerName = data['name'];
                vm.buyerMobilePhone = data['mobilephone'];
                vm.buyerPatchAddress = data['remark_address'];
                vm.buyerAddress = data['address'];
            }

            function cleanBuyerData() {
                vm.buyerName = '';
                vm.buyerMobilePhone = '';
                vm.buyerPatchAddress = '';
                vm.buyerAddress = '';
            }
        }

        function validateOrderData() {
            function setUserAddress() {
                if (vm.selectShipType == 0 && vm.expressShipInfo) {
                    vm.buyerAddress = vm.expressShipInfo['address'];
                }
                if (vm.selectShipType == 1 && vm.selectedPickUpAddressId != -1) {
                    var selectAddress = _.filter(vm.weshare.addresses, function (item) {
                        return item.id = vm.selectedPickUpAddressId;
                    });
                    vm.buyerAddress = selectAddress[0]['address'];
                }
                if (vm.selectShipType == 2 && vm.checkedOfflineStore) {
                    vm.buyerAddress = vm.checkedOfflineStore['name'];
                }
            }

            setUserAddress();
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
                alert('报名用户信息有误');
                return false;
            }
            if (!vm.validateShipInfo()) {
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

        function isShowExpressInfoBtn(order) {
            if (order['ship_mark'] == 'kuai_di') {
                if (order.status > 1 && vm.isOwner(order)) {
                    return true;
                }
            }
            return false;
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
                                    break;
                                }
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
                if (unitType == 1) {
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


        function handleShareData(data) {
            $rootScope.loadingPage = false;
            vm.weshare = data['weshare'];
            if (vm.weshare.addresses && vm.weshare.addresses.length == 1) {
                vm.selectedPickUpAddressId = vm.weshare.addresses[0].id;
            } else if (vm.weshare.addresses && vm.weshare.addresses.length > 1) {
                vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
                vm.selectedPickUpAddressId = -1;
            }
            vm.isManage = data['is_manage'];
            vm.canManageShare = data['can_manage_share'];
            vm.canEditShare = data['can_edit_share'];
            vm.recommendData = data['recommendData'];
            vm.currentUser = data['current_user'] || {};
            vm.consignee = data['consignee'];
            vm.myCoupons = data['my_coupons'];
            vm.rebateTotal = parseInt(data['user_rebate_total']);
            if(vm.rebateTotal > 0){
                vm.useRebate = 1;
            }
            vm.weshareSettings = data['weshare_ship_settings'];
            if(data['sub_status']){
                $rootScope.proxies.push(parseInt(vm.weshare.creator.id));
            };
            vm.autoPopCommentData = data['prepare_comment_data'];
            vm.dliveryTemplate = data['weshare']['deliveryTemplate'];
            vm.productSummery = data['share_summery'];
            vm.submitRecommendData = {};
            vm.submitRecommendData.recommend_content = vm.weshare.creator.nickname + '我认识，很靠谱！';
            vm.submitRecommendData.recommend_user = vm.currentUser.id;
            vm.submitRecommendData.recommend_share = vm.weshare.id;

            if (vm.myCoupons) {
                vm.useCouponId = vm.myCoupons.CouponItem.id;
                vm.userCouponReduce = vm.myCoupons.Coupon.reduced_price;
            }
            vm.userShareSummery = data['user_share_summery'];
            //|| vm.canManageShare
            if (vm.isCreator() || vm.canManageShare) {
                vm.faqTipText = '反馈消息';
            }
            //vm.checkShareInfoHeight();
            //load all comments
            vm.getShareSummeryData(vm.weshare.id, vm.weshare.creator.id);
            vm.loadOrderDetail(vm.weshare.id);
            vm.getRecommendWeshares(vm.weshare.id, vm.weshare.creator.id);
        }

        function getBannerImage(){
            if(/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream){
                return vm.staticFilePath + '/static/weshares/images/share-banner-ios-app.gif';
            }
            return vm.staticFilePath + '/static/weshares/images/share-banner-new.gif';
        }

        function getBannerLink(){
            if(/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream){
                return '/pys/download_app?from=share_view';
            }
            return '/weshares/add?from=share_view';
        }

        function getRecommendWeshares(weshareId, proxyId){
            $http.get('/weshares/get_recommend_weshares/' +  proxyId).success(function (data) {
                vm.recommendWeshares = _.filter(data, function(share){
                    return share.id != weshareId;
                });
                vm.recommendWeshares = vm.recommendWeshares.slice(0, 4);
            });
        }
    }
})(window, window.angular);
