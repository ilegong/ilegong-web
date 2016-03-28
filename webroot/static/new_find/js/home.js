// allinone.js

$(function (){
  $(".carousel-inner").swipe({
    //Generic swipe handler for all directions
    //swipe: function(event, direction, distance, duration, fingerCount) {
    //},
    swipeLeft: function() {
      $(this).parent().carousel('next');
    },
    swipeRight: function() {
      $(this).parent().carousel('prev');
    },
    excludedElements: "label, button, input, select, textarea, .noSwipe"
  });
});
