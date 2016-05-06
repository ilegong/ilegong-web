(function (window, angular, wx) {

  angular.module('weshares')
    .constant('wx', wx)
    .controller('TutorialBindingMobileCtrl', TutorialBindingMobileCtrl);


  function TutorialBindingMobileCtrl($scope, $rootScope, $log, $http, $interval, Utils, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.canSendCode = canSendCode;
    vm.sendCode = sendCode;
    vm.getSendCodeText = getSendCodeText;
    vm.validateMobilePhone = validateMobilePhone;
    vm.validateMobilePhoneAndAlert = validateMobilePhoneAndAlert;
    vm.canBindMobile = canBindMobile;
    vm.bindMobile = bindMobile;
    vm.canBindCard = canBindCard;
    vm.bindCard = bindCard;

    activate();
    function activate() {
      vm.mobilePhone = {value: '', valid: true};
      vm.code = {value: '', sent: false, timer: null, timeSpent: 60};
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
      $http.post('/check/get_message_code', {mobile: mobile}).success(function (data) {
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

      }).error(function () {
        vm.code.sending = false;
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
      $http.post('/users/mobile_bind', {
        'mobile': vm.mobilePhone.value,
        'code': vm.code.value,
        'from': ''
      }).success(function (data) {
        // TODO: Fix
        vm.binding = false;
        window.location.href = "/users/complete_user_info";
      }).error(function (data) {
        vm.binding = false;
      });
    }

    function bindCard() {

    }

    function canBindCard() {

    }
  }

})(window, window.angular, window.wx);
