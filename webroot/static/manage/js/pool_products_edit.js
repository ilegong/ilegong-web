$(function (){
  $('#add-new-product').click(function (){
    var len = $('.pool-product-item').length;
    var newItem = $('.pool-product-item').eq(0).clone();
    newItem.find('input[type!=hidden]').each(function (){
      var tmp_name = $(this).attr('name');
      $(this).attr('name', tmp_name.replace(/(data\[[a-zA-Z_-]+\]\[)\d+(\]\[[a-zA-Z_-]+\])/g, '$1' + len + '$2'));
      $(this).attr('value', '');
    });

    $('#add-new-product').before(newItem);
  });
});