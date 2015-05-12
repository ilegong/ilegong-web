$(document).ready(function(){
    var $tipInfo = $('#tip-info-panel');
    var $tipInfoMsg = $('#tip-info-msg',$tipInfo);
    var $closeTipInfo = $('a.layer_close',$tipInfo);
    var $tbg = $('.tipslayer_bg');
    var exchangeResult = false;
    var exchangeSuccsess = '<strong>兑换信息已提交，请耐心等待！</strong><br/>1个工作日内[朋友说]联系您。<br/>如有问题请咨询客服微信：killman';
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
            if(data['success']){
                $tipInfoMsg.html(exchangeSuccsess);
                exchangeResult = true;
            }else{
                $tipInfoMsg.html('<strong>'+data['reason']+'！</strong><br/>如有问题请咨询客服微信：killman');
            }
            $tbg.show();
            $tipInfo.show();
            $tbg.on('click',function(){
                $tbg.hide();
                $tipInfo.hide();
                if(exchangeResult){
                    window.location.reload();
                }
                return false;
            });
        },'json');
    });
    $closeTipInfo.on('click',function(){
        $tbg.hide();
        $tipInfo.hide();
        if(exchangeResult){
            window.location.reload();
        }
        return false;
    });
});