$(function () {
    var $container = $('#ship-view');
    //history data
    console.log(ship_setting_data);
    var $saveBtn = $('#save-ship-setting',$container);
    var dataId = $container.data('product-id');
    var dataType = $container.data('type');

    $.each(ship_setting_data,function(id,item){
        var $checkBox = $('.ship-type[value="'+item['ship_type']+'"]');
        $item = $checkBox.parents('.ship-setting-item');

        $checkBox.prop('checked','checked');
        $('.ship-val', $item).val(item['ship_val']);
        $('.least-num', $item).val(item['least_num']);
    });

    $saveBtn.on('click',function(){
        var postData = [];
        $('input[name="ship_type"]:checked').each(function(){
            var $item = $(this).parents('.ship-setting-item');
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
});