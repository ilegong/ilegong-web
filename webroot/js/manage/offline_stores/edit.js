$(function () {
    var areaId = $('.area-id');
    var type = $('.type');
    var shopNo = $('.shop-no');
    var childArea = $('.child-area-id');
    var initShopNo = function(){
        shopNo.toggleClass('hidden', type.val() != 0);
    };
    var initChildArea = function(){
        childArea.toggleClass('hidden',areaId.data('value') !=110114);
    };
//    var zitiAddress = zitiAddress('');
//    var areas = zitiAddress.getBeijingAreas;

    $.each(beijingArea, function (index, item) {
        $('<option value="' + index + '">' + item + '</option>').appendTo(areaId);
    });
    $.each(changpingArea, function (index, item) {
        $('<option value="' + index + '">' + item + '</option>').appendTo(childArea);
    });
    type.on('change', function(){
        initShopNo();
    });
    areaId.on('change',function(){
       var parentId = $(this).val();
         childArea.toggleClass('hidden',parentId != 110114);
    });
    iUtils.initSelectBox(areaId);
    iUtils.initSelectBox(type);
    iUtils.initSelectBox(childArea);
    initShopNo();
    initChildArea();
});