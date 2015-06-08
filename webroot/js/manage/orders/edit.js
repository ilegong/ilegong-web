$(document).ready(function () {
    var $form = $('.order-form');
    var $saveBtn = $('.save-btn');
    var $orderStatus = $('.order-status');
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

        $.post('/manage/admin/orders/update2/' + $('.order-id').val() + ".json", json, function(data){
            if(data.success){
                alert('修改成功: ' + data.reason);
            }
            else{
                alert('修改失败: ' + data.reason);
            }
        });
    });

    $consigneeId.parents('.form-group').toggleClass('hidden', $shipMark.val() != 'ziti');
    $remarkAddress.parents('.form-group').toggleClass('hidden', $shipMark.val()  != 'ziti');
});