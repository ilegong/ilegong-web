/**
 工具类
 */
//将url的参转换为hash
function url2Hash(_url) {
  var parmhash = {};
  var querstr = _url || location.search;
  var offset1 = querstr.indexOf("?");
  if (offset1 != -1) {
    querstr = querstr.substring(offset1 + 1);
  }
  if (querstr != "") {
    var parms = querstr.split("&");
    var parmsLen = parms.length;
    for (var i = 0; i < parmsLen; i++) {
      var _keyval = parms[i].split("=");
      var _key = _keyval[0];
      var _val = _keyval[1];
      try {
        parmhash[_key] = decodeURI(_val);
      } catch (ex) {
        var offset0 = -1;
        var n = 0;
        while ((offset0 = _val.indexOf("%%")) != -1) {
          _val = _val.substring(0, offset0) + '%25' + _val.substring(offset0 + 1);
          n += 4;
        }
        parmhash[_key] = decodeURI(_val);
      }
    }
  }
  return parmhash;
}
function str2hashcode(str) {
  var hash = 1,
    charCode = 0,
    idx;
  if (true) {
    hash = 0;
    for (idx = str.length - 1; idx >= 0; idx--) {
      charCode = str.charCodeAt(idx);
      hash = (hash << 6 & 268435455) + charCode + (charCode << 14);
      charCode = hash & 266338304;
      hash = charCode != 0 ? hash ^ charCode >> 21 : hash;
    }
  }
  return hash;
}

function sethrefEvent(infoid, isnotocuh) {
  var obj = $("#info_" + infoid);
  if (obj.length == 0) {
    obj = $("#" + infoid);
  }
  var hrefobjs = obj.find("a");
  var hrefobjslen = hrefobjs.length;
  var hrefoffset = 0;
  for (var kk = 0; kk < hrefobjslen; kk++) {
    var _hrefobj1 = $(hrefobjs[kk]);
    var _target = _hrefobj1.attr("target");
    if (_target == null) {
      continue;
    }

    _hrefobj1.attr("target", "_self");
    var _hrefstr = _hrefobj1.attr("href");
    if (_hrefstr != null && _hrefstr.indexOf("http") == 0) {
      var newhrefid = "href_" + kk + "_" + infoid;
      _hrefobj1.attr("href", "javascript:goplay('" + newhrefid + "')");
      _hrefobj1.attr("oldhref", _hrefstr);
      _hrefobj1.attr("id", newhrefid);
      _hrefobj1.attr("infoid", infoid);
      //如果是ipad加上块的click事件，不用点链接可以查看详细信息 && navigator.userAgent.match(/iPad|iPhone/i)

      if (!isnotocuh && hrefoffset == 0) {
        (function (_firsthrefid) {
          obj.click(function () {
            if (event.srcElement.nodeName != "A" && event.srcElement.parentNode.nodeName != "A") {
              goplay(_firsthrefid);
            }
          });
        })(newhrefid)
      }
      hrefoffset++;
    } else {
      _hrefobj1.attr("href", "javascript:void(0)");
    }
  }
}


function loadScript(_src, _reffunc) {
  var localvaljs = document.createElement('script');
  localvaljs.type = 'text/javascript';
  localvaljs.async = true;
  localvaljs.onload = function () {
    _reffunc();
  }
  localvaljs.src = _src;
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(localvaljs, s);
}
function alertmsg(str) {
  var alertmsgdivobj = document.getElementById("alertmsgdiv");
  if (alertmsgdivobj == null) {
    var _html = '<div id="alertmsgdiv" style="text-align:center;opacity:0.6;width:100%;height:100%;position:fixed;left:0px;top:0px;background-color:black;">'
      + '<div style="margin-top:10%;'
      + 'font-size:18px;z-index:1000;padding:12px 0px 12px 0px'
      + ';text-align:center;background-color:black;opacity:1;color:white;">' + str + '</div></div>';
    document.body.insertAdjacentHTML("beforeEnd", _html);
  } else {
    alertmsgdivobj.style.display = "block";
  }
}
function hidemsg(str) {
  var alertmsgdivobj = document.getElementById("alertmsgdiv");
  if (alertmsgdivobj != null) {
    alertmsgdivobj.style.display = "none";
  }
}
function data2str(_data, format) {
  format = format || "yyyy-MM-dd hh:mm:ss";
  var o = {
    "M+": _data.getMonth() + 1, //month
    "d+": _data.getDate(),    //day
    "h+": _data.getHours(),   //hour
    "m+": _data.getMinutes(), //minute
    "s+": _data.getSeconds(), //second
    "q+": Math.floor((_data.getMonth() + 3) / 3),  //quarter
    "S": _data.getMilliseconds() //millisecond
  }
  //console.log(o);
  if (/(y+)/.test(format)) format = format.replace(RegExp.$1,
    (_data.getFullYear() + "").substr(4 - RegExp.$1.length));
  for (var k in o)if (new RegExp("(" + k + ")").test(format))
    format = format.replace(RegExp.$1,
      RegExp.$1.length == 1 ? o[k] :
        ("00" + o[k]).substr(("" + o[k]).length));
  return format;
}
//长时间，形如 (2003-12-05 13:04:06)
function str2date(str) {
  var strlen = str.length;
  if (strlen < 19) {
    if (strlen == 10) {
      str += " 00:00:00";
    } else if (strlen == 16) {
      str += ":00";
    }
  }
  var reg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/;
  var r = str.match(reg);
  if (r != null) {

    var d = new Date(r[1], r[3] - 1, r[4], r[5], r[6], r[7]);
    if (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4] && d.getHours() == r[5] && d.getMinutes() == r[6] && d.getSeconds() == r[7]) {
      return d;
    }
  }
  return null;
}

function getFriendlyDate(addTimeStamp, nowTimeStamp) {
  var nowDate = new Date();
  nowDate.setTime(nowTimeStamp);
  var nowMonthV = nowDate.getMonth() + 1;
  var nowDayV = nowdate.getDate();
  var beforeNowDate = new Date();
  beforeNowDate.setTime(nowTimeStamp - 3600000 * 24);
  var beforeNowMonthV = beforeNowDate.getMonth() + 1;
  var beforeNowDayV = beforeNowDate.getDate();
  var currDate = new Date();
  currDate.setTime(addTimeStamp);
  var monthV = currDate.getMonth() + 1;
  var dayV = currDate.getDate();
  var dateStr = '';
  if (nowMonthV == monthV && nowDayV == dayV) {
    dateStr = '今天';
  } else if (beforeNowMonthV == monthV && beforeNowDayV == dayV) {
    dateStr = '昨天';
  } else {
    dateStr = '' + (dayV < 10 ? '0' + dayV : dayV) + '<font>' + monthdx[monthV - 1] + '月</font>';
  }
  return dateStr;
}
