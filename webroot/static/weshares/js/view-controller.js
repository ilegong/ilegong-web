(function (window, angular) {

	angular.module('weshares')
		.controller('WesharesViewCtrl', WesharesViewCtrl);

	function WesharesViewCtrl($state, $scope, $rootScope, $log, $http, $templateCache, $stateParams, Utils) {
		var vm = this;
		vm.statusMap = {
			0: '进行中',
			1: '已截止'
		};
		vm.viewImage = viewImage;
		vm.submitOrder = submitOrder;
		vm.increaseProductNum = increaseProductNum;
		vm.decreaseProductNum = decreaseProductNum;
		vm.getOrderDisplayName = getOrderDisplayName;

		vm.validateAddress = validateAddress;
		vm.validateProducts = validateProducts;
		vm.buyProducts = buyProducts;
    vm.validateMobile = validateMobile;
    vm.validateUserName = validateUserName;
    vm.getJoinUsercount=getJoinUserCount;

		activate();
		function activate() {
			var weshareId = $stateParams.id;
			vm.weshare = {};
			vm.orderTotalPrice = 0;
			$http({method: 'GET', url: '/weshares/detail/' + weshareId, cache: $templateCache}).
				success(function (data, status) {
					$log.log(data);
					vm.weshare = data['weshare'];
					if (vm.weshare.addresses.length == 1) {
						vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
					}
					else if (vm.weshare.addresses.length > 1) {
						vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
						vm.weshare.selectedAddressId = -1;
					}
					vm.ordersDetail = data['ordersDetail'];
					vm.currentUser = data['current_user']||{};
					vm.weixinInfo = data['weixininfo'];
					setWeiXinShareParams();
				}).
				error(function (data, status) {
					$log.log(data);
				});
		}

    function getJoinUserCount(){
      return Object.keys(vm.ordersDetail.users).length;
    }

		function setWeiXinShareParams() {
      if(wx){
        //creator
        var to_timeline_title = '';
        var to_friend_title = '';
        var imgUrl = '';
        var desc = '';
        var share_string = 'we_share';
        //member
        var userInfo =vm.ordersDetail.users[vm.currentUser.id];
        if(vm.currentUser.id==vm.weshare.creator.id){
          to_timeline_title = vm.weshare.creator.nickname+'分享'+vm.weshare.title;
          to_friend_title = vm.weshare.creator.nickname+'分享'+vm.weshare.title;
          imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
          if(vm.getJoinUsercount()>=5){
            desc+='已经有'+vm.getJoinUsercount()+'人报名，';
          }
          desc += vm.weshare.description;
        }else if(userInfo){
          to_timeline_title =userInfo.nickname+'报名'+vm.weshare.creator.nickname+'分享'+vm.weshare.title;
          to_friend_title = vm.weshare.creator.nickname+'分享'+vm.weshare.title;
          imgUrl = vm.weshare.images[0] || userInfo.image;
          desc = vm.weshare.creator.nickname+'是我的好朋友，我很信赖TA，很靠谱，'+vm.weshare.description;
        }else{
          //default custom
          to_timeline_title =vm.currentUser.nickname+'推荐'+vm.creator.nickname+'分享'+vm.weshare.title;
          to_friend_title = vm.weshare.creator.nickname+'分享'+vm.weshare.title;
          imgUrl = vm.weshare.images[0] || vm.currentUser.image;
          desc = vm.weshare.creator.nickname+'是我的好朋友，我很信赖TA，很靠谱，'+vm.weshare.description;;
        }
        if (vm.weixinInfo) {
          share_string = vm.weixinInfo.share_string;
        }
        wx.ready(function () {
          var to_friend_link = document.URL+ vm.weshare.id;
          var to_timeline_link = document.URL + vm.weshare.id;
          wx.onMenuShareAppMessage({
            title: to_friend_title,
            desc: desc,
            link: to_friend_link,
            imgUrl: imgUrl,
            success: function () {
              // 用户确认分享后执行的回调函数
              if(share_string != '0'){
                setTimeout(function(){
                  $http.post('/wx_shares/log_share',{ trstr: share_string, share_type: "appMsg" }).success().error();
                }, 500);
              }
            }
          });
          wx.onMenuShareTimeline({
            title: to_timeline_title,
            link: to_timeline_link,
            imgUrl: imgUrl,
            success: function () {
              if(share_string != '0'){
                setTimeout(function(){
                  $http.post('/wx_shares/log_share',{ trstr: share_string, share_type: "timeline" }).success().error();
                }, 500);
              }
            }
          });
        });
      }
      return;
		}

		function getOrderDisplayName(orderId) {
			var carts = vm.ordersDetail.order_cart_map[orderId];
			return _.map(carts, function (cart) {
				return cart.name + 'X' + cart.num;
			}).join(', ');
		}

		function viewImage(url) {
			wx.previewImage({
				current: url,
				urls: vm.weshare.images
			});
		}

		function calOrderTotalPrice() {
			var products = _.filter(vm.weshare.products, function (product) {
				return product.num > 0;
			});
			var totalPrice = 0;
			_.each(products, function (product) {
				totalPrice += product.price * product.num;
			});
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

    function validateMobile(){
      vm.buyerMobilePhoneHasError =  !Utils.isMobileValid(vm.buyerMobilePhone);
      return vm.buyerMobilePhoneHasError;
    }

    function validateUserName(){
      vm.usernameHasError =  _.isEmpty(vm.buyerName);
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

		function buyProducts() {
			var addressHasError = vm.validateAddress();
			var productsHasError = vm.validateProducts();
			if (addressHasError || productsHasError) {
				return;
			}

			vm.showBuyingDialog = true;
			vm.showLayer = true;
		}

		function submitOrder(paymentType) {
			vm.validateUserName();
			vm.validateMobile();

      if(vm.buyerMobilePhoneHasError || vm.usernameHasError){
        return false;
      }

			var products = _.filter(vm.weshare.products, function (product) {
				return product.num && (product.num > 0);
			});
			products = _.map(products, function (product) {
				return {id: product.id, num: product.num};
			});
			var orderData = {
				weshare_id: vm.weshare.id,
				address_id: vm.weshare.selectedAddressId,
				products: products,
				buyer: {name: vm.buyerName, mobilephone: vm.buyerMobilePhone}
			};

			$http.post('/weshares/makeOrder/', orderData).success(function (data) {
				$log.log(data);
				if (data.success) {
					//pay
					window.location.href = '/weshares/pay/' + data.orderId + '/' + paymentType;
				}
			}).error(function () {
			});
		}
	}
})(window, window.angular);