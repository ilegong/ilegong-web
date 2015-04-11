$(function(){
    var tuanTeams = $('.tuanTeam');
    $.getJSON('/manage/admin/tuan_teams/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
    });
});
