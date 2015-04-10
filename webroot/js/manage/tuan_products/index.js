$(function(){
    $('.tuan-product').click(function() {
      var productId = $(this).data('product-id');
      location.href = "/manage/admin/tuan_buyings/index?product_id=" + productId;
    });
});
