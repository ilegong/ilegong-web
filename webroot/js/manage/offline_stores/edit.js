$(function () {
    var areaId = $('.area-id');
    var type = $('.type');
    var shopNo = $('.shop-no');
    var initShopNo = function(){
        shopNo.toggleClass('hidden', type.val() != 0);
    }

    $.each(tuanAreas, function (index, item) {
        $('<option value="' + item['id'] + '">' + item['name'] + '</option>').appendTo(areaId);
    });
    type.on('change', function(){
        initShopNo();
    })

    iUtils.initSelectBox(areaId);
    iUtils.initSelectBox(type);
    initShopNo();
});