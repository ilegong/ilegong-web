$(document).ready(function(){
    try {

        _path_exchange = _path_exchange || '/apple_201410/exchange_coupon';
        _path_shake = _path_shake || '/apple_201410/shake';
        _path_notify = _path_notify || '/apple_201410/notifiedToMe';
        _path_assign_follow = _path_assign_follow || '/apple_201410/assignWXSubscribeTimes';
        _path_query = _path_query || '/apple_201410/hasNewTimes';
        _mobile_bind_reason = _mobile_bind_reason || '';

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

    if (typeof(get_coupons_button) != 'undefined') {
        get_coupons_button.add($('#get_coupons_button'));
    } else {
        var get_coupons_button = $('#get_coupons_button');
    }

    $share_div = $('<div class="apple_share fade in"><img style="width:80%; position: absolute; top: 0; right: 0" src="'+share_png_url+'"></div>')
    .hide().click(function(){
    $share_div.hide();
    });
    $('body').append($share_div);

    if (yao_tips_png) {
        $shaking_tips_png = $('<div class="apple_share fade in" style="text-align: center; padding-top: 2em;"><img style="width:80%;margin-top: 40%;" src="' + yao_tips_png + '"></div>')
            .hide().click(function () {
                $shaking_tips_png.hide();
            });
        $('body').append($shaking_tips_png);

        if (game_user_total == 0) {
            $shaking_tips_png.show();
        }
    }


    $('#share_btn_2, #share_btn_3').add($shareBtn).click(function(){
    showShareAndChangeTitle();
    });

    function changeTitle(total) {
        if (typeof(game_page_title) == 'function') {
            var title = game_page_title(total);
            if (title) {
                document.title = title;
            }
        }
    }

    function showShareAndChangeTitle() {
    changeTitle($appleGotCnt.text());
    $share_div.show();
    }

        changeTitle($appleGotCnt.text());

    var showNoMoreTimesDialog = false;

    function total_not_spent() {
        var total = $.trim($('#apple_got_cnt').text());
        return (total != '' ? parseInt(total) : 0);
    }

    function showNoMoreTimes() {
        if (showNoMoreTimesDialog == true) {
            return;
        }

        var not_sure_subscribed = !(typeof(user_subscribed) != 'undefined' && user_subscribed);
        var $message = (!$today_got_wx && not_sure_subscribed && total_not_spent() > 15 ) ? '关注朋友说每天增加2次机会' : '机会已用完，分享给你的朋友们，每个朋友点击过来就增加<span class="apple_numbers">1</span>次机会！';

        showNoMoreTimesDialog = true;
        bootbox.dialog({
            message: $message,
            closeButton: false,
            buttons: {
                main: {
                    label: "取消",
                    className: "btn-default",
                    callback: function () {
                        showNoMoreTimesDialog = false
                    }
                },
                danger: {
                    label: $today_got_wx ? "立即分享" : "领取机会",
                    className: "btn-danger",
                    callback: function () {
                        if ($today_got_wx) { showShareAndChangeTitle(); }
                        else { try_wx_subscribe_times(); }
                        showNoMoreTimesDialog = false;
                    }
                }
            }
        }).css({
            'top': '50%',
            'margin-top': function () {
                return -($(this).height() / 2);
            }
        }).find('div.modal-footer').css({'text-align': 'center'});
    }

    function updateViewState(times, total) {
        if (times > 0) {
            $shakeBtn.show();
//            $shareBtn.hide();
            $('#assignWXSubscribeTimes_2').hide();
        } else {
            $shakeBtn.hide();
//            $shareBtn.show();
            if (!$today_got_wx) {
                $('#assignWXSubscribeTimes_2').show();
            }
        }
        if (total && total > 0) {
            changeTitle(total);
        }
    }

    function coupon_message(times, total) {
        if (typeof(game_coupon_message) == 'function') {
            return game_coupon_message(times, total);
        } else {
            return '';
        }
    }

    function showAfterGot($got,times, total, need_login, timeout, $need_mobile) {
    var close_callback = times > 0 ? null : showNoMoreTimes;
    if (need_login) {
        close_callback = function() {
            window.location.href = '/users/login?force_login=true&auto_weixin=1&referer='+encodeURIComponent(location.href);
        };
    }

        if ($need_mobile) {
            window.location.href = '/users/to_bind_mobile?ref_url='+encodeURIComponent(location.href)+'&reason='+encodeURIComponent(_mobile_bind_reason)+'&from=game_jiujiu';
            return;
        }

    var msg;
    if ($got > 0) {
        msg = '恭喜你摇掉了<span class="apple_numbers">' + $got + '</span>个' + game_obj_name + '！'+ coupon_message(times, total);
        timeout = 3000;
        if (need_login) {
            msg += '<br/> 亲，您的成绩超过了大多数用户！请您先登录。';
        }
    } else {
        timeout = 5000;
        msg = '力气太小啦！只薅下了几根羊毛毛！'+coupon_message(times, total)+'';
    }

    if (typeof notify_after_shake == 'function') {
        notify_after_shake(total, times, msg, close_callback, timeout);
    } else {
        utils.alert_one(msg, '继续摇', close_callback, {timeout: timeout, close_callback: close_callback});
    }

    updateViewState(times, total);
       if (typeof('showAfterGotCallback')) {
           showAfterGotCallback(times, total, $got);
       }
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
            $('#apple_tree').octoberLeaves('start', {'speedC': 1, 'numberOfLeaves': 100, 'cycleSpeed': 80, 'rotationTrue': 0});
            sound.play();
            $appleTree.attr('src', shake_pic_url);

            setTimeout(function () {
                $('#apple_tree').octoberLeaves('stop');
                $.getJSON(_path_shake + '/' + game_type + '?r=' + Math.random(), function (data) {
                    var $curr_got = 0;
                    if (data && data.success) {
                        $appleGotCnt.text(data['total_apple']);
                        $riceGotCnt.text(data['total_apple'] * 10);
                        $appleTimesLeft.text(data['total_times'] < 0 ? 0 : data['total_times']);
                        $curr_got = data['got_apple'];
                        showAfterGot($curr_got, data['total_times'], data['total_apple'], data['need_login'], 2000, data['need_mobile']);
                    } else if (data.msg == 'incorrect_type') {
                        utils.alert('游戏类型错误', function () {
                            location.href = '/apple_201410/index.html';
                        });
                    } else if (data.msg == 'game_end') {
                        utils.alert('活动已结束！');
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
        $.getJSON(_path_notify + "/"+game_type+"?r="+Math.random(), function(data){
            var msg;
            if (data.notified == false) {
                if (data.notify_type > 0) {
                    msg = '您为<span style="color:red">' + data.name + '</span>获得了<span class="apple_numbers">1</span>次摇' + game_obj_name + '的机会！';
                } else if (data.notify_type == -1) {
                    msg = '您今天帮助过太多人了，明天再帮吧';
                } else if (data.notify_type == -2) {
                    msg = '您已经帮这个朋友点过啦！';
                } else {
                    //msg ＝ '您还没帮过这个朋友';
                    //Don't show
                }
            }
            if (msg) { utils.alert(msg); }
    });

    function try_wx_subscribe_times() {
        $.getJSON(_path_assign_follow + "/" + game_type + "?r=" + Math.random(), function (data) {
            if (data.result == "not-sub") {
                utils.alert("您还没有关注我们的服务号，按<a href=\"'+wx_follow_url+'\">关注指南</a>关注【朋友说】，就可以来领取啦");
            } else if (data.result == 'got') {
                utils.alert_one("已于" + data.got_time + "领取过啦，请明天再来领取。");
//                    disable_wx_times(data.got_time);
                $today_got_wx = 1;
            } else if (data.result == 'just-got') {
                utils.alert("领取成功！您现在有<span class='apple_numbers'>" + data['total_times'] + "</span>次机会");
                $appleTimesLeft.text(data['total_times'] < 0 ? 0 : data['total_times']);
//                    disable_wx_times();
                $today_got_wx = 1;
            } else if (data.result == 'retry') {
                utils.alert("领取失败，可能是网络状态不好，请稍候重试！");
            } else {
                utils.alert('可能是网络状态不好，请稍候重新领取！');
            }
        });
    }
    var $assignWXSubscribeTimes = $('#assignWXSubscribeTimes, #assignWXSubscribeTimes_2');
    $assignWXSubscribeTimes.click(function () {
        try_wx_subscribe_times();
    });

        /**
         * @param coupon_type  empty means no expect
         * @param reduce_cnt
         */
    function get_coupon_click(coupon_type, reduce_cnt) {
        var apple_count = $.trim($appleGotCnt.text());
        if (parseInt(apple_count) < parseInt(game_least_change)) {
            utils.alert("加油，我们<span class='apple_numbers'>" + game_least_change + "</span>个" + game_obj_name + "起兑喔，您目前已摇<span class='apple_numbers'>"
                + apple_count + "</span>个。加油加油！");
            return;
        }
        var reduce_desc = typeof(reduce_cnt) != 'undefined' && reduce_cnt ? reduce_cnt + '个' : '相应的数目';
        bootbox.confirm('您目前有'+apple_count+'个' + game_obj_name + '，兑换成功会扣除'+reduce_desc+'，您确定要兑换吗？', function (result) {
            if (!result) {
                return;
            }
            var coupon_url = _path_exchange + '/' + game_type + "?";
            if (typeof coupon_type != 'undefined' && coupon_type) {
                coupon_url += 'expect=' + coupon_type;
            }
            $.getJSON(coupon_url + "&r=" + Math.random(), function (data) {
                if (data.result == "just-got") {
                    var exchange_apple_count = data.exchange_apple_count;
                    var coupon_count = data.coupon_count;
                    $appleGotCnt.text(apple_count - exchange_apple_count);
                    $riceGotCnt.text((apple_count - exchange_apple_count) * 10);
                    if (typeof(game_notify_after_exchange) == 'function') {
                        game_notify_after_exchange(coupon_count, coupon_type, data['award_data']);
                    } else {
                        utils.alert_one("恭喜，兑换了" + coupon_count + "张优惠券!", '查看我的优惠券', function(){
                            window.location.href = '/users/my_coupons.html';
                        });
                    }
                } else if (data.result == 'sold_out'){
                    if (typeof(game_notify_after_exchange) == 'function') {
                        game_notify_after_exchange(coupon_count, coupon_type);
                    } else {
                        utils.alert("呜呜，券已兑完。");
                    }
                } else if (data.result == 'game_end') {
                    utils.alert("呜呜，活动已结束。");
                } else {
                    if (data.reason == 'need_mobile') {
                        window.location.href = '/users/to_bind_mobile?ref_url='+encodeURIComponent(location.href)+'&reason='+encodeURIComponent('使用时优惠券会校验手机号， 请设置您的手机号')+'&from=game_xiyang';
                        return;
                    }
                    utils.alert("呜呜，兑换失败，请稍后重试。");
                }
            });
        });
    }

    get_coupon_click_func = get_coupon_click;

    get_coupons_button.click(function(){
        get_coupon_click();
    });

        var query_interval = 20000;

        function new_times_query() {
            $.getJSON(_path_query + '/' + game_type + '?r=' + Math.random(), function (data) {
                if (data.success && data.new_times > 0) {
                    var times = currTimes() + data.new_times;
                    $appleTimesLeft.text(times);
                    updateViewState(times);
                    utils.alert('您的朋友<span style="color:red">' + data.nicknames + '</span>为您获得了<span class="apple_numbers">' + data.new_times + '</span>次摇' + game_obj_name + '的机会！', function () {
                        setTimeout(new_times_query, query_interval);
                    });
                } else {
                    setTimeout(new_times_query, query_interval);
                }

                if (data.total_help_me) {
                    $('#helpme_cnt_n').text(data.total_help_me);
                }

                if (data.top_list && typeof(update_top_list) == 'function') {
                    update_top_list(data.top_list);
                }

                if (data.update_award_list && typeof(update_award_list) == 'function') {
                    update_award_list(data.award_list);
                }

                if (typeof update_after_times_query == 'function') {
                    update_after_times_query(data);
                }
            });
        }

        setTimeout(new_times_query, query_interval);

    } catch (e) {
    TraceKit.report(e);
    }
    });