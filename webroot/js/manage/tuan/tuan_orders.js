$(document).ready(function(){
  var products = $('.products');
  var start_stat_date = $('input[name="start_stat_datetime"]');
  var end_stat_date = $('input[name="end_stat_datetime"]');
  var tuan_con_date = $('input[name="tuan_con_date"]');
  var product_con_date = $('input[name="product_con_date"]');
  var mainContent = $('#mainContent');
  mainContent.height(250);
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
    var tuanTeamsBox = $('.tuan-teams');

    var values = [{'val': -1, 'name': '请选择团队'}];
    $.each(data,function(teamId, item){
      var tuanTeam = item['TuanTeam'];
      var ele = $('<option value="' + teamId + '">' + tuanTeam['tuan_name']+'</option>');
      ele.appendTo(tuanTeamsBox);
      ele.data('TuanBuyings', item['TuanBuyings']);
      values.push({'val': teamId, 'name': tuanTeam['tuan_name']});
    });

    setSelectBoxValue(tuanTeamsBox);
    initSearchBox($('.tuan-team-search'), values);
    tuanTeamsBox.each(function(){
      updateTuanBuyingSelectBox($("option:selected", $(this)));
    })
  });
  $.getJSON('/manage/admin/tuanProducts/api_tuan_products',function(data){
    var values = [{'val': -1, 'name': '请选择商品'}];
    $.each(data,function(index,item){
      var tuan_product = item['TuanProduct'];
      $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo($('.tuan-products'));
      values.push({'val': tuan_product['product_id'], 'name': tuan_product['alias']});
    });

    setSelectBoxValue($('.tuan-products'));
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
    $.getJSON('/manage/admin/tuanBuyings/api_offline_stores', function(data){
        var menu = {0:'所有好邻居自提点', 1: '所有自有自提点'};
        var offlineStoreBox = $('.offline_store');
        for(var category in data){
            $('<optgroup label="--------"><option value="" class="store_'+ category +'">' + menu[category] + '</option>').appendTo(offlineStoreBox );
            for(var addressId in data[category]){
                $('<option value="' + addressId + '">' + data[category][addressId].name + '</option>').appendTo(offlineStoreBox);
                var val =  $('.store_'+ category).val();
                $('.store_'+ category).val(addressId + ','+val);
            }
        }
        setSelectBoxValue(offlineStoreBox);
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
  function updateTuanBuyingSelectBox(selectedTuanTeam){
    var tuanBuyingsBox = $('.tuan-buyings');
    tuanBuyingsBox.empty();
    $('<option value="-1">请选择团购</option>').appendTo(tuanBuyingsBox);
    if(selectedTuanTeam.length <= 0 || typeof(selectedTuanTeam.data('TuanBuyings')) == 'undefined'){
      return;
    }

    $.each(selectedTuanTeam.data('TuanBuyings'),function(index,item){
      var tuanBuying = item['TuanBuying'];
      var tuanProduct = item['TuanProduct'];
      if(tuanBuying['status'] == 11 || tuanBuying['status'] == 21){
        return;
      }
      var consignmentType = tuanProduct['consignment_type'] == 0 ? '团满发货' : (tuanProduct['consignment_type'] == 1 ? '团满准备发货' : '排期')
      var status = '进行中'
      if(tuanBuying['status'] == 1){
        status = '已截单';
      }
      else if(tuanBuying['status'] == 2){
        status = '已取消';
      }
      var name = tuanProduct['alias'] + "(" + consignmentType + ", " + status + ")";

      $('<option value="' + tuanBuying['id'] + '">' + name + '</option>').appendTo(tuanBuyingsBox);
    });
    setSelectBoxValue(tuanBuyingsBox);
  }

  function setupByTuanTeamForm(){
    var form = $('.form-by-tuan-team');
    var tuanTeamsBox = $('.tuan-teams', form);
    var tuanBuyingsbox = $('.tuan-buyings', form);
    var sendDateStart = $('.send-date-start', form);
    var sendDateEnd = $('.send-date-end', form);
    sendDateEnd.attr('disabled', 'disabled');
    tuanTeamsBox.on('change', function(){
      updateTuanBuyingSelectBox($("option:selected", $(this)));
      updateSendDateInput();
    });
    tuanBuyingsbox.on('change', function(){
      updateSendDateInput();
    });
    function updateSendDateInput(){
      if(tuanTeamsBox.val() == -1){
        sendDateStart.removeAttr('disabled');
        sendDateEnd.attr('disabled', 'disabled');
      }
      else{
        if(tuanBuyingsbox.val() == -1){
          sendDateStart.removeAttr('disabled');
          sendDateEnd.removeAttr('disabled');
        }
        else{
          sendDateStart.attr('disabled', 'disabled');
          sendDateEnd.attr('disabled', 'disabled');
        }
      }
    }
  }
  activateTab($('.nav-tabs').data('query-type'));
  setSelectBoxValue($('.order-status'));
  setSelectBoxValue($('.order-types'));
  setupByTuanTeamForm();
    $('#check_all_tb').click(function(e){
        var table= $(e.target).closest('table');
        $('td input:checkbox',table).prop('checked',this.checked);
    });
    function getAllCheckTbId(){
        var $checkboxes = $('td input:checkbox:checked',$('table tbody'));
        var $tb_ids = [];
        $.each($checkboxes,function(index,item){
            var $item = $(item);
            $tb_ids.push($item.val());
        });
        return $tb_ids;
    }
    $('.offline_store_msg').click(function(){
        var tb_ids = getAllCheckTbId();
        $.post('/manage/admin/tuan_buyings/send_wx_fetch_msg/normal', {"ids":tb_ids}, function(data){
            var success_ids = (data.res).join(',');
            if(data.success){
                $.post('/manage/admin/tuan_buyings/set_status', {tuan_orderid: success_ids, order_status:2}, function(edata){
                    utils.alert(edata.msg);
                    location.reload();
                },'json')
            }
        }, 'json')
    });
    $('.send_code').click(function(){
        var codeDom = $(this).prev('input');
        var orderId = codeDom.attr('name').split('_')[1];
        var code = codeDom.val();
        var obj = {};
        obj[orderId]=code;
        $.post('/manage/admin/tuan_buyings/send_wx_fetch_msg',obj , function(data){
            var success_ids = (data.res).join(',');
            if(data.success){
                $.post('/manage/admin/tuan_buyings/set_status', {tuan_orderid: success_ids, order_status:2}, function(edata){
                    utils.alert(edata.msg);
                    $('.table-bordered tbody tr').remove('[data-order-id='+ orderId +']');
                },'json')
            }
        },'json')
    })

});