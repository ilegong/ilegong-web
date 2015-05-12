$(document).ready(function(){
    $('.tuijian_btn').click(function(){
        var tls = $('.tipslayer_share, .tipslayer_bg');
        tls.show().click(function(){
            tls.hide();
        });
    });
    $('div.good a.exchangebtn').on('click',function(){
        var me = $(this);
        var awardId = me.data('id');
        $.post('/refer/exchange_award/'+awardId,function(data){

        },'json')
    });
});