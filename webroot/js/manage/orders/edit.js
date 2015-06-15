$(document).ready(function () {
    var $form = $('.order-form');
    var $saveBtn = $('.save-btn');
    var $status = $('.status');
    var $shipMark = $('.ship-mark');
    var $consigneeId = $('.consignee-id');
    var $consigneeName = $('.consignee-name');
    var $consigneeMobilephone = $('.consignee-mobilephone');
    var $consigneeAddress = $('.consignee-address');
    var $remarkAddress = $('.remark-address');
    var $sendDate = $('.send-date');
    var $modifyReason = $('.modify-reason');
    var $modifyUser = $('.modify-user');
    var yesterday = function(){
        var date = new Date();
        date.setDate(date.getDate() - 1);
        return date ;
    }

    $shipMark.on('change', function () {
        $consigneeId.parents('.form-group').toggleClass('hidden', $shipMark.val() != 'ziti');
        $remarkAddress.parents('.form-group').toggleClass('hidden', $shipMark.val() != 'ziti');
    });

    $saveBtn.on('click', function(){
        var invalidFields = [];
        if($sendDate.data('value') != $sendDate.val() && new Date($sendDate.val()) <= yesterday()){
            invalidFields.push($sendDate);
        }
        if(_.isEmpty($consigneeName.val())){
            invalidFields.push($consigneeName);
        }
        if(!iUtils.isMobileValid($consigneeMobilephone.val())){
            invalidFields.push($consigneeMobilephone);
        }
        if(_.isEmpty($consigneeAddress.val())){
            invalidFields.push($consigneeAddress);
        }
        if($shipMark.val() == 'ziti'){
            if(_.isEmpty($consigneeId.val())){
                invalidFields.push($consigneeId);
            }
        }
        if(_.isEmpty($modifyReason.val())){
            invalidFields.push($modifyReason);
        }
        if(_.isEmpty($modifyUser.val())){
            invalidFields.push($modifyUser);
        }

        $('.form-group').removeClass('has-error');
        _.each(invalidFields, function(field){
            field.parents('.form-group').addClass('has-error');
        });
        if(!_.isEmpty(invalidFields)){
            return false;
        }

        var json = {};
        $('.form-group .form-control').each(function(){
            var $field = $(this);
            if($field.val() != $field.data('value')){
                json[$field.attr('name')] = $field.val();
            }
        });

        var reasons = {
            'order_not_exists': '订单不存在',
            'fields_are_empty': '然而你并没有修改任何订单',
            'no_permission': '您没有权限',
            'invalid_send_date': '发货时间有误',
            'failed_to_save_send_date': '保存发货时间失败',
            'missed_consignee_id': '请输入自提点',
            'failed_to_save_order': '保存订单失败'
        }
        var orderId = $('.order-id').val();
        $.post('/manage/admin/orders/update2/' + orderId + ".json", json, function(data){
            if(data.success){
                alert('修改成功: 订单号' + orderId);
            }
            else{
                alert('修改失败: ' + reasons[data.reason]);
            }
        }, 'json');
    });

    $consigneeId.parents('.form-group').toggleClass('hidden', $shipMark.val() != 'ziti');
    $remarkAddress.parents('.form-group').toggleClass('hidden', $shipMark.val()  != 'ziti');
});