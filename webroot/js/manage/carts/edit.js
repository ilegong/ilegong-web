$(document).ready(function () {
    var $saveBtn = $('.save-btn');
    var $sendDate = $('.send-date');
    var $modifyUser = $('.modify-user');
    var yesterday = function(){
        var date = new Date();
        date.setDate(date.getDate() - 1);
        return date ;
    };
    $('.all_goods_shipped').on('click',function(){
        $('.status').each(function(){
            $(this).val("2")
        })
    });
    $('.status').on('change',function(){
        if($(this).val() != "2"){
           $('.all_goods_shipped').prop("checked", false);
        }
    });

    $saveBtn.on('click', function(){
        var invalidFields = [];
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
        var json = {"modify_user":$modifyUser.val()};
        $('.form-group .form-control').each(function(){
            var $field = $(this);
            if($field.val() != $field.data('value')){
                json[$field.attr('name')] = $field.val();
            }
        });
        var reasons = {
            'order_not_exists': '订单不存在',
            'cart_not_exists': '发货单不存在',
            'fields_are_empty': '然而你并没有修改任何订单',
            'no_permission': '您没有权限',
            'invalid_send_date': '发货时间有误',
            'invalid_status': '发货单状态只能修改为已发货、退款中、已退款',
            'failed_to_save_send_date': '保存发货时间失败',
            'missed_consignee_id': '请输入自提点',
            'failed_to_save_cart': '保存发货失败'
        };
        $.post('/manage/admin/carts/multi_update.json', json, function(data){
            if(data.success){
                alert('修改成功: 发货单号');
            }
            else{
                alert('修改失败: ' + reasons[data.reason]);
            }
        }, 'json');
    });
});