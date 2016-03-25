// allinone.js

$(function (){
  $('#carousel-captions').swipe({
    swipe: function (event, direction, distance, duration, fingerCount) {
      console.log('swipe to right');
    }
  });
  $(".carousel-inner").swipe({
    //Generic swipe handler for all directions
    swipe: function(event, direction, distance, duration, fingerCount) {
      console.log('swipe to right');
      // $(this).parent().carousel('prev');
    },
  });
});
