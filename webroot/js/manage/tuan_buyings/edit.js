$(function(){
  $('.form_datetime').datetimepicker({
    language:  'zh-CN',
    weekStart: 1,
    todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 2,
    forceParse: 0,
    showMeridian: 1
  });

  var tuanEndTime = $('.tuan-end-time');
  var tuanTargetNum = $('.tuan-target-num');
  var consignmentType = $('.consignment-type');
  var consignmentTime = $('.consignment-time');
  $(".tuan-buying-form").submit(function(e){
    var invalidTuanEndTime = tuanEndTime.val() == '';
    tuanEndTime.parents('.form-group').toggleClass('has-error', invalidTuanEndTime);
    var targetNum = Number(tuanTargetNum.val());
    var invalidTargetNum = isNaN(targetNum) || targetNum < 1;
    tuanTargetNum.parents('.form-group').toggleClass('has-error', invalidTargetNum);
    var invalidConsignmentTime = (consignmentType.val() == 0 || consignmentType.val() == 1) && consignmentTime.val() == '';
    consignmentTime.parents('.form-group').toggleClass('has-error', invalidConsignmentTime);

    if(invalidTuanEndTime || invalidTargetNum || invalidConsignmentTime){
      return false;
    }
    return true;
  });
})