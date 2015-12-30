var fnTimeCountDown = function(d, o){
  /**
   sec: 显示秒数值的标签对象,
   mini: 显示分钟数值的标签对象,
   hour: 显示小时数值的标签对象,
   day: 显示天数数值的标签对象,
   month: 显示月份数值的标签对象,
   year: 显示年数数值的标签对象
   * @type {{zero: Function, dv: Function, ui: Function}}
   */
  var f = {
    zero: function(n){
      var n = parseInt(n, 10);
      if(n > 0){
        if(n <= 9){
          n = "0" + n;
        }
        return String(n);
      }else{
        return "00";
      }
    },
    dv: function(){
      d = d || Date.UTC(2050, 0, 1); //如果未定义时间，则我们设定倒计时日期是2050年1月1日
      var future = new Date(d), now = new Date();
      //现在将来秒差值
      var dur = Math.round((future.getTime() - now.getTime()) / 1000) + future.getTimezoneOffset() * 60, pms = {
        sec: "00",
        mini: "00",
        hour: "00",
        day: "00",
        month: "00",
        year: "0"
      };
      if(dur > 0){
        pms.sec = f.zero(dur % 60);
        pms.mini = Math.floor((dur / 60)) > 0? f.zero(Math.floor((dur / 60)) % 60) : "00";
        pms.hour = Math.floor((dur / 3600)) > 0? f.zero(Math.floor((dur / 3600)) % 24) : "00";
        pms.day = Math.floor((dur / 86400)) > 0? f.zero(Math.floor((dur / 86400)) % 30) : "00";
        //月份，以实际平均每月秒数计算
        pms.month = Math.floor((dur / 2629744)) > 0? f.zero(Math.floor((dur / 2629744)) % 12) : "00";
        //年份，按按回归年365天5时48分46秒算
        pms.year = Math.floor((dur / 31556926)) > 0? Math.floor((dur / 31556926)) : "0";
      }
      return pms;
    },
    ui: function(){
      if(o.sec){
        o.sec.innerHTML = f.dv().sec;
      }
      if(o.mini){
        o.mini.innerHTML = f.dv().mini;
      }
      if(o.hour){
        o.hour.innerHTML = f.dv().hour;
      }
      if(o.day){
        o.day.innerHTML = f.dv().day;
      }
      if(o.month){
        o.month.innerHTML = f.dv().month;
      }
      if(o.year){
        o.year.innerHTML = f.dv().year;
      }
      setTimeout(f.ui, 1000);
    }
  };
  f.ui();
};
$(document).ready(function () {
  var $layerInvitation = $('#layer-invitation');
  var $maskBgLayer = $('#mask-bg-layer');
  var $promptInvitationLayerBtn = $('#prompt-invitation-layer-btn');
  var $expireTime = $('#tag-expire-date');
  $promptInvitationLayerBtn.on('click', function (e) {
    e.preventDefault();
    $maskBgLayer.show();
    $layerInvitation.show();
  });
  $maskBgLayer.on('click', function (e) {
    e.preventDefault();
    $layerInvitation.hide();
    $maskBgLayer.hide();
  });
  initTimeCountDown();
  function initTimeCountDown(){
    if(document.getElementById("leave-sec")){
      var d = getDateFromStr($expireTime.val());
      var obj = {
        sec: document.getElementById("leave-sec"),
        mini: document.getElementById("leave-mini"),
        hour: document.getElementById("leave-hour")
      };
      fnTimeCountDown(d, obj);
    }
  }
  function getDateFromStr(dateString){
    //var dateString = "2010-08-09 01:02:03";
    var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
    var dateArray = reggie.exec(dateString);
    var dateObject = Date.UTC(
      (+dateArray[1]),
      (+dateArray[2])-1, // Careful, month starts at 0!
      (+dateArray[3]),
      (+dateArray[4]),
      (+dateArray[5]),
      (+dateArray[6])
    );
    return dateObject;
  }
});
