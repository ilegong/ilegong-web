$(document).ready(function () {
  //wizard form
  var navListItems = $('div.setup-panel div a'),
    allWells = $('.setup-content'),
    allNextBtn = $('.nextBtn');

  allWells.hide();
  navListItems.click(function (e) {
    e.preventDefault();
    var $target = $($(this).attr('href')),
      $item = $(this);

    if (!$item.hasClass('disabled')) {
      navListItems.removeClass('btn-primary').addClass('btn-default');
      $item.addClass('btn-primary');
      allWells.hide();
      $target.show();
      $target.find('input:eq(0)').focus();
    }
  });
  allNextBtn.click(function () {
    var curStep = $(this).closest(".setup-content"),
      curStepBtn = curStep.attr("id"),
      nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
      curInputs = curStep.find("input[type='text'],input[type='url']"),
      isValid = true;

    $(".form-group").removeClass("has-error");
    for (var i = 0; i < curInputs.length; i++) {
      if (!curInputs[i].validity.valid) {
        isValid = false;
        $(curInputs[i]).closest(".form-group").addClass("has-error");
      }
    }
    if (isValid)
      nextStepWizard.removeAttr('disabled').trigger('click');
  });
  $('div.setup-panel div a.btn-primary').trigger('click');
  //end wizard form
  //update share info
  var $shareBasicInfoPanel = $('#share-basic-info-panel');
  var $shareIdEl = $('#share-id');
  var $saveShareBasicInfoBtn = $('#save-share-basic-info', $shareBasicInfoPanel);
  var $shareTitleEl = $('#share-title', $shareBasicInfoPanel);
  var $shareSendInfoEl = $('#share-send-info', $shareBasicInfoPanel);
  var $shareImagesEl = $('#share-images', $shareBasicInfoPanel);
  var $shareDescriptionEl = $('#share-description', $shareBasicInfoPanel);
  $saveShareBasicInfoBtn.on('click', function (e) {
    e.preventDefault();
    var shareId = $shareIdEl.val();
    var shareTitle = $shareTitleEl.val();
    var shareSendInfo = $shareSendInfoEl.val();
    var imagesStr = get_share_images();
    var shareDescription = $shareDescriptionEl.val();
    var data = {
      "id": shareId,
      "images": imagesStr,
      "title": shareTitle,
      "send_info": shareSendInfo,
      "description": shareDescription
    };
    //todo valid data
    $.post('/share_manage/update_share.json', {"data": JSON.stringify(data)}, function (data) {
      if (data['success']) {
        alert('保存成功');
      }
    }, 'json');
  });

  function get_share_images() {
    var $imagesEl = $('div.ui-upload-filelist img', $shareBasicInfoPanel);
    var images = [];
    $imagesEl.each(function (index, item) {
      images.push($(item).attr('src'));
    });
    return images.join('|');
  }

  //end update share info
  //update share products
  var $shareProductInfoPanel = $('#share-product-info');
  var $addNewShareProductBtn = $('#add-new-product', $shareProductInfoPanel);
  var $saveShareProductBtn = $('#save-share-product', $shareProductInfoPanel);
  var $productContainer = $('#product-container', $shareProductInfoPanel);
  var $shareProductTemplate = $('#share-product-template', $shareProductInfoPanel);
  initSelectCheckBoxVal();
  $saveShareProductBtn.on('click', function (e) {
    e.preventDefault();
    var productData = getProductData();
    $.post('/share_manage/update_share_product.json', {"data": JSON.stringify(productData)}, function (data) {
      if (data['success']) {
        alert('更新产品成功');
      }
    }, 'json');
  });
  $addNewShareProductBtn.on('click', function (e) {
    e.preventDefault();
    $productContainer.append($shareProductTemplate.clone(true).attr('id', '').show());
    initRmShareBtnEvent();
  });
  function initRmShareBtnEvent(){
    var $shareProudctRmBtn = $('button.share-product-rm-btn', $productContainer);
    $shareProudctRmBtn.unbind();
    $shareProudctRmBtn.on('click', function(e){
      e.preventDefault();
      var $me = $(this);
      $me.parent().parent().parent().remove();
    });
  }
  function getProductData() {
    var productData = [];
    $('div.product-item', $shareProductInfoPanel).each(function (index, item) {
      var $item = $(item);
      var $productId = $('input[name="product-id"]', $item);
      var $productName = $('input[name="product-name"]', $item);
      var $productPrice = $('input[name="product-price"]', $item);
      var $productStore = $('input[name="product-store"]', $item);
      var $productTag = $('select[name="product-tag"]', $item);
      var $productTbd = $('input[name="product-tdb"]', $item);
      var $productLimit = $('input[name="product-limit"]', $item);
      var $productDeleted = $('input[name="product-deleted"]', $item);
      var data = {
        "id": $productId.val(),
        "name": $productName.val(),
        "price": $productPrice.val(),
        "store": $productStore.val(),
        "tag_id": $productTag.val(),
        "weshare_id": $shareIdEl.val()
      };
      data['tbd'] = $productTbd.is(':checked') ? 1 : 0;
      data['limit'] = $productLimit.is(':checked') ? 1 : 0;
      data['deleted'] = $productDeleted.is(':checked') ? 1 : 0;
      productData.push(data);
    });
    return productData;
  }

  function initSelectCheckBoxVal() {
    $('select', $shareProductInfoPanel).each(function (index, item) {
      var $item = $(item);
      var initVal = $item.data('init-val');
      $item.val(initVal);
    });
    $('input[type="checkbox"]', $shareProductInfoPanel).each(function (index, item) {
      var $item = $(item);
      var initVal = $item.data('init-val');
      if (initVal > 0) {
        $item.attr("checked", true);
      } else {
        $item.attr("checked", false);
      }
    });
  }
});