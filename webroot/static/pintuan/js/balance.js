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

  function validOrderData() {
    var consignee_address = $consgineeAddress.val();
    var consignee_mobilephone = $consigneeMobile.val();
    var consignee_name = $consgineeName.val();
    if (!consignee_address.trim()) {
      alert('请输入收件地址');
      return false;
    }
    if (!consignee_mobilephone.trim()) {
      alert('请输入收件人联系方式');
      return false;
    }
    if (!consignee_name.trim()) {
      alert('请输入收件人名称');
      return false;
    }
    return true;
  }

  function triggerMakeOrder(payType) {
    if (!validOrderData()) {
      return;
    }
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