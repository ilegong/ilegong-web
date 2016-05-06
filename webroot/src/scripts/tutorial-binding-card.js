(function (window, angular, wx) {

  angular.module('weshares')
    .constant('wx', wx)
    .controller('TutorialBindingCardCtrl', TutorialBindingCardCtrl);


  function TutorialBindingCardCtrl($scope, $rootScope, $log, $http, $interval, Utils, staticFilePath) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.canBindCard = canBindCard;
    vm.bindCard = bindCard;
    vm.onAccountChanged = onAccountChanged;
    vm.validateBankAccount = validateBankAccount;
    vm.onError = onError;
    vm.goBack = function () {
      window.history.back();
    }

    activate();
    function activate() {
      vm.payment = {
        "type": 0,
        "account": '',
        "full_name": '',
        "card_name": ''
      }
      vm.zhifubao = {
        "account": '',
        "full_name": ''
      }
      vm.bank = {
        "account": '',
        "full_name": '',
        "card_name": '',
        account_valid: true
      }
    }

    function validateBankAccount() {
      var valid = /^\d{16,20}$/.test(vm.bank.account);
      if (valid && !vm.bank.account_valid) {
        vm.bank.account_valid = true;
      }
      return valid;
    }

    function onAccountChanged() {
      vm.bank.account_valid = vm.validateBankAccount();
      if (!vm.bank.account_valid) {

      }

      $http.get('/payapi/get_bank_info/' + vm.bank.account).success(function (data) {
        if (data.validated) {
          vm.bank.card_name = data.card_name;
        }
      }).error(function () {
      });
    }

    function bindCard() {
      if (vm.binding) {
        return;
      }

      vm.binding = true;
      if (vm.payment.type == 0) {
        vm.payment.account = vm.zhifubao.account;
        vm.payment.full_name = vm.zhifubao.full_name;
      }
      else {
        vm.payment.account = vm.bank.account;
        vm.payment.full_name = vm.bank.full_name;
        vm.payment.card_name = vm.bank.card_name;
      }
      $log.log('Bind payment: ').log(vm.payment);
      $http.post('/users/complete', {payment: vm.payment}).success(function () {
        vm.binding = false;
        window.location.href="/weshares/add";
      }).error(function (data) {
        vm.binding = false;
      });
    }

    function canBindCard() {
      if (vm.payment.type == 0) {
        return !_.isEmpty(vm.zhifubao.account) && !_.isEmpty(vm.zhifubao.full_name);
      }
      else {
        return !_.isEmpty(vm.bank.account) && !_.isEmpty(vm.bank.full_name) && !_.isEmpty(vm.bank.card_name) && vm.bank.account_valid;
      }
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
