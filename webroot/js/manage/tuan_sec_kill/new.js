$(function(){
    var team_ids = $('#team_ids');
    var tuanTeamList = $('.tuan-teams-list');
    $.getJSON('/manage/admin/tuanTeams/api_tuan_teams',function(data){
        $.each(tuanAreas,function(Index,Item){
            var tuan_area =  $('<p class="tuan-area"  value="'+ Item['id']+'"><strong>'+ Item['name']+'</strong></p>');
            tuan_area.appendTo(tuanTeamList).css('color','red').hide();
            $.each(data,function(index,items){
                var item = items['TuanTeam'];
                if(item['county_id'] == Item['id']){
                    $('<input type="checkbox" class="tuan-team" data-id="'+item['county_id'] +'" value="'+item['id']+'"name="team_id">'+ item['tuan_name'] + '</input>').appendTo(tuanTeamList);
                    tuan_area.show();
                }
            });
        });
    });
  $(".sec-kill-form").submit(function(e){
      var tuanTeamId = new Array();
      $(".tuan-team[type='checkbox']:checked").each(function(){
          tuanTeamId.push($(this).val());
      });
      team_ids.val(tuanTeamId.join(','));
    var invalidConsignmentDate = $("#consignment_date").val() == '';
    $("#consignment_date").parents('.form-group').toggleClass('has-error', invalidConsignmentDate);

    if(invalidConsignmentDate){
        return false;
    }
    return true;
  });
});