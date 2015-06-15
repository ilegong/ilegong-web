$(function(){
    var tuanTeamId = $('.tuan-team').data('tuan-team-id');

    $.getJSON('/promotors/api_summary/'+tuanTeamId+'.json',function(data){

    });
})