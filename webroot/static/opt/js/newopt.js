$(document).ready(function () {
  var $logListDiv = $('#maindiv');
  var logListDom = $logListDiv[0];
  var $loadingDiv = $('#dataloadingdiv');
  var loadingDivDom = $loadingDiv[0];
  var $backTopBtn = $('#topiconbut');
  var getOptLogUrl = '/share_opt/newfetch_opt_list_data.json';
  var loadDataFlag = 0;
  var filterVal = 0;
  var oldest_timestamp = 0;
  var last_timestamp = 0;
  var lazyLoadOptions = {
    event: "scrollstop",
    vertical_only: true,
    no_fake_img_loader: true,
    container: $logListDiv,
    load: afterLoad
  };
  init();
  function init() {
    var $body = $("body");
    var w0 = $body.width();
    var w1 = $logListDiv.width();
    var zv1 = w0 / w1;
    if (zv1 > 1.1) {
      $body.css("zoom", zv1);
    }
    $backTopBtn.click(function () {
      $logListDiv.scrollTop(0);
    });
    $logListDiv.scroll(function () {
      loadMoreDataWithScrollY();
    });
    initOptLogView();
  }

  function afterLoad() {
    var $me = $(this);
    var tagName = $me[0].tagName;
    if (tagName == 'a') {
      if ($me.hasClass('contentb')) {
        //background-size contain cover
        var $hasShareAttr = $me.attr('data-shareid');
        if ($hasShareAttr) {
          $me.css({'background-size': "cover"});
        } else {
          $me.css({'background-size': "contain"});
        }
      }
    }
  }
  function showNewOptLogInfo() {
  }

  function initOptLogView() {
    loadOptLogData();
  }

  function loadMoreDataWithScrollY() {
    var topVal = logListDom.scrollTop;
    if (topVal > 150) {
      $backTopBtn.show();
    } else {
      $backTopBtn.hide();
    }
  }

  function reloadLogOptData() {
    var optLogs = $logListDiv.children(".postinfo");
    var optLogsLen = optLogs.length;
    if (optLogsLen > 0) {
      $(optLogs[0]).before($loadingDiv);
      $loadingDiv.show();
      var topVal = $loadingDiv.offset().top;
      var scrollTopVal = $logListDiv.scrollTop;
      if (topVal < 0) {
        $logListDiv.scrollTop = (scrollTopVal + topVal) - 60;
      } else {
        if (topVal < 30) {
          $logListDiv.scrollTop = scrollTopVal - 60;
        }
      }
      setTimeout(function () {
        loadOptLogData(function () {
          $loadingDiv.css("marginBottom", optLogsLen * 120);
          optLogs.remove();
          setTimeout(function () {
            $loadingDiv.css("marginBottom", 0);
          }, 300);
        });
      }, 200);
    } else {
      loadOptLogData(checkDataShow);
    }
  }

  function filterOptLogData() {

  }

  function loadOptLogData() {
    if (loadDataFlag == 1) {
      return false;
    }
    $loadingDiv.show();
    loadDataFlag = 1;
    var bottomTimeStamp = 0;
    var $lastOptLog = $loadingDiv.prev('div.postinfo');
    if ($lastOptLog.length > 0) {
      bottomTimeStamp = $lastOptLog.data("timestamp");
      if (oldest_timestamp != 0 && bottomTimeStamp == oldest_timestamp) {
        $loadingDiv.hide();
        return false;
      }
    }
    var reqParams = {
      "type": filterVal,
      "time": bottomTimeStamp,
      "limit": 10
    };
    var callbackFunc = function (data) {
      var list = data['opt_logs'];
      var nowTimeStamp = data['nowTimeStamp'];
      for (var i = 0; i < list.length; i++) {
        var objJson = list[i];
        parseInfoJsonObj(objJson, nowTimeStamp);
      }
      last_timestamp = data['last_timestamp'];
      oldest_timestamp = data['oldest_timestamp'];
      loadDataFlag = 0;
      $loadingDiv.hide();
    };
    $.getJSON(getOptLogUrl, reqParams, callbackFunc);
  }

  function pareseLikeInfos(userIds, users) {
    var likeData = {
      count: userIds.length,
      userIds: userIds,
      users: users
    };
    return TemplateEngine(optLogLikeTemplate, likeData);
  }

  function parseInfoJsonObj(objJson) {
    var html = convertOptJson2html(objJson);
    loadingDivDom.insertAdjacentHTML('BeforeBegin', html);
    var optLogItemId = objJson.id;
    var optLogItemDom = $(document.getElementById("info_" + optLogItemId));
    var fontContentObj = optLogItemDom.children(".fontcontent");
    var expContentAObj = optLogItemDom.children(".expcontenta");
    var fontContentHeight = fontContentObj.height();
    if (fontContentHeight > 54) {
      fontContentObj.css({"height": "54px"});
      expContentAObj.css("display", "block");
      expContentAObj.click(function () {
        if (this.innerText == "全文") {
          this.innerText = "收起";
          $(this.parentNode).children(".fontcontent")[0].style.cssText = "height:none";
        } else {
          $(this.parentNode).children(".fontcontent")[0].style.cssText = "height:54px";
          this.innerText = "全文";
        }
      });
    } else {
      expContentAObj.remove();
    }
    var optLogCtrlObjDom = $(document.getElementById("ctrl_" + optLogItemId));
    optLogCtrlObjDom.children("a").click(function () {
      var dataAction = this.getAttribute("data-action");
      if (dataAction == "moreaction") {
        //TODO 展开评论和点赞按钮
        //showMoreActionWin(optLogItemId);
      } else {
        //TODO 处理按钮事件
        //execMenuAction(dataAction, optLogItemId);
      }
    });
    //处理发布的链接
    optLogItemDom.children(".mediacontent").children(".tvlinkcontent").click(function () {
      window.location.replace(this.getAttribute("data-url") || "");
    });
    //TODO 处理查看图片 查看评论 查看语音 视频等事件
  }


  function convertOptJson2html(objJson) {
    console.log(objJson);
    return TemplateEngine(_optLogTemplate, objJson);
    // return TemplateEngine(optLogTemplate, objJson);
  }


  var _optLogTemplate = "" +
'<div class="clearfix" data-timestamp="<%this.timestamp%>" id="info_<%this.id%>">' +
  '<!--个人信息关注TA-->' +
  '<ul class="biao ">' +
    '<li>' +
      '<img src="<%this.avatar%>" class="tupian fl">' +
    '</li>' +
    '<li class="center fl">' +
      '<br>' +
      '<div class="nicheng biao-bin"><%this.proxy%></div>' +
      '<div class="jibie b biao-bin"><%this.level%></div>' +
      '<br>' +
      '<div class="time  biao-bin"><%this.readtime%></div>' +
      '<div class="urser biao-bin"><%this.customer%>报名了</div>' +
     '</li>' +
    '<li class="fr"><div class="bk-balck ta">关注TA</div></li>' +
  '</ul>' +
  '<!--产品-->' +
    '<div>' +
        '<div class="chanpin">' +
            '<a href="<%this.data_url%>"><img src="<%this.image%>"></a>' +
        '</div>' +
        '<ul>' +
          '<li class="text1"><%this.title%></li>' +
          '<li class="text2"><%this.description%>......<a href="<%this.data_url%>">更多&gt;&gt;</a></li>' +
        '</ul>' +
          '<img src="http://static.tongshijia.com/static/opt/images/fenxiang.png" class="img fl">' +
        '<div class="fenxian fl">分享</div>' +
        '<div class="c fr">' +
          '<div class="bm bin">报名(9)</div>' +
          '<div class="pl bin">评论(9)</div>' +
        '</div>' +
     '</div>' +
'</div>';

  var TemplateEngine = function (html, options) {
    var re = /<%(.+?)%>/g,
      reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
      code = 'with(obj) { var r=[];\n',
      cursor = 0,
      result;
    var add = function (line, js) {
      js ? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
        (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
      return add;
    }
    while (match = re.exec(html)) {
      add(html.slice(cursor, match.index))(match[1], true);
      cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));
    code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, '');
    try {
      result = new Function('obj', code).apply(options, [options]);
    }
    catch (err) {
      console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n");
    }
    return result;
  };
});
