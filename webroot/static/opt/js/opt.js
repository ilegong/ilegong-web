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
    if (st + ch == sh) {
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

  function loadOptLogData(callback) {
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
      var nowTimeStamp = data['nowTimeStamp'];
      for (var i = 0; i < list.length; i++) {
        var objJson = list[i];
        var objJsonUserId = objJson['user_id'];
        var objJsonUserInfo = users[objJsonUserId];
        objJson['user_info'] = objJsonUserInfo;
        parseInfoJsonObj(objJson, nowTimeStamp);
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

  function checkDataShow() {
    var dataShowObjs = $logListDiv.children("[data-show=0]");
    if (dataShowObjs.length > 0) {
      var heightVal = logListDom.clientHeight;
      for (var i = 0; i < dataShowObjs.length; i++) {
        var obj = $(dataShowObjs[i]);
        var objOffset = obj.offset();
        var objBottomY = obj.height() + objOffset.top;
        if ((objOffset.top >= 0 && objOffset.top < heightVal)
          || (objBottomY > 0 && objOffset.top < 0)
        ) {
          var headAImageObj = obj.children(".heada").children(".headimg");
          var headAImageUrl = headAImageObj.attr("data-original") || "";
          if (headAImageUrl) {
            headAImageObj.attr("src", headAImageUrl);
            headAImageObj[0].removeAttribute("data-original");
          }
          var mediaContentObj = obj.children(".mediacontent,.pure-mediacontent");
          var contentAObjs = mediaContentObj.children(".linkcontent img");
          var contentAObjsLen = contentAObjs.length;
          for (var k = 0; k < contentAObjsLen; k++) {
            var contentAImageUrl = contentAObjs[k].getAttribute("data-original");
            if (contentAImageUrl) {
              contentAObjs[k].style.backgroundImage = "url(" + contentAImageUrl + ")";
              if (contentAObjs[k].className.indexOf("contentb") != -1) {
                if (contentAObjs[k].getAttribute("data-shareid")) {
                  contentAObjs[k].style.backgroundSize = "cover";
                } else {
                  contentAObjs[k].style.backgroundSize = "contain";
                }
              }
              contentAObjs[k].removeAttribute("data-original");
            }
          }
          var likeContentObj = obj.children(".zancontent");
          var commentContentObj = obj.children(".talkcontent");
          var likeDataCount = parseInt(likeContentObj.attr("data-count")) || 0;
          var commentDataCount = parseInt(commentContentObj.attr("data-count")) || 0;
          if (likeDataCount > 0 || commentDataCount > 0) {
            var likeHeadImgObjs = likeContentObj.find(".zheadimg");
            var commentHeadImgObjs = commentContentObj.find(".theadimg");
            var likeHeadImgObjsLen = likeHeadImgObjs.length;
            var commentHeadImgObjsLen = commentHeadImgObjs.length;
            if (likeHeadImgObjsLen > 0 || commentHeadImgObjsLen > 0) {
              obj.attr("data-show", 1);
              for (var k = 0; k < likeHeadImgObjsLen; k++) {
                var headImgUrl = likeHeadImgObjs[k].getAttribute("data-original") || "";
                likeHeadImgObjs[k].setAttribute("src", headImgUrl);
                likeHeadImgObjs[k].removeAttribute("data-original");
              }
              for (var k = 0; k < commentHeadImgObjsLen; k++) {
                var headImgUrl = commentHeadImgObjs[k].getAttribute("data-original") || "";
                commentHeadImgObjs[k].setAttribute("src", headImgUrl);
                commentHeadImgObjs[k].removeAttribute("data-original");
              }
            }
          } else {
            obj.attr("data-show", 1);
          }
        } else if (objOffset.top > 0) {
          break;
        }
      }
    }
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
    '<a class="heada" href="javascript:void(0)">' +
    '<img class="headimg" src="/static/opt/images/default.png" <%if(this.user_info){%>data-original="<%this.user_info.image%>"<%}else{%>data-original="/static/opt/images/default.png"<%}%>>' +
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