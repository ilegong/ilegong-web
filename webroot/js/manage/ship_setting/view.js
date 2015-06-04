$(function () {
    var $shipSettings = $('.ship-settings');
    var $saveBtn = $('#save-ship-setting');
    var dataId = $shipSettings.data('product-id');
    var dataType = $shipSettings.data('type');
    var $shipSettingAddBtn = $('.ship-setting-add-btn');
    var shipSettingZitiTemplate = $('#ship-setting-ziti-template').html();
    var getDisplayName = function($shipSetting){
        var shipType = $('.ship-type', $shipSetting).val();
        var shipVal = $('.ship-val', $shipSetting).val();
        var leastNum = $('.least-num', $shipSetting).val();
        if(shipType == 1){
            return '自提';
        } else if(shipType == 5){
            return '顺丰到付';
        }

        var displayName = '';
        if(shipVal <= 0){
            $('.ship-val', $shipSetting).val(0);
            if(leastNum <= 1){
                displayName = '包邮';
            }
            else{
                displayName = '满' + leastNum + '份包邮';
            }
        }
        else{
            if(leastNum <= 1){
                displayName = '快递(' + (shipVal / 100) + '元)';
            }
            else{
                displayName = '满' + leastNum + '份' + (shipVal / 100) + '元';
            }
        }
        return displayName;
    }
    var showDisplayName = function($shipSetting){
        var displayName = getDisplayName($shipSetting);
        $('.display-name', $shipSetting).text(displayName);
    }

    $saveBtn.on('click',function(){
        var postData = [];
        $('.ship-type:checked').each(function(){
            var $item = $(this).parents('.ship-setting');
            postData.push({"data_id": dataId, "data_type": dataType, "ship_type":$(".ship-type", $item).val(),"ship_val":$(".ship-val", $item).val(),'least_num':$(".least-num", $item).val()});
        });
        $.post('/manage/admin/ship_setting/save', {data: postData, dataId: dataId, dataType: dataType},function(data){
            if(data['success']){
                alert('保存成功');
            }
            else{
                alert('对不起，保存失败');
            }
        },'json');
    });

    $shipSettingAddBtn.on('click', function(){
        $(".ship-setting-sfdf").before(shipSettingZitiTemplate);
    });

    $('.ship-setting input').on('change', function(){
        showDisplayName($(this).parents('.ship-setting'));
    });
    $('.ship-setting').each(function(){
        showDisplayName($(this));
    });

    if($('.ship-setting-kuaidi').length < 1){
        $(".ship-setting-sfdf").before(shipSettingZitiTemplate);
        $('.ship-val', $('.ship-setting-kuaidi')).val(1500);
        $('.least-num', $('.ship-setting-kuaidi')).val(1);
    }
});