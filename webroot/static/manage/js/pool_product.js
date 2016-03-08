$(function (){
  $('.ban-pool-product').on('click', function (){
    if (confirm('确定下架?')) {
      window.location.href = '/shareManage/pool_product_ban/' + $(this).attr('data-upid') + ".html";
    } else {
      console.log('手贱, 不好意思');
    }
  });
  $('.delete-pool-product').on('click', function (){
    if (confirm('确定删除?')) {
      window.location.href = '/shareManage/pool_product_delete/' + $(this).attr('data-upid') + ".html";
    } else {
      console.log('手贱, 不好意思');
    }
  });
  $('#show-hidden-by-status-ban').click(function (){
    $('tr.list-item').each(function (){
      if ($(this).attr('status') == 0) {
        $(this).toggle();
      }
    });
  });
  $('#show-hidden-by-status-pending').click(function (){
    $('tr.list-item').each(function (){
      if ($(this).attr('status') == 2) {
        $(this).toggle();
      }
    });
  });
});
