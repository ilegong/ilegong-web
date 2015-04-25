$(document).ready(function(){
  var tuanTeams = $('.tuan-teams');
  var tuanProductSearch = $('.tuan-product-search');
  var tuanProducts = $('.tuan-products');
  var tuan_name = $('#tuan_name');
  var start_stat_date = $('input[name="start_stat_datetime"]');
  var end_stat_date = $('input[name="end_stat_datetime"]');
  var tuan_con_date = $('input[name="tuan_con_date"]');
  var product_con_date = $('input[name="product_con_date"]');
  var leftTeamSelectData = [];
  var leftProductSelectData = [];
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
    $.each(data,function(index,item){
      $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
    });
    setSelectBoxValue(tuanTeams);
    search_tuanteam();
  });
  $.getJSON('/manage/admin/tuanProducts/api_tuan_products',function(data){
    $.each(data,function(index,item){
      var tuan_product = item['TuanProduct'];
      $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo(tuanProducts);
    });
    setSelectBoxValue(tuanProducts);
    search_product();
  });

  String.prototype.Trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
  };

  function search_product(){
    $("option", tuanProducts).each(function(){
      leftProductSelectData.push({'val':$(this).val(),'name':$(this).text()});
    });
    if(navigator.userAgent.indexOf("MSIE")>0){
      tuanProductSearch.on('onpropertychange', function(){productChange($(this))});
    }else{
      tuanProductSearch.on('input', function(){productChange($(this))});
    }
  }

  function search_tuanteam(){
    $("option", tuanTeams).each(function(){
      leftTeamSelectData.push({'val':$(this).val(),'name':$(this).text()});
    });
    if(navigator.userAgent.indexOf("MSIE")>0){
      tuan_name.on('onpropertychange',tuanNameChange);
    }else{
      tuan_name.on('input',tuanNameChange);
    }
  }
  function tuanNameChange(){
    var content= tuan_name.val().Trim();
    tuanTeams.empty();
    if(content == ''){
      $.each(leftTeamSelectData,function(index,value){
        tuanTeams.append('<option value="'+value['val']+'">'+value['name']+'</option>');
      });
    }else{
      var reg = new RegExp(content,'i');
      $.each(leftTeamSelectData,function(index,val){
        if(reg.test(val['name'])){
          tuanTeams.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
        }
      })
    }
  }

  function productChange(searchBox){
    var content= searchBox.val().Trim();
    var filterTuanProducts = $("#" + searchBox.data('search-for'));
    filterTuanProducts.empty();
    if(content == ''){
      $.each(leftProductSelectData,function(index,value){
        filterTuanProducts.append('<option value="'+value['val']+'">'+value['name']+'</option>');
      });
    }else{
      var reg = new RegExp(content,'i');
      $.each(leftProductSelectData,function(index,val){
        if(reg.test(val['name'])){
          filterTuanProducts.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
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
  setSelectBoxValue($('.order-types'));
});