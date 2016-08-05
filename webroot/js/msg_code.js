/**
 * Created by ldy on 14/12/15.
 */
// 验证短信和图片验证码
$().ready(function() {
    $('#reginfo_a').on('submit', function () {
        $('#registerformsubmit').button('loading');
    });
    //刷新验证码
    $('#image_code').click(function () {
        document.getElementById('image_code').src = '/check/captcha.html?' + Math.random() * 10000;
    });
    //获取手机验证码，显示提示信息
    $('#btnMobileCode').click(function () {
        var phone_num = $("#mobile_number").val();
        var key_str = $("#J_CheckCodeInput").val();
        $("#sendInfo").html("");
        $("#checkInfo").html("");
        var f = "/check/message_code";
        var countdown = setInterval(CountDown, 1000);
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: f,
            data: {mobile: phone_num, keyString: key_str},
            cache: !1,
            success: function (a) {
                if (a.error == 0) {
                    $("#sendInfo").html('<em>验证短信已发出，'+ a.timelimit +'之前有效，未收到可再获取</em>');
                } else if (a.error == 1) {
                    $("#checkInfo").html("<em>验证码错误，验证短信发送失败，请重新输入</em>").addClass('help-inline ');
                    resetGetMsgCodeBtn();
                }else if (a.error == 2) {
                    $("#checkInfo").html("<em>手机号码格式错误，验证短信发送失败，请重新输入</em>").addClass('help-inline ');
                    resetGetMsgCodeBtn();
                }
                document.getElementById('image_code').src = '/check/captcha.html?' + Math.random() * 10000;
            }
        });
        var count = 60;
        function CountDown() {
            $("#btnMobileCode").attr("disabled", true);
            $("#btnMobileCode").val("等待 " + count + " 秒重新获取");
            if (count == 0) {
               resetGetMsgCodeBtn();
            }
            count--;
        }
        function resetGetMsgCodeBtn(){
            $("#btnMobileCode").val("获取").removeAttr("disabled");
            clearInterval(countdown);
        }
    })
})