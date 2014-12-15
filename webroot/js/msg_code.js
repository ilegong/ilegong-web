/**
 * Created by ldy on 14/12/15.
 */
// 验证短信和图片验证码
$().ready(function() {
    //刷新验证码
    $('#image_code').click(function () {
        document.getElementById('image_code').src = '/Check/captcha.html?' + Math.random() * 10000;
    });
    //获取手机验证码，显示提示信息
    $('#btnMobileCode').click(function () {
        var phone_num = $("#mobile_number").val();
        var key_str = $("#J_CheckCodeInput").val();
        $("#sendInfo").html("");
        $("#checkInfo").html("");
        var f = "/check/message_code";
        var countdown = null;
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: f,
            data: {phoneNumbers: phone_num, keyString: key_str},
            cache: !1,
            success: function (a) {
                if (a.error == 0) {
                    $("#sendInfo").html('<em>验证短信已发出，'+ a.timelimit +'之前有效，未收到可再获取</em>');
                    countdown = setInterval(CountDown, 1000);
                } else if (a.check_error == 1) {
                    $("#checkInfo").html("<em>验证码错误，验证短信发送失败，请重新输入</em>").addClass('help-inline ');
                }
                document.getElementById('image_code').src = '/Check/captcha.html?' + Math.random() * 10000;
            }
        });
        var count = 30;
        function CountDown() {
            $("#btnMobileCode").attr("disabled", true);
            $("#btnMobileCode").val("等待 " + count + " 秒重新获取");
            if (count == 0) {
                $("#btnMobileCode").val("获取").removeAttr("disabled");
                clearInterval(countdown);
            }
            count--;
        }
    })
})