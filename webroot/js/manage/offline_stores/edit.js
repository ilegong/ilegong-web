$(function () {
    var areaId = $('.area-id');
    var type = $('.type');
    var shopNo = $('.shop-no');
    var childArea = $('.child-area-id');
    var shopAccount = $('.shop-account');
    var shopAccountName = $('.shop-account-name');
    var initShopNo = function(){
        shopNo.toggleClass('hidden', type.val() != 0);
    };
    var initChildArea = function(){
        childArea.toggleClass('hidden',areaId.data('value') !=110114);
    };
    var initShopAccount = function(){
        shopAccount.toggleClass('hidden',type.val() == 0);
    };
    var initShopAccountName = function(){
      shopAccountName.toggleClass('hidden',type.val() == 0);
    };
//    var zitiAddress = zitiAddress('');
//    var areas = zitiAddress.getBeijingAreas;

    $.each(beijingArea, function (index, item) {
        $('<option value="' + index + '">' + item.name + '</option>').appendTo(areaId);
        if(item.children_area){
            $.each(item.children_area,function(ind,itm){
                $('<option value="' + ind + '">' + itm.name + '</option>').appendTo(childArea);
            });
        }
    });
    type.on('change', function(){
        initShopNo();
        initShopAccount();
        initShopAccountName();
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
    initShopAccount();
    initShopAccountName();

    $('.offline-store').submit(function(){
       if(!shopNo.hasClass('hidden')){
          var shopNum = shopNo.val()==''|| shopNo.val()==0;
          shopNo.parents('.form-group').toggleClass('has-error',shopNum);
           if(shopNum){
              return false;
           }
       }

       return true;
    });
});