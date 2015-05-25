$(function () {
    var $container = $('#ship-view');
    //history data
    console.log(ship_setting_data);
    var $saveBtn = $('#save-ship-setting',$container);
    var $shipSettingItems = $('div.ship-setting-item',$container);

    $.each(ship_setting_data,function(id,item){
        var shipType = item['ship_type'];
        var shipVal = item['ship_val'];
        var shipLeastNum = item['least_num'];
        var $checkBox = $('[value="'+shipType+'"]:checkbox');
        $checkBox.prop('checked','checked');
        var $shipVal = $('[name="val"]',$checkBox.closest('div.ship-setting-item'));
        $shipVal.val(shipVal);
        var $shipLeastNum = $('[name="least_num"]',$checkBox.closest('div.ship-setting-item'));
        $shipLeastNum.val(shipLeastNum);
    });

    $saveBtn.on('click',function(){
        var postData = [];
        $shipSettingItems.each(function(index,item){
            var $item = $(item);
            var $shipType = $('input[name="ship_type"]',$item);
            var $shipVal = $('[name="val"]',$item);
            var $shipLeastNum = $('[name="least_num"]',$item);
            if($shipType.prop('checked')){
                postData.push({"shipType":$shipType.val(),"shipVal":$shipVal.val(),'shipLeastNum':$shipLeastNum.val()});
            }
        });
        $.post('/manage/admin/ship_setting/save',{"data":JSON.stringify(postData),"dataId":dataId,"dataType":dataType},function(data){
            if(data['success']){
                alert('保存成功');
            }
        },'json');
    });
});