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
        "card_name": ''
      }
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
      $http.post().success(function () {
        vm.binding = false;
      }).error(function (data) {
        vm.binding = false;
      });
    }

    function canBindCard() {
      if (vm.payment.type == 0) {
        return !_.isEmpty(vm.zhifubao.account) && !_.isEmpty(vm.zhifubao.full_name);
      }
      else {
        return !_.isEmpty(vm.bank.account) && !_.isEmpty(vm.bank.full_name) && !_.isEmpty(vm.bank.card_name);
      }
    }

    function onAccountChanged() {
      $http.get('/ban').success(function(){

      }).error(function(){

      });
    }
  }

})(window, window.angular, window.wx);
