$(function(){
    var getCountyId = $('.county-type');
    var county_id = $('#county_id').val();
    $.each(tuanAreas,function(index,item){
        if(county_id == item['id']){
            $('<option selected = "selected" value="'+item['id']+'">'+item['name']+'区</option>').appendTo(getCountyId);
        }else{
            $('<option value="'+item['id']+'">'+item['name']+'</option>').appendTo(getCountyId);
        }
    });
});