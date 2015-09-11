$(document).ready(function () {
  var $logListDiv = $('#maindiv');
  var logListDom = $logListDiv[0];
  var $loadingDiv = $('#dataloadingdiv');
  var loadingDivDom = $loadingDiv[0];
  var $backTopBtn = $('#topiconbut');
  var getTopicOptLogUrl = '';
  var showDataModel = "single";
  var loadDataFlag = 0;
  var filterVal = 0;
  var topicJson = {};
  var l2CacheInfoArray = [];
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

  function showNewOptLogInfo() {

  }

  function initOptLogView() {

  }

  function loadMoreDataWithScrollY() {
    var topVal = $logListDiv.scrollTop;
    var elmHeight = $logListDiv.clientHeight;
    if (topVal > 150) {
      $backTopBtn.show();
    } else {
      $backTopBtn.hide();
    }
    var lastChildObj = $logListDiv.lastElementChild;
    while (lastChildObj && lastChildObj.nodeName != "DIV") {
      lastChildObj = lastChildObj.previousElementSibling;
    }
    if (lastChildObj != null) {
      var lastChildObjTop = lastChildObj.offsetTop;
      var lastChildObjHeight = 0;
      if (topVal + elmHeight > (lastChildObjTop + lastChildObjHeight)) {
        loadOptLogData();
      }
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
      loadOptLogData();
    }
  }

  function filterOptLogData() {

  }

  function invokeAjax(url, callback, reqParams, method) {
    var headers = {};
    method = method || 'GET';
    $.ajax({
      headers: headers,
      type: method,
      url: url,
      data: reqParams || {},
      dataType: 'json',
      success: function (data) {
        callback(data);
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        callback(null, XMLHttpRequest, textStatus, errorThrown, url);
      }
    });
  }

  function loadOptLogData(callback) {
    if (loadDataFlag == 1) {
      return false;
    } else if (loadDataFlag == 2) {
      return false;
    }
    loadDataFlag = 1;
    var timeStampInfoId = "";
    var bottomTimeStamp = "0";
    var lastInfoEl = $loadingDiv[0].previousElementSibling;
    if (lastInfoEl) {
      while (lastInfoEl && lastInfoEl.nodeType
      && (lastInfoEl.nodeType != 1 || (timeStampInfoId = lastInfoEl.getAttribute("data-infoid")) == null)) {
        lastInfoEl = lastInfoEl.previousElementSibling;
      }
      if (lastInfoEl && timeStampInfoId) {
        bottomTimeStamp = lastInfoEl.getAttribute("data-timestamp");
      }
    }

    var reqParams = {
      "filterVal": filterVal,
      "bottomTimeStampInfoId": timeStampInfoId,
      "bottomTimeStamp": bottomTimeStamp,
      "discuss": "no  "
    };

    if (topicJson && topicJson.topicPermit) {
      reqParams["topicPermit"] = topicJson.topicPermit;
    }

    if (l2CacheInfoArray && l2CacheInfoArray.length > 0 && bottomTimeStamp != "0") {
      var l2CacheIds = l2CacheInfoArray.shift().index;
      reqParams["l2CacheIds"] = l2CacheIds;
    }

    var callbackFunc = function (data) {
      if (data['l2Cache']) {
        l2CacheInfoArray = data['l2Cache'];
      }
      var list = data.list || [];
      if (list.length < 10) {
        if (!reqParms["l2CacheIds"]) {
          loadDataFlag = 2;
          $loadingDiv.style.display = "none";
        }
      }
      var comment = data.comment || [];
      var nowTimeStamp = data['nowTimeStamp'];
      var cacheHash = {};
      for (var i = 0; i < comment.length; i++) {
        cacheHash[comment[i]["optLogId"]] = comment[i];
      }
      if (callback) {
        callback();
      }
      for (var i = 0; i < list.length; i++) {
        var objJson = list[i];
        var commentObj = cacheHash[objJson.id];
        if (commentObj) {
          objJson["comment"] = commentObj;
          if (objJson.topicPermit != null) {
            commentObj["topicPermit"] = objJson.topicPermit;
          }
        }
        parseJsonObj(objJson, nowTimeStamp);
        if (true || showDataModel == "list") {
          document.getElementById("info_" + objJson.id).style.borderBottom = "1px solid #dfdfdd";
        }
      }
      if (topicJson.id) {
        getLikeAndComment(comment);
      } else {
        var allPlComment = [];
        var rePlComment = [];
        for (var i = 0; i < comment.length; i++) {
          if (comment[i]["topicPermit"] == "2") {
            allPlComment.push(comment[i]);
          } else if (comment[i]["topicPermit"] == "1") {
            rePlComment.push(comment[i]);
          }
        }
        topicJson.topicpermit = "1";
        getLikeAndComment(rePlComment);
        topicJson.topicpermit = "2";
        getLikeAndComment(allPlComment);
      }
    };
    invokeAjax(getTopicOptLogUrl, callbackFunc, reqParams);
  }

  function parseJsonObj(objJson) {

  }

  function getLikeAndComment(comment) {

  }

  function checkDataShow() {
    var dataShowObjs = $logListDiv.children("[data-show=0]");
    if (dataShowObjs.length > 0) {
      var scrollTopVal = $logListDiv.scrollTop;
      var heightVal = $logListDiv.clientHeight;
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
          var mediaContentObj = obj.children(".mediacontent");
          var contentAObjs = mediaContentObj.children(".contenta");
          var contentAObjsLen = contentAObjs.length;
          for (var k = 0; k < contentAObjsLen; k++) {
            var contentAimageUrl = contentAObjs[k].getAttribute("data-original");
            if (contentAimageUrl) {
              contentAObjs[k].style.backgroundImage = "url(" + contentAimageUrl + ")";
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

  function parseinfoJSONObj(objJson, nowTimeStamp) {
    var isNew = objJson.isNew;
    var html = convertOptJson2html(objJson, nowTimeStamp);
    if (isNew) {//新信息
      var firstEl = logListDom.firstElementChild;
      if (firstEl) {
        firstEl.insertAdjacentHTML('afterEnd', html);
      } else {
        loadingDivDom.insertAdjacentHTML('BeforeBegin', html);
      }
    } else if (objJson["modify"]) {//修改
      delete objJson["modify"];
      var oldOptLogDom = document.getElementById("info_" + objJson.id);
      if (oldOptLogDom) {
        oldOptLogDom.outerHTML = html;
      }
    } else {
      loadingDivDom.insertAdjacentHTML('BeforeBegin', html);
    }
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


  function convertOptJson2html(objJson, nowTimeStamp){

  }

  var TemplateEngine = function(html, options) {
    var re = /<%(.+?)%>/g,
      reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
      code = 'with(obj) { var r=[];\n',
      cursor = 0,
      result;
    var add = function(line, js) {
      js? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
        (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
      return add;
    }
    while(match = re.exec(html)) {
      add(html.slice(cursor, match.index))(match[1], true);
      cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));
    code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, '');
    try { result = new Function('obj', code).apply(options, [options]); }
    catch(err) { console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n"); }
    return result;
  };
});