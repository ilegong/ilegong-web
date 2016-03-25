// allinone.js

$(function (){
  $(".carousel-inner").swipe({
    //Generic swipe handler for all directions
    swipe: function(event, direction, distance, duration, fingerCount) {
      console.log('swipe to right');
      // $(this).parent().carousel('prev');
    }
  });
});