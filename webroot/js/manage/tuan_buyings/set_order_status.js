/**
 * Created by algdev on 15/4/14.
 */
$(function(){
    var orderIds = $('#order_id');
    var orderStatus = $('#order_status');
    $('#set_order_status').on('click',function(e){
        e.preventDefault();
       var orderid = $.trim(orderIds.val());
       var orderstatus = orderStatus.val();
        if(orderid==''){
           bootbox.alert('订单ID不能为空');
        }else{
            $.post('/manage/admin/tuan_buyings/set_status',{'tuan_orderid':orderid,'order_status':orderstatus},function(data){
               var result = JSON.parse(data);
                if(result.success){
                    bootbox.alert(result.msg);
                }else{
                    bootbox.alert(result.msg);
                }

           });
        }
    });
});
