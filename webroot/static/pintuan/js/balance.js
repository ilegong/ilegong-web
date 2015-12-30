$(document).ready(function () {
  var $consgineeAddress = $('#consigneeAddress');
  var $consgineeName = $('#consigneeName');
  var $consigneeMobile = $('#consigneeMobile');
  var $businessRemark = $('#businessRemark');

  var $tagId = $('#tagId');
  var $start = $('#start');
  var $normal = $('#normal');
  var $shareId = $('#shareId');

  var $wxPayBtn = $('#wx-pay');
  var $aliPay = $('#ali-pay');

  $wxPayBtn.on('click', function (e) {
    e.preventDefault();
    triggerMakeOrder(0);
  });

  $aliPay.on('click', function (e) {
    e.preventDefault();
    triggerMakeOrder(1);
  });

  function triggerMakeOrder(payType) {
    var tradeType = getTradeType();
    var postData = {
      consignee_address: $consgineeAddress.val(),
      consignee_mobilephone: $consigneeMobile.val(),
      business_remark: $businessRemark.val(),
      consignee_name: $consgineeName.val(),
      share_id: $shareId.val()
    };
    if (tradeType == 2) {
      postData['tag_id'] = $tagId.val()
    }
    $.post('/pintuan/make_order/' + tradeType, postData, function (data) {
      handleMakeOrderResult(data, payType);
    }, 'json');
  }

  function handleMakeOrderResult(data, payType) {
    if (data['success']) {
      if (data['order_id']) {
        window.location.href = '/pintuan/pay/' + payType + '/' + data['order_id'];
      } else {
        alert('下单失败，请联系客服！');
      }
    } else {
      var errorMsg = getMakeOrderErrorMsg(data);
      alert(errorMsg);
    }
  }

  function getMakeOrderErrorMsg(data) {
    if (data['reason'] == 'not_login') {
      return '没有登录';
    }
    if (data['reason'] == 'param_error') {
      return '下单失败';
    }
    if (data['reason'] == 'system_error') {
      return '下单失败，请联系客服';
    }
  }

  function getTradeType() {
    if ($tagId.val()) {
      return 2;
    }
    if ($start.val()) {
      return 1;
    }
    if ($normal.val()) {
      return 0;
    }
  }

});