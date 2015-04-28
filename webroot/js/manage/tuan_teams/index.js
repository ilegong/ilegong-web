/**
 * Created by algdev on 15/3/28.
 */
$(function(){
    var tuanTeams = $('.tuanTeam');
    var tuan_name = $('#tuan_name');
    var leftSelectData = [];
    $.getJSON('/manage/admin/tuan_teams/api_tuan_teams',function(data){
        $.each(data,function(teamId, item){
            var tuanTeam = item['TuanTeam'];
            var ele = $('<option value="' + teamId + '">' + tuanTeam['tuan_name']+'</option>');
            ele.appendTo(tuanTeams);
        });
        search_tuanteam();
        tuanTeams.val(tuanTeams.attr('data-team-id'));
    });
    function search_tuanteam(){

        String.prototype.Trim = function() {
            return this.replace(/(^\s*)|(\s*$)/g, "");
        };

        $("select[name='team_id'] option").each(function(){
            leftSelectData.push({'val':$(this).val(),'name':$(this).text()});
        });
        if(navigator.userAgent.indexOf("MSIE")>0){
            tuan_name.on('onpropertychange',txChange);
        }else{
            tuan_name.on('change',txChange);
        }
    }
    function txChange(){
        var content= tuan_name.val().Trim();
        tuanTeams.empty();
        if(content == ''){
            $.each(leftSelectData,function(index,value){
                tuanTeams.append('<option value="'+value['val']+'">'+value['name']+'</option>');
            });
        }else{
            var reg = new RegExp(content,'i');
            $.each(leftSelectData,function(index,val){
                if(reg.test(val['name'])){
                    tuanTeams.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
                }
            })
        }
    }
});
