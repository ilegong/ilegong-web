/**
 * Created by algdev on 15/4/15.
 */
$(function(){
    var getCountyId = $('.county-type');
    var county_id = $('#county_id').val();
    $.getJSON('/manage/admin/tuanTeams/api_tuan_county',function(data){
        $.each(data,function(index,item){
            if(county_id == item['id']){
            $('<option selected = "selected" value="'+item['id']+'">'+item['name']+'区</option>').appendTo(getCountyId);
            }else{
            $('<option value="'+item['id']+'">'+item['name']+'区</option>').appendTo(getCountyId);
            }
        })
    });
});