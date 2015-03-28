$(function(){
    var tuanTeams = $('.tuan-teams');
    $.getJSON('/manage/admin/tuan/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
    });
    var tuanProducts = $('.tuan-products');
    $.getJSON('/manage/admin/tuan/api_tuan_products',function(data){
        tuanProducts.data('tuan-products', data);
        $.each(data,function(index,item){
            $('<option value="' + index + '">' + item + '</option>').appendTo(tuanProducts);
        });
    });

    var tuanBuyingForm = $('.tuan-buying-form');
    var tuanBuyingDue= $('.tuan-buying-due');
    var tuanBuyingCanceled= $('.tuan-buying-canceled');
    var tuanBuyingFinished = $('.tuan-buying-finished');
    var tuanBuyingRefunded = $('.tuan-buying-refunded');
    var tuanBuyingSendmsg = $('.tuan-buying-sendmsg');
    var tuanbuyingcancelmsg = $('.tuan-buying-cancelmsg');

    var tuanbuyingcompletemsg = $('.tuan-buying-completemsg');
    var tuanbuyingtipmsg = $('.tuan-buying-tipmsg');

    tuanBuyingDue.click(function(){
        if(!confirm('确定结束吗？')) {
            return;
        }
        var tuanBuyingId = $(this).parents('tr').data('id');
        $.post( "/manage/admin/tuan/api_tuan_buying_due", {id: tuanBuyingId}, function( data ) {
            console.log('已设置为：团购截止！');
        }).fail(function(){
            console.log('设置团购截止失败！');
        }).always(function(){
            window.location.reload();
        });
    });
    tuanBuyingCanceled.click(function(){
        if(!confirm('确定取消吗？')) {
            return;
        }
        var tuanBuyingId = $(this).parents('tr').data('id');
        $.post( "/manage/admin/tuan/api_tuan_buying_canceled", {id: tuanBuyingId}, function( data ) {
            console.log('已设置为：团购取消！');
        }).fail(function(){
            console.log('设置团购取消失败！');
        }).always(function(){
            window.location.reload();
        });
    });
    tuanBuyingFinished.click(function(){
        if(!confirm('确定发货完成了吗？')) {
            return;
        }
        var tuanBuyingId = $(this).parents('tr').data('id');
        $.post( "/manage/admin/tuan/api_tuan_buying_finished", {id: tuanBuyingId}, function( data ) {
            console.log('已设置为：发货完成！');
        }).fail(function(){
            console.log('设置发货完成失败！');
        }).always(function(){
            window.location.reload();
        });
    });
    tuanBuyingRefunded.click(function(){
        if(!confirm('确定完成退款了吗？')) {
            return;
        }
        var tuanBuyingId = $(this).parents('tr').data('id');
        $.post( "/manage/admin/tuan/api_tuan_buying_refunded", {id: tuanBuyingId}, function( data ) {
            console.log('已设置为：退款完成！');
        }).fail(function(){
            console.log('设置退款完成失败！');
        }).always(function(){
            window.location.reload();
        });
    });

    tuanBuyingSendmsg.on('click',function(e){
        if(!confirm('静哥,确定要发送模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/cron/send_tuan_buy_create_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });
    tuanbuyingcancelmsg.on('click',function(e){
        if(!confirm('静哥,确定要发送取消团购模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/cron/send_tuan_buy_fail_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });

    tuanbuyingcompletemsg.on('click',function(e){
        if(!confirm('静哥,确定要发送模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/cron/send_tuan_buy_complete_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });

    tuanbuyingtipmsg.on('click',function(e){
        if(!confirm('静哥,确定要发送模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/cron/send_tuan_buy_tip_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });


    $('#tuan_down,#tuan_product_down').click(function(){
        var id = $(this).attr('data-id');
        var val=$(this).attr("value");

        if(confirm('确定编辑吗？')){
            if($(this).attr('id')== 'tuan_product_down'){
                confirm('团购取消后请及时退款哦');
            }
            var data={"id":id,"val":val};
            $.ajax({
                type:'post',
                success:function(data){
                    window.location.reload();
                    alert('状态修改成功');
                },
                error:function(e){alert(e);},
                url:"{{$this->Html->url(array('controller'=>'tuan','action'=>'admin_tuan_buying_set'))}}",
                data:data,
                dataType:'json'
            })
        }
        else{
            return false;
        }
    });
});