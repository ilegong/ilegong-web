$(document).ready(function () {
  var $shareOrderListTagToggle = $('#order-tag-toggle');
  var $tagLi  = $('li', $shareOrderListTagToggle);
  var $divOrderItems = $('div.div-order-item');
  var $summeryProductItems = $('tr.summery-product-item');
  var $orderDataSummeryItems = $('tr.order-data-summery');
  var $zitiPanel = $('#self-ziti-orders');
  $('div.offer .div-share-item').on('click',function(e){
    e.preventDefault();
    var $me = $(this);
    var $parent = $me.parentsUntil('div.col-xs-12').parent('div.col-xs-12');
    var child_share_id = $parent.data('id');
    window.location.href='/weshares/view/'+child_share_id;
  });
  function init(){
    if($tagLi.length > 0){
      $('li:first', $shareOrderListTagToggle).trigger('click');
    }
  }
  $tagLi.on('click', function(e){
    e.preventDefault();
    var $me = $(this);
    var tagId = $me.data('id');
    handleTagChange(tagId);
    $tagLi.removeClass('active');
    $me.addClass('active');
  });
  init();
  function handleTagChange(tag) {
    if (tag == 'all') {
      $divOrderItems.show();
      $summeryProductItems.show();
      $orderDataSummeryItems.hide();
      $('tr[name="order-data-summery-all"]').show();
    } else {
      $divOrderItems.hide();
      $summeryProductItems.hide();
      $('div[name="order-item-tag-' + tag + '"]').show();
      $('tr[name="summery-product-' + tag + '"]').show();
      $orderDataSummeryItems.hide();
      $('tr[name="order-data-summery-' + tag + '"]').show();
    }
  }
  var orderType = '';
  var $selfZitiOrder = $('#self-ziti-orders');
  $('select[name="ship_company_code"]').on('change', function () {
    var valueSelected = this.value;
    $("option[value='" + valueSelected + "']").prop("selected", true);
  });
  $('ul.nav li a').on('click', function () {
    var $me = $(this);
    orderType = $me.data('order-type');
    if (orderType == 'self_ziti') {
      $('#send_product_arrive_msg').show();
    } else {
      $('#send_product_arrive_msg').hide();
    }
  });
  $('ul.nav li:first a').trigger('click');
  $('button.set-order-ship-code').on('click', function () {
    var $me = $(this);
    var $parent = $me.parentsUntil('div.col-xs-12');
    var $form = $me.parent('div');
    var $shipCompany = $('select[name=ship_company_code]', $form);
    var $shipCode = $('input[name=ship_code]', $form);
    var orderId = $me.data('order-id');
    var shipCompayId = $shipCompany.val();
    var shipCode = $shipCode.val();
    var weshareId = $me.data('weshare-id');
    var companyName = $("option:selected", $shipCompany).text();
    $.post('/weshares/set_order_ship_code', {
      order_id: orderId,
      company_id: shipCompayId,
      ship_code: shipCode,
      weshare_id: weshareId
    }, function (data) {
      if (data['success']) {
        $parent.removeClass('offer-success').addClass('offer-warning');
        $('div.shape-text', $parent).text('已发货');
        $form.parent('div.offer-content').append('<p><strong>' + companyName + ':</strong><strong>' + shipCode + '</strong> </p>');
        $form.remove();
      }
    }, 'json');
  });
  $('button[name="set_order_shipped"]').on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    bootbox.confirm("确定已发货?", function (result) {
      if (!result) {
        return;
      }
      var order_id = $me.data('id');
      var $parent = $me.parentsUntil('div.col-xs-12');
      $.getJSON('/weshares/confirmReceived/' + order_id, function (data) {
        if (data['success']) {
          $parent.removeClass('offer-success').addClass('offer-warning');
          $('div.shape-text', $parent).text('已发货');
          $me.next().remove();
          $me.remove();
        } else {
          alert('修改失败');
        }
      });
    });
  });
  $('button[name="send_product_arrival_msg"]').on('click', function (e) {
    e.preventDefault();
    var $msgInput = $('#arrival_msg');
    var msg = $msgInput.val();
    if (!msg || !msg.trim()) {
      return;
    }
    var id = $(this).data('id');
    var $processOrders = $('div.div-order-item:visible', $zitiPanel);
    var processOrderIds = [];
    $processOrders.each(function(index,item){
      var $item = $(item);
      processOrderIds.push($item.data('id'));
    });
    var processOrderIdStrs = processOrderIds.join(',');
    var postData = {msg: msg, share_id: id, ids: processOrderIdStrs};
    $.ajax({
      type: 'POST',
      url: '/weshares/send_arrival_msg',
      data: JSON.stringify(postData), // or JSON.stringify ({name: 'jonas'}),
      success: function (data) {
        $('div.send-msg-dialog').modal('hide');
        var $paidOrders = $('div[data-order-status="1"]', $selfZitiOrder);
        $paidOrders.each(function (index, item) {
          var $dom = $(item);
          $dom.removeClass('offer-success').addClass('offer-warning');
          $('div.shape-text', $dom).text('已发货');
          $dom.data('order-status', '2')
        });
      },
      contentType: "application/json",
      dataType: 'json'
    });

  });

  var $setShareShippedDialog = $('#set_share_shipped_dialog');
  var $setShipShareId = $('#set_shipped_share_id', $setShareShippedDialog);
  var $setShipShareMsg = $('#share_arrival_msg', $setShareShippedDialog);
  var $setShipReferShareId = $('#set_shipped_refer_share_id', $setShareShippedDialog);
  var $setShipAddress = $('#set_shipped_address', $setShareShippedDialog);

  $('button.set-shipped-share').on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var shareId = $me.data('id');
    var referShareId = $me.data('refer-share-id');
    var address = $me.data('address');
    $setShipReferShareId.val(referShareId);
    $setShipAddress.val($setShipAddress);
    $setShipShareId.val(shareId);
    $setShareShippedDialog.modal('show');
  });

  //批量发货对子分享
  $('button[name="set-share-shipped"]').on('click', function (e) {
    e.preventDefault();
    var msg = $setShipShareMsg.val();
    if (!msg || !msg.trim()) {
      return;
    }
    var shareId = $setShipShareId.val();
    var referShareId = $setShipReferShareId.val();
    var shipAddress = $setShipAddress.val();
    var postData = {'share_id': shareId, 'refer_share_id': referShareId, 'address': shipAddress, 'msg': msg};
    $.post('/weshares/set_share_shipped', postData, function () {
      var $setShippedBtn = $('#set-share-shipped-btn-' + shareId);
      $setShippedBtn.unbind();
      var $parent = $setShippedBtn.parent('div.offer-content');
      $parent = $parent.parent();
      $parent.removeClass().addClass('offer').addClass('offer-success');
      $('div.shape-text', $parent).text('已发货');
      $setShippedBtn.remove();
      $setShareShippedDialog.modal('hide');
    }, 'json');
  });

  var $refundMoneyDialog = $('#refund-money-dialog');
  var $refundOrderName = $('#refund-order-user', $refundMoneyDialog);
  var $refundOrderId = $('#refund-order-id', $refundMoneyDialog);
  var $refundMoney = $('#refund-money', $refundMoneyDialog);
  var $refundMsg = $('#refund-msg', $refundMoneyDialog);
  $('button[name="handle-refund-money"]').on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var refundMoney = $refundMoney.val();
    if (isNaN(refundMoney)) {
      alert('请输入退款金额');
      return false;
    }
    var refundMark = $refundMsg.val();
    if (!refundMark) {
      alert('请输入退款原因');
      return false;
    }
    var postData = {
      orderId: $refundOrderId.val(),
      shareId: $me.data('id'),
      refundMoney: $refundMoney.val(),
      refundMark: $refundMsg.val()
    };
    $.post('/weshares/refund_money.json', postData, function (data) {
      if (data['success']) {
        var orderId = data['order_id'];
        var $refundBtn = $('#refund-btn-' + orderId);
        $refundBtn.unbind();
        var $parent = $refundBtn.parent('div.offer-content');
        $parent = $parent.parent();
        $parent.removeClass().addClass('offer').addClass('offer-danger');
        $('div.shape-text', $parent).text('退款中');
        $refundBtn.remove();
        $refundMoneyDialog.modal('hide');
      }
    }, 'json');
  });
  $('button.refund-money').on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var orderId = $me.data('order-id');
    var orderUserName = $me.data('order-name');
    $refundOrderName.val(orderUserName);
    $refundOrderId.val(orderId);
    $refundMoney.val(0);
    $refundMsg.val('');
    $refundMoneyDialog.modal('show');
  });
  var $confirmMoneyDialog = $('#confirm-money-dialog');
  var $confirmOrderId = $('#confirm-order-id', $confirmMoneyDialog);
  var $confirmUsername = $('#confirm-order-user', $confirmMoneyDialog);
  var cartJsonStr = '';
  var cartJsonData = {};
  $('button.price-confirm').on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var orderId = $me.data('order-id');
    var orderName = $me.data('order-name');
    $confirmOrderId.val(orderId);
    $confirmUsername.val(orderName);
    cartJsonStr = $('#order-cart-info-' + orderId).val();
    cartJsonData = JSON.parse(cartJsonStr);
    var formDom = '';
    $.each(cartJsonData, function (index, item) {
      if (item['confirm_price'] == 0) {
        formDom = formDom + '<div class="form-group cart-item">' +
        '<label for="refund-money" class="col-sm-2 control-label">' + item['name'] + 'X' + item['num'] + '&nbsp;&nbsp;已经预付' + (item['price'] * item['num'] / 100) + '</label>' +
        '<div class="col-sm-10">' +
        '<input type="number" placeholder="实际价格" class="form-control" id="cart_' + item['id'] + '" data-origin-price="' + (item['price'] * item['num']) + '">' +
        '</div>' +
        '</div>';
      }
    });
    $('form', $confirmMoneyDialog).append(formDom);
    $confirmMoneyDialog.modal('show');
    //clear dom
    $confirmMoneyDialog.on('hide.bs.modal', function () {
      // clear dynamic form
      $('form div.cart-item', $confirmMoneyDialog).remove();
    });
  });
  $('button[name="handle-confirm-money"]').on('click', function (e) {
    e.preventDefault();
    var $postData = {};
    $postData['order_id'] = $confirmOrderId.val();
    $postData['cart_map'] = [];
    $.each(cartJsonData, function (index, item) {
      if(item['confirm_price'] == 0){
        var cartId = item['id'];
        var $cartDom = $('#cart_' + cartId, $confirmMoneyDialog);
        var cartOriginPrice = $cartDom.data('origin-price');
        var cartPrice = $cartDom.val() || cartOriginPrice;
        var cartProductId = item['product_id'];
        var cartMapData = {};
        cartMapData['product_id'] = cartProductId;
        cartMapData['price'] = cartPrice;
        $postData['cart_map'].push(cartMapData);
      }
    });
    var $postJsonStr = JSON.stringify($postData);
    $.post('/weshares/confirm_price.json', {data: $postJsonStr}, function (result) {
      if (result['success']) {
        var orderId = result['order_id'];
        var difference_price = parseFloat(result['difference_price']);
        var $priceConfirmBtn = $('#price-confirm-btn-' + orderId);
        $priceConfirmBtn.unbind();
        var $parent = $priceConfirmBtn.parent('div.offer-content');
        $parent = $parent.parent();
        var $statusLabel = $('#process-prepaid-status span', $parent);
        if (difference_price > 0){
          $statusLabel.removeClass().addClass('label').addClass('label-info').text('待补款');
        }else if(difference_price < 0){
          $statusLabel.removeClass().addClass('label').addClass('label-info').text('待退差价');
        }else{
          $statusLabel.removeClass().addClass('label').addClass('label-info').text('无差价');
        }
        $priceConfirmBtn.remove();
        $confirmMoneyDialog.modal('hide');
      } else {
        alert('保存失败,请联系客服');
      }
    }, 'json');
  });

  var $refundShareDialog = $('#refund-share-dialog');
  var $refundShareOfflineAddress = $('#refund-share-address', $refundShareDialog);
  var $refundShareId = $('#refund-share-id', $refundShareDialog);
  var $refundShareRemark = $('#refund-share-msg', $refundShareDialog);

  $('button.refund-share-money').on('click', function(e){
    e.preventDefault();
    var $me = $(this);
    var address = $me.data('address');
    var shareId = $me.data('id');
    $refundShareOfflineAddress.val(address);
    $refundShareId.val(shareId);
    $refundShareRemark.val('');
    $refundShareDialog.modal('show');
  });

  $('button[name="handle-refund-share-money"]').on('click', function(e){
    e.preventDefault();
    var refundMark = $refundShareRemark.val();
    var shareId = $refundShareId.val();
    if (!refundMark) {
      alert('请输入退款原因');
      return false;
    }
    var postData = {
      refundMark: refundMark
    };
    $.post('/weshares/refund_share/'+shareId+'.json', postData, function (data) {
      if (data['success']) {
        //todo mark
        $('#refund-share-btn-'+shareId).remove();
        $refundShareDialog.modal('hide');
      }
    }, 'json');
  });
  //update order ship info
  var $toEditOrderShipInfoBtn = $('button.edit-ship-code');
  var $editOrderShipInfoForm = $('div.update-ship-info-dialog');
  var $editOrderShipTypeNameEl = $('input[name="ship_type_name"]', $editOrderShipInfoForm);
  var $editOrderShipCodeEl = $('input[name="ship_code"]', $editOrderShipInfoForm);
  var $editOrderShipOrderId = $('input[name="order_id"]', $editOrderShipInfoForm);
  var $handleUpdateShipCodeBtn = $('button[name="handle-update-ship-code"]', $editOrderShipInfoForm);
  $toEditOrderShipInfoBtn.on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var orderId = $me.data('order-id');
    var $shipInfoP = $me.parent();
    var shipTypeName = $('strong[name="order_ship_type_name"]', $shipInfoP).text();
    var shipCode = $('strong[name="order_ship_code"]', $shipInfoP).text();
    $editOrderShipTypeNameEl.val(shipTypeName);
    $editOrderShipCodeEl.val(shipCode);
    $editOrderShipOrderId.val(orderId);
    $editOrderShipInfoForm.modal('show');
  });
  $handleUpdateShipCodeBtn.on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var weshare_id = $me.data('id');
    var order_id = $editOrderShipOrderId.val();
    var ship_type_name = $editOrderShipTypeNameEl.val();
    var ship_code = $editOrderShipCodeEl.val();
    if(!ship_code || !ship_type_name){
      alert('请输入快递单号和快递公司');
      return false;
    }
    var postData = {
      'order_id': order_id,
      'ship_type_name': ship_type_name,
      'ship_code': ship_code,
      'weshare_id': weshare_id
    };
    $.post('/weshares/update_order_ship_code', postData, function (data) {
      if(data['success']){
        //update view
        var $currentOrderShipInfo = $('#order-ship-info-' + order_id);
        $('strong[name="order_ship_type_name"]', $currentOrderShipInfo).text(ship_type_name);
        $('strong[name="order_ship_code"]', $currentOrderShipInfo).text(ship_code);
        $editOrderShipOrderId.val('');
        $editOrderShipTypeNameEl.val('');
        $editOrderShipCodeEl.val('');
        $editOrderShipInfoForm.modal('hide');
      }
    }, 'json')
  });
});
