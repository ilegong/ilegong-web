$(function(){
    $('.form_datetime').datetimepicker({
        language:  'zh-CN',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0,
        showMeridian: 1
    });
    var tuanTeams = $('.tuan-teams');
    $.getJSON('/manage/admin/tuan/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
    });
    var tuanProducts = $('.tuan-products');
    $.getJSON('/manage/admin/tuan/api_tuan_products',function(data){
        $.each(data,function(index,item){
            $('<option value="' + index + '">' + item + '</option>').appendTo(tuanProducts);
        });
    });
    var tuanEndTime = $('.tuan-end-time');
    var tuanTargetNum = $('.tuan-target-num');
    $(".tuan-form").submit(function(e){
        var invalidTuanTeam = tuanTeams.val() == -1;
        tuanTeams.parents('.form-group').toggleClass('has-error', invalidTuanTeam);
        var invalidTuanProduct = tuanProducts.val() == -1;
        tuanProducts.parents('.form-group').toggleClass('has-error', invalidTuanProduct);
        var invalidTuanEndTime = tuanEndTime.val() == '';
        tuanEndTime.parents('.form-group').toggleClass('has-error', invalidTuanEndTime);
        var targetNum = Number(tuanTargetNum.val());
        var invalidTargetNum = isNaN(targetNum) || targetNum < 1;
        tuanTargetNum.parents('.form-group').toggleClass('has-error', invalidTargetNum);

        if(invalidTuanTeam || invalidTuanProduct || invalidTuanEndTime || invalidTargetNum){
            return false;
        }
        return true;
    });
})