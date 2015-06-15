$(function(){

    var areaId = $('.area');
    var type = $('.type');
    $.each(beijingArea, function (index, item) {
       var ele = $('<option value="' + index + '">' + item.name + '</option>');
       ele.appendTo(areaId);
    });
    areaId.val(areaId.attr('data-team-id'));
    type.val(type.data('team-id'));
});
