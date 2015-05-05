$(function(){
    var getCountyId = $('.county-type');
    var county_id = $('#county_id').val();
    var offlineStoreBox = $('.offline-store');
    $.each(tuanAreas,function(index,item){
        if(county_id == item['id']){
            $('<option selected = "selected" value="'+item['id']+'">'+item['name']+'区</option>').appendTo(getCountyId);
        }else{
            $('<option value="'+item['id']+'">'+item['name']+'</option>').appendTo(getCountyId);
        }
    });
    $.getJSON('/manage/admin/tuan_buyings/api_offline_stores',function(data){
        for(var category in data){
            var categoryName = category == 0 ? '好邻居' : '自有自提点';
            $('<optgroup label="--------"><option value="" class="store_'+ category +'">' + categoryName + '</option>').appendTo(offlineStoreBox );
            for(var offlineStoreId in data[category]){
                $('<option value="' + offlineStoreId + '">' + data[category][offlineStoreId].name + '</option>').appendTo(offlineStoreBox);
            }
        }
        iUtils.initSelectBox(offlineStoreBox);
    });
});