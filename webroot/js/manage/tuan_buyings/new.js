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
})