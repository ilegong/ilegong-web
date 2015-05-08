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

    iUtils.initSelectBox(tuanTeamsBox);
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

    iUtils.initSelectBox($('.tuan-products'));
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

    iUtils.initSelectBox(products);
    initSearchBox($('.product-search'), values);
  });
    $.getJSON('/manage/admin/offline_stores/api_offline_stores', function(data){
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
        iUtils.initSelectBox(offlineStoreBox);
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
      iUtils.initSelectBox(tuanBuyingsBox);
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
    function setupHaolinjuStoreDialog(orderId){
        var sendWeixinMessageCheckBox = $(".send-weixin-message");
        var haolinjuCodeInput = $(".haolinju-code");
        var haolinjuOrderIdInput = $(".haolinju-order-id");
        var shipToHaolinjuStoreForm = $('.ship-to-haolinju-store-form');
        var confirmShipping = function(){
            if(!sendWeixinMessageCheckBox.is(':checked')){
                if(!confirm("修改为发货，但是不发送到货提醒，确认吗?")){
                    return;
                }
                shipToHaolinju(haolinjuOrderIdInput.val(), false, '');
            }
            else{
                if(haolinjuCodeInput.val() == ''){
                    utils.alert("修改为发货，并发送到货提醒，请输入提货码");
                    return;
                }
                if(!confirm("修改为发货，并发送到货提醒，提货码为" + haolinjuCodeInput.val() + ", 确认吗？")){
                    return;
                }
                shipToHaolinju(haolinjuOrderIdInput.val(), true, haolinjuCodeInput.val());
            }
        }
        var shipToHaolinju = function(orderId, sendWeixinMessage, haolinjuCode){
            $.post('/manage/admin/tuan_orders/ship_to_haolinju_store', {orderId: orderId, sendWeixinMessage: sendWeixinMessage, haolinjuCode: haolinjuCode}, function(data){
                if(data.success){
                    var returnedOrderId = data.res;
                    if(orderId = returnedOrderId){
                        utils.alert('订单状态修改成功，并发送了到货提醒');
                    }
                    else{
                        utils.alert('订单状态修改成功，但是没有发送到货提醒');
                    }
                }
                else{
                    utils.alert('订单修改失败: ' + data.res);

                }
                $('.table-bordered tbody tr').remove('[data-order-id='+ orderId +']');
            },'json')
        }
        var shipToHaolinjuStoreDialog = $( ".ship-to-haolinju-store-dialog" ).dialog({
          autoOpen: false,
          height: 300,
          width: 350,
          modal: true,
          buttons: {
              "取消": function() {shipToHaolinjuStoreDialog.dialog( "close" );},
              "确认发货": function(){confirmShipping(orderId);}
          },
          close: function() {
              sendWeixinMessageCheckBox.attr("checked", "checked");
              haolinjuCodeInput.val("");
          }
        });
        sendWeixinMessageCheckBox.on('click', function(){
          if($(this).is(":checked")){
              haolinjuCodeInput.removeAttr('disabled');
          }
          else{
              haolinjuCodeInput.attr('disabled', 'disabled');
          }
        })
        $( ".ship-to-haolinju-store" ).click(function() {
            var orderId = $(this).parents('tr').data('order-id');
            haolinjuOrderIdInput.val(orderId);
            shipToHaolinjuStoreDialog.dialog( "open" );
        });
    }

    activateTab($('.nav-tabs').data('query-type'));
    iUtils.initSelectBox($('.order-status'));
    iUtils.initSelectBox($('.order-types'));
    setupByTuanTeamForm();
    setupHaolinjuStoreDialog();
    $('#check_all_tb').click(function(e){
        var table= $(e.target).closest('table');
        var checked = $(this).is(':checked');
        $('.order input:checkbox', $('.orders')).each(function(){
            if(checked && !$(this).is(':disabled')){
                $(this).attr('checked', 'checked');
            }
            else{
                $(this).removeAttr('checked');
            }
        })
    });
    function getCheckedOrderIds(){
        var $tb_ids = [];
        $.each($('.order input:checkbox:checked', $('.orders')), function(index, item){
            $tb_ids.push($(this).val());
        });
        return $tb_ids;
    }
    $('.ship-to-pys-stores').click(function(){
        var orderIds = getCheckedOrderIds();
        if(orderIds.length == 0){
            utils.alert('请先选择自有自提点的待发货订单');
            return;
        }

        if(!confirm("您选择" + orderIds.length + "个订单，会向用户发送到货提醒，是否继续？")){
            return;
        }
        $.post('/manage/admin/tuan_orders/ship_to_pys_stores', {"ids": orderIds}, function(data){
            if(data.success){
                var msg;
                if(orderIds.length == data.res.length){
                    msg = '订单状态修改成功，并全部发送了到货提醒';
                }
                else{
                    msg = '订单状态修改成功，但有' + (orderIds.length - data.res.length) + '个未发送到货提醒';
                }
                utils.alert(msg);
                location.reload();
            }
            else{
                utils.alert(data.res);
            }
        }, 'json');
    });
});