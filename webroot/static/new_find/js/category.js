// allinone.js
function update_arrow(ele) {
  if (ele.children('i').hasClass('fa-angle-down')) {
    ele.children('i').removeClass('fa-angle-down').addClass('fa-angle-up');
  } else {
    ele.children('i').removeClass('fa-angle-up').addClass('fa-angle-down');
  }
}

function update_event_listener() {
  $('.followed, .unfollow, .follow-ta').unbind('click');

  $('.follow-ta').on('click', function (){
    console.log('Follow TA');
    var me = $(this).attr('data-me');
    var ta = $(this).attr('data-ta');
    $.ajax({
      url: '/weshares/subscribe_sharer/' + ta + '/' + me,
      type: 'get',
      dataType: 'json',
      success: function (data) {
        if (data.success == true) {
          $(this).addClass('followed').removeClass('follow-ta');
          $(this).text('已关注').append('<i class="fa fa-angle-down"></i>');
          update_event_listener();
        }
      }.bind(this),
    });
  });

  $('.followed').on('click', function (){
    console.log('Trigger unfollow event.');
    $(this).siblings().eq(0).toggle();
    update_arrow($(this));
  });

  $('.unfollow').on('click', function (){
    $(this).toggle();
    update_arrow($(this).prev());
    console.log('unfollow');
    var me = $(this).attr('data-me');
    var ta = $(this).attr('data-ta');
    $.ajax({
      url: '/weshares/unsubscribe_sharer/' + ta + '/' + me,
      type: 'get',
      dataType: 'json',
      success: function (data) {
        if (data.success == true) {
          $(this).prev().addClass('follow-ta').removeClass('followed').html('').text('关注TA');
          update_event_listener();
        }
      }.bind(this),
    });
  });
}

$(function (){
  update_event_listener();
});

