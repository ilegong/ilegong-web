/**
 * Created by shichaopeng on 6/6/15.
 */
$(function () {

    var $table = $('#week_order_table');
    var $tableDatas = $('tr',$table);
    var categories = [];
    var new_order_user = [];
    var order_user = [];
    var repeat_buy_user = [];
    var repeat_buy_ratio = [];
    var max_order = [];
    var avg_order = [];
    var all_order = [];
    var tuan_order = [];
    var new_user = [];

    $tableDatas.each(function(index,item){
        var $trData = $(item);
        var $tdDatas = $('td',$trData);
        categories.push($($tdDatas.get(0)).text());
        new_order_user.push(parseInt($($tdDatas.get(1)).text()));
        order_user.push(parseInt($($tdDatas.get(2)).text()));
        repeat_buy_user.push(parseInt($($tdDatas.get(3)).text()));
        repeat_buy_ratio.push(parseFloat($($tdDatas.get(4)).text()));
        max_order.push(parseInt($($tdDatas.get(5)).text()));
        avg_order.push(parseFloat($($tdDatas.get(7)).text()));
        all_order.push(parseInt($($tdDatas.get(8)).text()));
        tuan_order.push(parseInt($($tdDatas.get(9)).text()));
        new_user.push(parseInt($($tdDatas.get(12)).text()));

    });

    $('#chart-container').highcharts({
        title: {
            text: '每周订单汇总',
            x: -20 //center
        },
        subtitle: {
            text: '',
            x: -20
        },
        xAxis: {
            categories: categories.reverse()
        },
        yAxis: {
            title: {
                text: '份'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: '份'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: '新下单用户',
            data: new_order_user.reverse()
        }, {
            name: '下单用户',
            data: order_user.reverse()
        }, {
            name: '重复购买用户',
            data: repeat_buy_user.reverse()
        }, {
            name: '复购率',
            data: repeat_buy_ratio.reverse()
        },{
            name: '峰值订单',
            data: max_order.reverse()
        },{
            name: '平均订单',
            data: avg_order.reverse()
        },{
            name: '所有订单',
            data: all_order.reverse()
        },{
            name: '团购订单',
            data: tuan_order.reverse()
        },{
            name: '新增用户',
            data: new_user.reverse()
        },]
    });
});