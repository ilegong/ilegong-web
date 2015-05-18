/**
 * Created by shichaopeng on 5/18/15.
 */

$(document).ready(function(){

    $('a.send-wx-msg').on('click',function(e){
        e.preventDefault();
        var confirm = confirm('确定要推送消息吗？');
        if(confirm){
            var me = $(this);
            var dataId = me.data('id');
            var dataType = me.data('type');
            $.post('/manage/admin/WxSendMsg/send_wx_msg',{"data_id":dataId,"data_type":dataType},function(data){
                if(data['success']){
                    alert('推送成功');
                }else{
                    alert(data['reason']);
                }
            },"json");
        }
    });

});
