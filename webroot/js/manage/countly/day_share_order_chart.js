/**
 * Created by shichaopeng on 6/6/15.
 */
$(function () {
  var $table = $('#share-order-data-table');
  var $tableDatas = $('tr', $table);
  var categories = [];
  var orderCount = [];
  var totalPrice = [];

  $tableDatas.each(function (index, item) {
    var $trData = $(item);
    var $tdDatas = $('td', $trData);
    categories.push($($tdDatas.get(0)).text());
    orderCount.push(parseInt($($tdDatas.get(1)).text()));
    totalPrice.push(parseFloat($($tdDatas.get(2)).text()));
  });

  $('#chart-container').highcharts({
    title: {
      text: '每天订单汇总',
      x: -20 //center
    },
    subtitle: {
      text: '',
      x: -20
    },
    xAxis: {
      categories: categories
    },
    yAxis: {
      title: {
        text: ''
      },
      plotLines: [{
        value: 0,
        width: 1,
        color: '#808080'
      }]
    },
    tooltip: {
      valueSuffix: ''
    },
    legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle',
      borderWidth: 0
    },
    series: [{
      name: '订单数',
      data: orderCount
    }, {
      name: '成交额',
      data: totalPrice
    }]
  });
});