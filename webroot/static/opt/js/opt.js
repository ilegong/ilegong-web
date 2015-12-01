$(document).ready(function () {
  var $logListDiv = $('#maindiv');
  var logListDom = $logListDiv[0];
  var $loadingDiv = $('#dataloadingdiv');
  var loadingDivDom = $loadingDiv[0];
  var $backTopBtn = $('#topiconbut');
  var getOptLogUrl = '/share_opt/fetch_opt_list_data.json';
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
      setTimeout(checkDataShow, 200);
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
    var st = logListDom.scrollTop;
    var ch = logListDom.clientHeight;
    var sh = logListDom.scrollHeight;
    if (sh - (st + ch) < 100) {
      loadOptLogData(checkDataShow);
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
      var list = data['opt_logs'] || [];
      var users = data['combine_data']['users'] || {};
      var levels = data['combine_data']['users_level'] || {};
      var share_user_map = data['combine_data']['share_user_buy_map'] || {};
      var user_fans_extra = data['combine_data']['user_fans_extra'] || {};
      var nowTimeStamp = data['nowTimeStamp'];
      for (var i = 0; i < list.length; i++) {
        var objJson = list[i];
        var objJsonUserId = objJson['user_id'];
        var objJsonUserInfo = users[objJsonUserId];
        var objJsonUserLevel = levels[objJsonUserId];
        if (objJsonUserInfo) {
          objJsonUserInfo['fans_count'] = user_fans_extra[objJsonUserId];
        }
        objJson['user_info'] = objJsonUserInfo;
        objJson['level_data'] = objJsonUserLevel;
        parseInfoJsonObj(objJson, nowTimeStamp);
        var buy_user_ids = share_user_map[objJson['obj_id']];
        if (buy_user_ids && buy_user_ids.length > 0) {
          var $likeContent = pareseLikeInfos(buy_user_ids, users);
          $('#info_' + objJson['id'], $logListDiv).append($likeContent);
        }
        document.getElementById("info_" + objJson.id).style.borderBottom = "1px solid #dfdfdd";
      }
      if (last_timestamp == 0) {
        checkDataShow();
      }
      last_timestamp = data['last_timestamp'];
      oldest_timestamp = data['oldest_timestamp'];
      loadDataFlag = 0;
      $loadingDiv.hide();
    };
    $.getJSON(getOptLogUrl, reqParams, callbackFunc);
  }

  function lazyLoadImg() {
    var $optLogs = $('div.postinfo', $logListDiv);
    var $optLogsHeadImgs = $('a.heada img.headimg', $optLogs);
    $optLogsHeadImgs.lazyload(lazyLoadOptions);
    var $mediaContent = $('div.mediacontent');
    $mediaContent.lazyload(lazyLoadOptions);
    var $pureMediaContent = $('div.pure-mediacontent');
    $pureMediaContent.lazyload(lazyLoadOptions);
    var $mediaContentLinkContent = $('.linkcontent img', $mediaContent);
    $mediaContentLinkContent.lazyload(lazyLoadOptions);
    var $pureMediaContentLinkContent = $('.linkcontent img', $pureMediaContent);
    $pureMediaContentLinkContent.lazyload(lazyLoadOptions);
    var $likeContent = $('.zancontent', $optLogs);
    var $commentContent = $('.talkcontent', $optLogs);
    var $likeHeadImg = $('.zheadimg', $likeContent);
    $likeHeadImg.lazyload(lazyLoadOptions);
    var $commentHeadImg = $('.theadimg', $commentContent);
    $commentHeadImg.lazyload(lazyLoadOptions);
  }

  function checkDataShow() {
    lazyLoadImg();
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
    return TemplateEngine(optLogTemplate, objJson);
  }

  var optLogTemplate = '<div class="postinfo" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(223, 223, 221);" data-show="0" data-infoid="<%this.id%>" data-timestamp="<%this.timestamp%>" data-myzan="0" id="info_<%this.id%>">' +
    '<a class="heada" <%if(this.user_info){%>href="/weshares/user_share_info/<%this.user_info.id%>"<%}else{%>href="javascript:void(0)"<%}%>>' +
    '<img class="headimg" src="/static/opt/images/default.png" <%if(this.user_info){%>data-original="<%this.user_info.image%>"<%}else{%>data-original="/static/opt/images/default.png"<%}%>>' +
    '<%if(this.user_info&&this.user_info.fans_count > 100){%><img src="/static/weshares/images/v.png" class="user-is-vip-user-tag"><%}%>' +
    '<%if(this.level_data){%><span class="user-is-proxy-tag"><%this.level_data.level_name%></span><%}%>' +
    '</a>' +
    '<a href="javascript:void(0)" class="nickname"><%if(this.user_info){%><%this.user_info.nickname%><%}else{%>匿名用户<%}%></a>' +
    '<font class="jibie"><%this.data_type_tag%></font>' +
    '<font class="jibie" style="float:right"></font>' +
    '<div style="height:0px;clear:both"></div>' +
    '<%if(this.reply_content) {%> <div class="fontcontent"><%this.reply_content%></div> <%}%>' +
    '<%if(this.reply_content) {%><a href="javascript:void(0)" class="expcontenta">全文</a><%}%>' +
    '<div <%if(!this.reply_content){%>class="pure-mediacontent"<%}%> <%if(this.reply_content){%>class="mediacontent"<%}%>><a href="<%this.data_url%>" data-url="<%this.data_url%>" class="linkcontent">' +
    '<img src="/static/opt/images/pyqlink.png" data-original="<%this.thumbnail%>" style="width:40px;height:40px">' +
    '<div class="linkfontcontent"><%this.memo%></div>' +
    '</a> <div style="height:0px;clear:both"></div>' +
    '</div>' +
    '<div class="date"><%this.created%></div>' +
    '<a href="javascript:void(0)" class="controlimg" style="visibility: hidden;">' +
    '<img src="/static/opt/images/repicon.png"></a>' +
    '<div style="height:0px;clear:both"></div>' +
    '</div>';

  var optLogLikeTemplate = '<div data-count="<%this.count%>" class="zancontent" style="margin-left:48px;min-height:30px;display:block;">' +
    '<img class="zanicon" src="/static/opt/images/zanicon.png">' +
    '<%if(this.count<=10){%>'+
    '<%for(var i=0;i<this.count;i++){%>' +
    '<a href="/weshares/user_share_info/<%this.userIds[i]%>"><img class="zheadimg" src="/static/opt/images/default.png" data-original="<%this.users[this.userIds[i]].image%>" title="<%this.users[this.userIds[i]].nickname%>"></a>' +
    '<%}%>' +
    '<%}else{%>' +
    '<%for(var i=0;i<10;i++){%>' +
    '<a href="/weshares/user_share_info/<%this.userIds[i]%>"><img class="zheadimg" src="/static/opt/images/default.png" data-original="<%this.users[this.userIds[i]].image%>" title="<%this.users[this.userIds[i]].nickname%>"></a>' +
    '<%}%>' +
    '<%}%>'+
    '<%if(this.count>10){%>' +
    '<font class="zaninfo"> ...共<%this.count%>人报名</font>' +
    '<%}%>' +
    '<div style="height:0px;clear:both"></div></div>';

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