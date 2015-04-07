$(function(){
    var tuanTeams = $('.tuan-teams');
    var tuanProducts = $('.tuan-products');
    var timeType=$('.time-type');
    var tuanType=$('.tuan-type');
    var leftSelectData = [];
    var tuan_name = $('#tuan_name');
    function setVal(){
        timeType.val(timeType.attr('data-time-type'));
        tuanType.val(tuanType.attr('data-tuan-type'));
    }
    $.getJSON('/manage/admin/tuan/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
        search_tuanteam();
        tuanTeams.val(tuanTeams.attr('data-team-id'));
    });

    $.getJSON('/manage/admin/tuan/api_tuan_products',function(data){
        tuanProducts.data('tuan-products', data);
        $.each(data,function(index,item){
            $('<option value="' + index + '">' + item + '</option>').appendTo(tuanProducts);
        });
        setVal();
        tuanProducts.val(tuanProducts.attr('data-product-id'));
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
    var tuanbuyingdelaymsg = $('.tuan-buying-delayemsg');
    var tuanBuyingStartDeliver = $('.tuanbuying-start-deliver');

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
//            $.getJSON('/manage/admin/tuan/api_tuan_buying_finished',function(data){
//                alert(data.success);
//            });
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
        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_create_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });
    tuanbuyingcancelmsg.on('click',function(e){
        if(!confirm('静哥,确定要发送取消团购模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_fail_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });

    tuanbuyingcompletemsg.on('click',function(e){
        var tuanStatus = $(this).attr('data-tuanBuying-id');
        if(tuanStatus == 1){
        if(!confirm('静哥,确定要发送模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_complete_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
        }else{
            alert('只有团购截止后，才能发送团购完成模版消息');
        }
    });

    tuanbuyingtipmsg.on('click',function(e){
        if(!confirm('静哥,确定要发送模板消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_tip_by_id_msg',{'tuan_buy_id':tuanBuyingId},function(data){
            alert(data['msg']);
        });
    });
    tuanBuyingStartDeliver.on('click',function(){
        if(!confirm('静哥，确定发送开始配送模版消息吗?')){
            return;
        }
        var tuanBuyingId = $(this).data('id');
        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_start_deliver_msg',{'tuan_buying_id':tuanBuyingId},function(data){
           alert(data.msg);

        });
        window.location.reload();
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

    function search_tuanteam(){

    String.prototype.Trim = function() {
        return this.replace(/(^\s*)|(\s*$)/g, "");
    };

    $("select[name='team_id'] option").each(function(){
        leftSelectData.push({'val':$(this).val(),'name':$(this).text()});
    });
    if(navigator.userAgent.indexOf("MSIE")>0){
        tuan_name.on('onpropertychange',txChange);
    }else{
        tuan_name.on('input',txChange);
    }
    }
    function txChange(){
        var content= tuan_name.val().Trim();
        tuanTeams.empty();
        if(content == ''){
            $.each(leftSelectData,function(index,value){
                tuanTeams.append('<option value="'+value['val']+'">'+value['name']+'</option>');
            });
        }else{
            var reg = new RegExp(content,'i');
            $.each(leftSelectData,function(index,val){
                if(reg.test(val['name'])){
                    tuanTeams.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
                }
            })
        }
    }

});