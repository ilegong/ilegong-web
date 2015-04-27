$(document).ready(function(){
  var tuanTeams = $('.tuan-teams');
  var tuanProducts = $('.tuan-products');
  var products = $('.products');
  var start_stat_date = $('input[name="start_stat_datetime"]');
  var end_stat_date = $('input[name="end_stat_datetime"]');
  var tuan_con_date = $('input[name="tuan_con_date"]');
  var product_con_date = $('input[name="product_con_date"]');
  start_stat_date.datetimepicker({
    format: 'yyyy-mm-dd hh:ii'
  });
  end_stat_date.datetimepicker({
    format: 'yyyy-mm-dd hh:ii'
  });
  tuan_con_date.datetimepicker({
    format: 'yyyy-mm-dd'
  });
  product_con_date.datetimepicker({
    format: 'yyyy-mm-dd'
  });
  $.getJSON('/manage/admin/tuanTeams/api_tuan_teams',function(data){
    var values = [{'val': -1, 'name': '请选择团队'}];
    $.each(data,function(index,item){
      $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
      values.push({'val': item['id'], 'name': item['tuan_name']});
    });

    setSelectBoxValue(tuanTeams);
    initSearchBox($('.tuan-teams-search'), values);
  });
  $.getJSON('/manage/admin/tuanProducts/api_tuan_products',function(data){
    var values = [{'val': -1, 'name': '请选择商品'}];
    $.each(data,function(index,item){
      var tuan_product = item['TuanProduct'];
      $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo(tuanProducts);
      values.push({'val': tuan_product['product_id'], 'name': tuan_product['alias']});
    });

    setSelectBoxValue(tuanProducts);
    initSearchBox($('.tuan-product-search'), values);
  });
  $.getJSON('/manage/admin/tuanProducts/api_products',function(data){
    var values = [{'val': -1, 'name': '请选择商品'}];
    $.each(data,function(productId, item){
      var name = item['isTuanProduct'] ? item['TuanProduct']['alias'] : item['ProductTry']['product_name'] + '(' + item['ProductTry']['spec'] + ')';
      if(item['isTuanProduct'] && item['isProductTry']){
        name = name + "[团，秒]";
      }
      else if (item['isTuanProduct']){
        name = name + "[团]";
      }
      else if(item['isProductTry']){
        name = name + "[秒]";
      }

      $('<option value="' + productId + '">' + name + '</option>').appendTo(products);
      values.push({'val': productId, 'name': name});
    });

    setSelectBoxValue(products);
    initSearchBox($('.product-search'), values);
  });

  String.prototype.Trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
  };

  function initSearchBox(searchBox, values){
    if(navigator.userAgent.indexOf("MSIE")>0){
      searchBox.on('onpropertychange',function(){onSearchBoxChanged($(this), values)});
    }else{
      searchBox.on('input',function(){onSearchBoxChanged($(this), values)});
    }
  }
  function onSearchBoxChanged(searchBox, values){
    var content= searchBox.val().Trim();
    var selector = $("#" + searchBox.data('search-for'));
    selector.empty();
    if(content == ''){
      $.each(values,function(index,value){
        selector.append('<option value="'+value['val']+'">'+value['name']+'</option>');
      });
    }else{
      var reg = new RegExp(content,'i');
      $.each(values,function(index,val){
        if(reg.test(val['name'])){
          selector.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
        }
      })
    }
  }

  $(".nav-tabs a").click(function(){
    var tab = $(this).data('tab');
    activateTab(tab);
  });

  function activateTab(tab){
    $(".nav-tabs a").parents('li').removeClass('active');
    $(".nav-tabs a[data-tab=" + tab + "]").parents('li').addClass('active');

    $('.tab-pane').removeClass('active');
    $('#' + tab).addClass('active');
  };

  function setSelectBoxValue(selectBox){
    var selectBoxValue = selectBox.data('value');
    $("option", selectBox).each(function(){
      if($(this).val() == selectBoxValue){
        $(this).attr('selected', 'selected');
      }
      else{
        $(this).removeAttr('selected');
      }
    });
  }
  activateTab($('.nav-tabs').data('query-type'));
  setSelectBoxValue($('.order-status'));
});