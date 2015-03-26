$(function(){
    var tuanTeams = $('.tuan-teams');
    $.getJSON('/manage/admin/tuan/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
    });
    var tuanProducts = $('.tuan-products');
    $.getJSON('/manage/admin/tuan/api_tuan_products',function(data){
        console.log(data);
        $.each(data,function(index,item){
            $('<option value="' + index + '">' + item + '</option>').appendTo(tuanProducts);
        });
    });
    var tuanEndTime = $('.tuan-end-time');
    var tuanTargetNum = $('.tuan-target-num');
    $(".tuan-form").submit(function(e){
        tuanTeams.parents('.form-group').toggleClass('has-error', tuanTeams.val() == -1);
        tuanProducts.parents('.form-group').toggleClass('has-error', tuanProducts.val() == -1);
        tuanEndTime.parents('.form-group').toggleClass('has-error', tuanEndTime.val() == '');
        var targetNum = Number(tuanTargetNum.val());
        tuanTargetNum.parents('.form-group').toggleClass('has-error', isNaN(targetNum) || targetNum < 1);

        if(tuanTeams.val() == -1 || tuanProducts.val() == -1 || tuanEndTime.val() == '' || isNaN(targetNum) || targetNum < 1){
            return false;
        }
        return true;
    });
})