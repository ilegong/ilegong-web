$(document).ready(function () {

    function bindImgEvent(){
        $('.preview-image').unbind();
        $('.delete-image').unbind();
        $('.preview-image').on("click", function () {
            $('#image-preview').attr('src', $(this).attr('src-data'));
            $('#image-preview-modal').modal('show');
        });

        $('.delete-image').on("click", function () {
            var arr = $('#share-images').val().split('|');
            var idx = arr.splice(arr.indexOf($(this).attr('src-data')), 1);
            var nstring = arr.join('|');
            $('#share-images').val(nstring);
            $(this).parent('div').parent('div.image-area').remove();
        });

        $(document).on("click",".set-first-image",function(){
            var obj = $(this).parent().parent();
            var clone = obj.clone();
            src = $(this).attr('src-data');
            $(".image-area:first").before(clone);
            obj.remove();
            var arr = $('#share-images').val().split('|');
            var idx = arr.splice(arr.indexOf(src), 1);
            var nstring = arr.join('|');
            $('#share-images').val(src+'|'+nstring);

        })

    }

    bindImgEvent();

    $('#upload-image, #banner-upload-image').on("click", function () {
        $('#uploader').click();
    });

    $('#upload-image-action').on("click", function () {
        var formData = new FormData($('#file-uploader').get(0));
        console.log(formData);

        $.ajax({
            url: 'http://images.tongshijia.com/upload_images_to',
            type: 'post',
            data: formData,
            dataType: 'json',
            async: false,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.url[0]) {
                    var imgUrl = 'http://static.tongshijia.com/' + data.url[0];
                    var obj = $('#image-area-template').eq(0).clone();
                    obj.find('img').attr('src', imgUrl);
                    obj.find('a').attr('src-data', imgUrl);
                    obj.show();
                    obj.attr('id', '');
                    $('.share-upload-btn').before(obj);
                    $('.preview-image').on("click", function () {
                        $('#image-preview').attr('src', $(this).attr('src-data'));
                        $('#image-preview-modal').modal('show');
                    });
                    $('#share-images').val($('#share-images').val() + "|" + imgUrl);
                    bindImgEvent();
                }
            },
            error: function (data) {
            }
        });

    });
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
        var shareCreator = $('.share-creator').val();
        var shareTitle = $shareTitleEl.val();
        var shareSendInfo = $shareSendInfoEl.val();
        var imagesStr = $('#share-images').val();
        var shareDescription = $shareDescriptionEl.val();
        var data = {
            "id": shareId,
            "images": imagesStr,
            "title": shareTitle,
            "send_info": shareSendInfo,
            "description": shareDescription,
            "creator": shareCreator
        };
        //todo valid data
        $.post('/share_manage/update_share.json', {
            shareId: $shareIdEl.val(),
            "data": JSON.stringify(data)
        }, function (data) {
            if (data['success']) {
                alert('保存成功');
            }
        }, 'json');
    });

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
        $.post('/share_manage/update_share_product.json', {
            shareId: $shareIdEl.val(),
            "data": JSON.stringify(productData)
        }, function (data) {
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
    function initRmShareBtnEvent() {
        var $shareProudctRmBtn = $('button.share-product-rm-btn', $productContainer);
        $shareProudctRmBtn.unbind();
        $shareProudctRmBtn.on('click', function (e) {
            e.preventDefault();
            var $me = $(this);
            $me.parent().parent().parent().parent().remove();
        });
    }

    function getProductData() {
        var productData = [];
        $('div.product-item', $productContainer).each(function (index, item) {
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

    //end edit share product
    //update share ship setting
    var $shareShipSettingInfoPanel = $('#shareShipSettingsInfo');
    var $shipSettingItems = $('div.ship-setting-item', $shareShipSettingInfoPanel);
    var $addNewAddressBtn = $('button.add-new-address', $shareShipSettingInfoPanel);
    var $newAddressInput = $('input.new-address-val', $shareShipSettingInfoPanel);
    var $shipFeeInput = $('input.ship-fee', $shareShipSettingInfoPanel);
    var $pinTuanNumInput = $('input.pin-tuan-num', $shareShipSettingInfoPanel);
    var $weshareAddressUl = $('ul[name="weshare-addresses"]', $shareShipSettingInfoPanel);
    var $saveShareShipSetting = $('#save-share-ship-setting', $shareShipSettingInfoPanel);
    var weshareAddressData = [];
    var shipSettingData = [];
    initRmWeshareAddressEvent();
    var processSaveShareShipSetting = false;
    $saveShareShipSetting.on('click', function (e) {
        e.preventDefault();
        if (processSaveShareShipSetting) {
            alert('正在保存');
            return;
        }
        processSaveShareShipSetting = true;
        getShareAddress();
        getShareShipSettingsData();
        var postData = {
            'ship_setting': shipSettingData,
            'weshare_address': weshareAddressData
        };
        $.post('/share_manage/update_share_ship_setting.json', {
            shareId: $shareIdEl.val(),
            data: JSON.stringify(postData)
        }, function (data) {
            if (data['success']) {
                alert('保存成功')
            }
        }, 'json');
    });

    function getShareAddress() {
        var $addresses = $('li', $weshareAddressUl);
        $addresses.each(function (index, item) {
            var $item = $(item);
            var addressId = $item.data('id');
            var address = $item.text();
            var deleted = parseInt($item.data('deleted'));
            if (addressId != 0 || (addressId == 0 && deleted == 0)) {
                weshareAddressData.push({
                    'id': addressId,
                    'address': address,
                    'deleted': deleted,
                    'weshare_id': $shareIdEl.val()
                });
            }
        });
    }

    function getShareShipSettingsData() {
        $shipSettingItems.each(function (index, item) {
            var $item = $(item);
            var shipName = $item.data('name');
            var shipId = $item.data('id');
            var $shipStatus = $('input[name="ship_status"]', $item);
            var data = {
                'tag': shipName,
                'id': shipId,
                'status': -1,
                'limit': 0,
                'ship_fee': 0,
                'weshare_id': $shareIdEl.val()
            };
            if ($shipStatus.is(':checked')) {
                data['status'] = 1;
            }
            //reset ship fee
            if (shipName == 'kuai_di') {
                data['ship_fee'] = $shipFeeInput.val() || 0;
                data['ship_fee'] = data['ship_fee'] * 100;
            }
            //reset limit person
            if (shipName == 'pin_tuan') {
                data['limit'] = $pinTuanNumInput.val() || 1;
            }
            shipSettingData.push(data);
        });
    }

    $addNewAddressBtn.on('click', function (e) {
        e.preventDefault();
        var address = $newAddressInput.val();
        if (address) {
            $weshareAddressUl.append('<li data-id="0" data-deleted="0">' + address + '<button type="button" class="btn btn-warning btn-circle rm-weshare-address" style="width: 20px;height: 20px;padding: 0px 0;"><i class="fa fa-times fa-lg"></i></button></li>');
            initRmWeshareAddressEvent();
            $newAddressInput.val('');
        } else {
            alert('请输入地址');
        }
    });

    function initRmWeshareAddressEvent() {
        var $rmWeshareAddressBtn = $('button.rm-weshare-address', $shareShipSettingInfoPanel);
        $rmWeshareAddressBtn.unbind();
        $rmWeshareAddressBtn.on('click', function (e) {
            var $me = $(this);
            $me.parent().hide();
            $me.parent().data('deleted', "1");
        });
    }

    //end update share ship setting
    //update share rebate setting
    var $updateRebateSetPanel = $('#shareRebateSettingInfo');
    var $rebateSetId = $('input[name="shareRebateId"]', $updateRebateSetPanel);
    var $rebateStatusCheckBox = $('input[name="shareRebateStatus"]', $updateRebateSetPanel);
    var $rebatePercent = $('input[name="shareRebate"]', $updateRebateSetPanel);
    var $saveRebateSetBtn = $('#saveShareRebateSetting', $updateRebateSetPanel);
    initRebateStatusCheckBox();
    $saveRebateSetBtn.on('click', function (e) {
        var rebateSetting = {
            'share_id': $shareIdEl.val(),
            'percent': $rebatePercent.val(),
            'id': $rebateSetId.val(),
            'status': $rebateStatusCheckBox.is(':checked') ? 1 : 0
        };
        $.post('/share_manage/update_share_rebate_setting.json', {
            shareId: $shareIdEl.val(),
            data: JSON.stringify(rebateSetting)
        }, function (data) {
            if (data['success']) {
                alert('保存成功');
            }
        }, 'json');
    });
    function initRebateStatusCheckBox() {
        var rebateStatusCheckBoxVal = $rebateStatusCheckBox.data('init-val');
        if (rebateStatusCheckBoxVal == 1) {
            $rebateStatusCheckBox.prop('checked', true);
        }
    }

    $("#payment_type").change(function(){
            if($(this).val() == 0)
            {
                $("#card_name").hide();
            }else{
                $("#card_name").show();
            }
        }
    );

    $("#save-user-info").click(function(){
       $.post("/share_manage/save_user_edit",
           $("#form").serializeArray(),
           function(data,status){
               history.go(-1);
           }
       )
    });
});
