$(document).ready(function(){
    $('.tuijian_btn').click(function(){
        var tls = $('.tipslayer_share, .tipslayer_bg');
        tls.show().click(function(){
            tls.hide();
        });
    });
});