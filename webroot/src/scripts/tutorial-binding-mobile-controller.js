(function (window, angular, wx) {

  angular.module('weshares')
    .constant('wx', wx)
    .controller('TutorialBindingMobileCtrl', TutorialBindingMobileCtrl);

  function TutorialBindingMobileCtrl($scope, $rootScope, $log, $http, $interval, $timeout, Utils) {
    var vm = this;
    vm.staticFilePath = Utils.staticFilePath();
    vm.canSendCode = canSendCode;
    vm.sendCode = sendCode;
    vm.getSendCodeText = getSendCodeText;
    vm.validateMobilePhone = validateMobilePhone;
    vm.validateMobilePhoneAndAlert = validateMobilePhoneAndAlert;
    vm.canBindMobile = canBindMobile;
    vm.bindMobile = bindMobile;
    vm.onCodeChanged = onCodeChanged;
    vm.onError = onError;
    vm.goBack = function () {
      window.history.back();
    }

    activate();
    function activate() {
      $rootScope.loadingPage = false;
      vm.mobilePhone = {value: '', valid: true};
      vm.code = {value: '', sent: false, timer: null, timeSpent: 60, valid: true};
    }

    function validateMobilePhone() {
      var valid = Utils.isMobileValid(vm.mobilePhone.value);
      if (valid && !vm.mobilePhone.valid) {
        vm.mobilePhone.valid = true;
      }
      return valid;
    }

    function validateMobilePhoneAndAlert() {
      vm.mobilePhone.valid = vm.validateMobilePhone();
    }

    function getSendCodeText() {
      if (vm.code.sent) {
        return '已发送(' + vm.code.timeSpent + 's)';
      }
      return '获取验证码';
    }

    function canSendCode() {
      return Utils.isMobileValid(vm.mobilePhone.value) && !_.isEmpty(vm.mobilePhone.value) && !vm.code.sent;
    }

    function sendCode() {
      if (!vm.canSendCode()) {
        return;
      }

      vm.code.sending = true;
      $http.post('/check/get_mobile_code?mobile=' + vm.mobilePhone.value).success(function (data) {
        if (data.error) {
          if (data.msg == 'not login') {
            window.location.href = '/users/login';
            return;
          }
        }
        vm.code.sent = true;
        vm.code.timer = $interval(function () {
          vm.code.timeSpent = Math.max(0, vm.code.timeSpent - 1);
          if (vm.code.timeSpent <= 0) {
            vm.code.sent = false;
            vm.code.timeSpent = 60;
            if (!_.isEmpty(vm.code.timer)) {
              $interval.cancel(vm.code.timer);
              vm.code.timer = null;
            }
          }
        }, 1000);
        vm.code.sending = false;
      }).error(function (data) {
        vm.code.sending = false;
        $log.log("Failed to get mobile code: ").log(data);
      });
    }

    function canBindMobile() {
      return Utils.isMobileValid(vm.mobilePhone.value) && !_.isEmpty(vm.mobilePhone.value) && !_.isEmpty(vm.code.value);
    }

    function bindMobile() {
      if (!vm.canBindMobile()) {
        return;
      }
      vm.binding = true;
      $http.post('/users/mobile_bind', {'mobile': vm.mobilePhone.value, 'code': vm.code.value}).success(function (data) {
        vm.binding = false;
        if (data.success) {
          window.location.href = '/users/complete_user_info';
          return;
        }
        if (data['msg'] == 'user_not_login') {
          vm.onError("未登录，请登陆后再操作");
          window.location.href = "/users/login";
          return;
        }
        if (data['msg'] == 'mobile_phone_duplicate') {
          vm.onError("该手机号已经被注册,请联系管理员");
          vm.mobilePhone.valid = false;
          return;
        }
        if (data['msg'] == 'code_invalid') {
          vm.onError("验证码错误，请重新输入");
          vm.code.valid = false;
          return;
        }
        if (data['msg'] == 'mobile_phone_invalid') {
          vm.onError("手机号错误，请联系管理员");
          vm.mobilePhone.valid = false;
          return;
        }

        vm.onError("系统错误，请联系管理员");
        vm.code.valid = false;
      }).error(function(data){
        vm.onError("系统错误，请联系管理员");
        vm.code.valid = false;
        vm.binding = false;
      });
    }

    function onCodeChanged(){
      vm.code.valid = !_.isEmpty(vm.code.value);
    }
    function onError(message) {
      $rootScope.showErrorMessageLayer = true;
      $rootScope.errorMessage = message;
      $timeout(function () {
        $rootScope.showErrorMessageLayer = false;
      }, 2000);
    }
  }

})(window, window.angular, window.wx);
