$(function(){
  $(".sec-kill-form").submit(function(e){
    var invalidConsignmentDate = $("#consignment_date").val() == '';
    $("#consignment_date").parents('.form-group').toggleClass('has-error', invalidConsignmentDate);

    if(invalidConsignmentDate){
        return false;
    }
    return true;
  });
});