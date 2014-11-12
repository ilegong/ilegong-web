$(document).ready(function(){
    try {
    $([shake_pic_url]).preload();
    var sound = new Howl({
    urls: [sound_url]
    });
    var $appleGotCnt = $('#apple_got_cnt');
    var $riceGotCnt = $('#rice_got_cnt');
    var $appleTimesLeft = $('#apple_times_left, #apple_times_left_bottom');
    var $appleTree = $('img#apple_tree_img');
    var apple_tree_url = $('#apple_tree').attr('src');

    var $shakeBtn = $('#shake_btn');
    var $shareBtn = $('#share_btn');

    $share_div = $('<div class="apple_share fade in"><img src="'+share_png_url+'"></div>')
    .hide().click(function(){
    $share_div.hide();
    });

    $shaking_tips_png = $('#shaking_tips_pic');

    $('body').append($share_div);

    $('#share_btn_2').add($shareBtn).click(function(){
    showShareAndChangeTitle();
    });

    function changeTitle(total) {
    document.title = '摇一摇免费兑稻花香大米, 我已经兑到'+total*10+'g五常稻花香大米啦 -- 城市里的乡下人腾讯nana分享爸爸种的大米-朋友说';
    }

    function showShareAndChangeTitle() {
    changeTitle($appleGotCnt.text());
    $share_div.show();
    }

    var showNoMoreTimesDialog = false;
    function showNoMoreTimes() {
    if (showNoMoreTimesDialog == true) { return; }
    showNoMoreTimesDialog = true;
    bootbox.dialog({
    message: '机会已用完，分享给你的朋友们，每个朋友点击过来就增加<span class="apple_numbers">1</span>次机会！',
    buttons: {
    main: {
    label: "取消",
    className: "btn-default",
    callback: function(){showNoMoreTimesDialog = false}
    },
    danger: {
    label: "立即分享",
    className: "btn-danger",
    callback: function() {
    showShareAndChangeTitle();
    showNoMoreTimesDialog = false;
    }
    }
    }
    }).css({
    'top': '50%',
    'margin-top': function () {
    return -($(this).height() / 2);
    }
    }).find('div.modal-footer').css({'text-align':'center'});
    }

    function updateViewState(times, total) {
    if (times > 0) {
    $shakeBtn.show();
    $shareBtn.hide();
    $('#assignWXSubscribeTimes_2').hide();
    } else {
    $shakeBtn.hide();
    $shareBtn.show();
    if (!$got_wx_sub_times) {
    $('#assignWXSubscribeTimes_2').show();
    }
    }
    if (total && total > 0) {
    changeTitle(total);
    }
    }

    function showAfterGot($got,times, total, timeout) {
    var close_callback = times > 0 ? null : showNoMoreTimes;
    if ($got > 0) {
    utils.alert('恭喜你！你摇掉了<span class="apple_numbers">' + $got + '</span>个苹果！<br/><small>(2秒后自动消失)</small>', function(){}, timeout, close_callback);
    } else {
    utils.alert('你力气太小啦！只晃掉了几片树叶！<br/><small>(2秒后自动消失)</small>', function(){}, timeout, close_callback);
    }
    updateViewState(times, total);
    }


    function currTimes() {
    return parseInt($appleTimesLeft.first().text());
    }

    if (currTimes()>0 && $.trim($appleGotCnt.text()) == '0' ) {
//            $shaking_tips_png.css({
//                'width':$('body').innerWidth()
//            });
    $shaking_tips_png.show();
    }

    window.addEventListener('shake', shakeEventDidOccur, false);
    var shaking = false;
    function shakeEventDidOccur() {
    if (shaking) {
    return false;
    }
    var timesLeft = parseInt($appleTimesLeft.text());
    if (timesLeft <= 0) {
    showNoMoreTimes();
    return;
    }
    shaking = true;
    $shaking_tips_png.hide();
    $('#apple_tree').octoberLeaves('start', {'speedC': 1, 'numberOfLeaves':100, 'cycleSpeed':80, 'rotationTrue':0});
    sound.play();
    $appleTree.attr('src', shake_pic_url);

    setTimeout(function(){
    $('#apple_tree').octoberLeaves('stop');
    $.getJSON('/apple_201410/shake?r=' + Math.random(), function(data){
    var $curr_got = 0;
    if (data) {
    $appleGotCnt.text(data['total_apple']);
    $riceGotCnt.text(data['total_apple']*10);
    $appleTimesLeft.text(data['total_times'] < 0 ? 0 : data['total_times']);
    $curr_got = data['got_apple'];
    showAfterGot($curr_got, data['total_times'], data['total_apple'], 2000);
    }
    $appleTree.attr('src', apple_tree_url);
    shaking = false;
//                    if (currTimes() > 0) {
//                        $shaking_tips_png.show();
//                    }
    });
    }, 2000);
    }

    $shakeBtn.click(function(){
    utils.alert('摇动手机！没有声音请开声音！');
    });
        $.getJSON("/apple_201410/notifiedToMe?r="+Math.random(), function(data){
    if (data.notified === false) {
    var msg = data.got ? '您为<span style="color:red">'+data.name+'</span>获得了<span class="apple_numbers">1</span>次摇苹果的机会！' : '您已经帮这个朋友点过啦！';
    utils.alert(msg);
    }
    });

    var $assignWXSubscribeTimes = $('#assignWXSubscribeTimes, #assignWXSubscribeTimes_2');
    $assignWXSubscribeTimes.click(function(){
    $.getJSON("/apple_201410/assignWXSubscribeTimes?r="+Math.random(), function(data){
    if (data.result == "not-sub") {
    utils.alert("您还没有关注我们的服务号，按<a href=\"http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200769784&idx=1&sn=8cce5a47e8a6123028169065877446b9#rd\">关注指南</a>关注【朋友说】，就可以来领取啦");
    }  else if (data.result == 'got') {
    utils.alert("已于"+data.got_time+"领取过啦，请明天再来领取。");
//                    disable_wx_times(data.got_time);
    } else if (data.result == 'just-got') {
    utils.alert("领取成功！您现在有<span class='apple_numbers'>"+data['total_times']+"</span>次机会" );
    $appleTimesLeft.text(data['total_times'] < 0 ? 0 : data['total_times']);
//                    disable_wx_times();
    } else if (data.result == 'retry') {
    utils.alert("领取失败，可能是网络状态不好，请稍候重试！");
    } else {
    utils.alert('可能是网络状态不好，请稍候重新领取！');
    }
    });
    });

    var get_coupons_button =$('#get_coupons_button');

    get_coupons_button.click(function(){
    var apple_count = $.trim($appleGotCnt.text());
    if(apple_count<50){
    utils.alert("加油小主，我们<span class='apple_numbers'>"+50+"</span>个苹果起兑喔，您目前已摇<span class='apple_numbers'>"
    +apple_count+"</span>个。加油加油！");
    }else{
    $.getJSON("/apple_201410/exchange_coupon?r="+Math.random(), function(data){
    if (data.result == "just-got") {
    var exchange_apple_count = data.exchange_apple_count;
    var coupon_count = data.coupon_count;
    $appleGotCnt.text(apple_count-exchange_apple_count);
    $riceGotCnt.text((apple_count-exchange_apple_count)*10);
    utils.alert("恭喜，兑换"+coupon_count+"张粮票成功，<a href='/users/my_coupons.html' class='apple_medium_links'>查看我的优惠券</a>!");
    }  else {
    utils.alert("呜呜，兑换失败，请稍后重试。");
    }
    });
    }
    });

    var query_interval = 30000;
    function new_times_query() {
    $.getJSON('/apple_201410/hasNewTimes?' + Math.random(), function (data) {
    if (data.success && data.new_times > 0) {
    var times = currTimes() + data.new_times;
    $appleTimesLeft.text(times);
    updateViewState(times);
    utils.alert('您的朋友<span style="color:red">'+data.nicknames+'</span>为您获得了<span class="apple_numbers">' + data.new_times + '</span>次摇苹果的机会！', function () {
    setTimeout(new_times_query, query_interval);
    });
    } else {
    setTimeout(new_times_query, query_interval);
    }
    });
    }
    setTimeout(new_times_query, query_interval);

    } catch (e) {
    TraceKit.report(e);
    }
    });