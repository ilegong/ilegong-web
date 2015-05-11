$(function(){

    var areaId = $('.area');
    var type = $('.type');
    var areas = zitiAddress.getBeijingAreas;
    $.each(areas, function (index, item) {

       var ele = $('<option value="' + index + '">' + item + '</option>');
       ele.appendTo(areaId);
    });
    areaId.val(areaId.attr('data-team-id'));
    type.val(type.data('team-id'));
});
