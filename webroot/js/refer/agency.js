/**
 * Created by shichaopeng on 6/5/15.
 */
$(document).ready(function () {
    var $preMonth = $('a.pre-month');
    var $dataDate = $('a.data-date');
    var $nexMonth = $('a.nex-month');
    var $curMonth = $('a.cur-month');
    var minDate = moment('2015-05').format('YYYY-MM');
    var maxDate = moment().format('YYYY-MM');

    var $my_refer_data = $('#my_refer_data');
    var $my_refer_user_data = $('#my_refer_user_data');
    var $my_refer_user_all_data = $('#my_refer_user_all_data');
    var $my_refer_id = $('#my_refer_id');
    var user_id = $my_refer_id.val();

    $preMonth.on('click', function () {
        var me = $(this);
        if (!me.hasClass('cur')) {
            return;
        }
        var currDate = $dataDate.text();
        var currentDate = moment(currDate).subtract(1, 'months').format('YYYY-MM');
        $dataDate.text(currentDate);
        checkCanClick();
        loadData(currentDate);
    });

    $nexMonth.on('click', function () {
        var me = $(this);
        if (!me.hasClass('cur')) {
            return;
        }
        var currDate = $dataDate.text();
        var currentDate = moment(currDate).add(1, 'months').format('YYYY-MM');
        $dataDate.text(currentDate);
        checkCanClick();
        loadData(currentDate);

    });

    $curMonth.on('click', function () {
        var currentDate = moment().format('YYYY-MM');
        $dataDate.text(currentDate);
        checkCanClick();
        loadData(currentDate);
    });
    function checkCanClick() {
        var currDate = $dataDate.text();
        if (minDate == currDate) {
            $preMonth.removeClass('cur');
        } else {
            $preMonth.addClass('cur');
        }
        if (maxDate == currDate) {
            $nexMonth.removeClass('cur');
        } else {
            $nexMonth.addClass('cur');
        }
    }

    function loadData(date) {
        var queryDate = date + '-01';
        $.getJSON('/refer/get_refer_statics_data/' + queryDate, function (data) {
            parseReferData(data);
        }, 'json');
    }

    function cleanData(){
        $my_refer_data.html('');
        $my_refer_user_all_data.html('');
        $my_refer_user_data.html('<li class="clearfix" id="to_refer_data"><a href="/refer/index/'+user_id+'" class="mytuijian_a">点击链接推荐好友</a></li>');
    }

    function parseReferData(data) {
        cleanData();
        var myData = data['my_data'];
        var secondReferData = data['second_refer_data'];
        $my_refer_data.html(myData['user_count'] + '人<br>' + (myData['total_money'] || 0) + '元');
        $my_refer_user_all_data.html(secondReferData['user_count'] + '人<br>' + (secondReferData['total_money'] || 0) + '元');
        var refer_user_data = data['refer_user_data'];
        var user_info_datas = data['user_infos'];
        $.each(refer_user_data, function (key, item) {
            var user_info = user_info_datas[key];
            var dataDom = '<li class="clearfix"> <span class="fl"><img onerror="this.onerror=null;this.src=\'http://51daifan.sinaapp.com/img/default_user_icon.jpg\'" src="' + user_info['image'] + '"></span> <div class="tuijian_title"> <div class="tuijian_title_style clearfix"> <em class="fl">'+user_info['nickname']+'</em> <b>推荐了' + item['user_count'] + '个新用户，新用户购买总金额：' + ((item['total_money']) || 0) + '元</b> </div> </div> </li>';
            $my_refer_user_data.prepend($(dataDom));
        });
    }

    loadData($dataDate.text());
});