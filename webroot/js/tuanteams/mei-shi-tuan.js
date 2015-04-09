/**
 * Created by algdev on 15/4/9.
 */
$(function(){

    var area = $('.conordertuan');
    var areaIds = $('.tuan-teams');
    $.getJSON('/tuanteams/api_getArea',function(data){
        $.each(data,function(index,item){
            var areaId = item['id'];
            var isArea = areaIds.find('[data-county-id="'+areaId+'"]');
            if(isArea.length){
            $('<ul class="clearfix"><li><a href="#X" class="county" data-county-id="'+item['id']+'">'+ item['name']+ '区</a></li></ul>').appendTo(area);
            }
        });
        $('.county').on('click',function(){
            var countyId = $(this).data('county-id');
            $(".tuan-team").each(function(){
                $(this).toggleClass('hide', $(this).data("county-id") != countyId);
            });
            $('.county').removeClass('cur');
            $(this).addClass('cur');
        });
    });

});

