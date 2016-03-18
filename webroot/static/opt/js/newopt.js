$(document).ready(function () {
  var $logListDiv = $('#maindiv');
  var logListDom = $logListDiv[0];
  var $loadingDiv = $('#dataloadingdiv');
  var loadingDivDom = $loadingDiv[0];
  var $backTopBtn = $('#topiconbut');
  var getOptLogUrl = '/share_opt/newfetch_opt_list_data.json';
  var loadDataFlag = 0;
  var loadDataTimes = 0;
  var filterVal = 0;
  var oldest_timestamp = 0;
  var last_timestamp = 0;
  var bottomTimeStamp = 0;
  var currentUserId = $logListDiv.data('uid');
  var processingSubmit = false;

  var lazyLoadOptions = {
    event: "scrollstop",
    vertical_only: true,
    no_fake_img_loader: true,
    container: $logListDiv
  };

  function lazyLoadImg() {
    var $optLogs = $('div.list_item', $logListDiv);
    var $optLogsHeadImgs = $('ul.biao li img', $optLogs);
    $optLogsHeadImgs.lazyload(lazyLoadOptions);
    var $mediaContent = $('div.chanpin a img');
    $mediaContent.lazyload(lazyLoadOptions);
  }

  $backTopBtn.on('click', function () {
    $logListDiv.scrollTop(0);
  });
  $logListDiv.on('scroll', function () {
    loadMoreDataWithScrollY();
    setTimeout(lazyLoadImg, 200);
  });
  initOptLogView();

  function initOptLogView() {
    loadOptLogData();
  }

  /**
   * loadMoreDataWithScrollY 显示或者隐藏回到顶部按钮
   * @access public
   * @return void
   */
  function loadMoreDataWithScrollY() {
    var topVal = logListDom.scrollTop;
    if (topVal > 150) {
      $backTopBtn.show();
    } else {
      $backTopBtn.hide();
    }
    if (logListDom.scrollHeight - topVal < 150 + logListDom.clientHeight) {
      loadOptLogData();
    }
  }

  function subUser(userId, callback) {
    processingSubmit = true;
    $.getJSON('/weshares/subscribe_sharer/' + userId + "/" + currentUserId, callback);
  }

  function unSubUser(userId, callback) {
    processingSubmit = true;
    $.getJSON('/weshares/unsubscribe_sharer/' + userId + "/" + currentUserId, callback);
  }

  function afterSubUser() {

  }

  function loadOptLogData() {
    if (loadDataFlag == 1) {
      return false;
    }
    $loadingDiv.show();
    loadDataFlag = 1;
    if (oldest_timestamp != 0 && bottomTimeStamp == oldest_timestamp) {
      $loadingDiv.hide();
      return false;
    }
    var followed = $('#show-only-followed').hasClass('activity') ? 1 : 0;
    var reqParams = {
      'followed': followed,
      "type": filterVal,
      "time": bottomTimeStamp,
      "limit": 10
    };
    var callbackFunc = function (data) {
      $loadingDiv.find('div').text('数据加载中...');
      if (data.error) {
        loadDataFlag = 0;
        $loadingDiv.find('div').text('没有获取到(更多)有效数据!!!');
        return;
      }
      var list = data['opt_logs'];
      var nowTimeStamp = data['nowTimeStamp'];
      for (var i = 0; i < list.length; i++) {
        var objJson = list[i];
        bottomTimeStamp = objJson.time;
        objJson['dataMark'] = loadDataTimes;
        parseInfoJsonObj(objJson, nowTimeStamp);
      }
      last_timestamp = data['last_timestamp'];
      oldest_timestamp = data['oldest_timestamp'];
      if (last_timestamp == 0) {
        lazyLoadImg();
      }
      loadDataFlag = 0;
      $loadingDiv.hide();
      bindToogleUnSubClick();
      bindSubEvent();
      bindUnSubEvent();
      loadDataTimes = loadDataTimes + 1;
    };
    $.getJSON(getOptLogUrl, reqParams, callbackFunc);
  }

  function parseInfoJsonObj(objJson) {
    var html = convertOptJson2html(objJson);
    loadingDivDom.insertAdjacentHTML('BeforeBegin', html);
  }

  function convertOptJson2html(objJson) {
    return TemplateEngine(_optLogTemplate, objJson);
  }

  $('#show-all').on('click', function () {
    if ($(this).hasClass('activity')) {
      return true;
    } else {
      // 切换回到全部显示列表
      $('#show-only-followed').removeClass('activity');
      $('#show-all').addClass('activity');
      // 重新获取列表
      bottomTimeStamp = 0;
      $('div.clearfix.list_item').remove();
      initOptLogView();
    }
  });
  $('#show-only-followed').on('click', function () {
    if ($(this).hasClass('activity')) {
      return true;
    } else {
      // 切换回到显示关注列表
      $('#show-all').removeClass('activity');
      $('#show-only-followed').addClass('activity');
      // 重新获取列表
      bottomTimeStamp = 0;
      $('div.clearfix.list_item').remove();
      initOptLogView();
    }
  });

  function processSubEvent($el) {
    var userId = $el.data('user-id');

    function callback(data) {
      //toggle dom
      if (data['success']) {
        var $parent = $el.parent();
        $el.remove();
        $parent.append('<div class="bk-balck ta follow" follow="true">已关注</div><div class="bk-balck ta unfollow hidden un-sub-user-btn" follow="true" data-user-id="' + userId + '">取消关注</div>');
        $parent.addClass('un-sub-group');
        //bind event
        $('div.follow', $parent).on('click', function (e) {
          e.preventDefault();
          $('div.unfollow', $parent).toggle();
        });
        $('div.un-sub-user-btn', $parent).on('click', function (e) {
          e.preventDefault();
          var $el = $(this);
          processUnSubEvent($el)
        });
      }
      processingSubmit = false;
    }

    subUser(userId, callback);
  }

  function bindSubEvent() {
    $('div.sub-user-btn', $('div.list_item_' + loadDataTimes)).on('click', function (e) {
      e.preventDefault();
      var $el = $(this);
      processSubEvent($el);
    });
  }

  function processUnSubEvent($el) {
    var userId = $el.data('user-id');
    function callback(data) {
      //toggle dom
      if (data['success']) {
        var $parent = $el.parent();
        $parent.empty();
        $parent.append('<div class="bk-balck ta sub-user-btn" follow="false" data-user-id="' + userId + '">关注TA</div>');
        $parent.removeClass('un-sub-group');
        $('div.sub-user-btn', $parent).on('click', function (e) {
          e.preventDefault();
          var $el = $(this);
          processSubEvent($el);
        })
      }
      processingSubmit = false;
    }
    unSubUser(userId, callback);
  }

  function bindUnSubEvent() {
    $('li.un-sub-group .un-sub-user-btn', $('div.list_item_' + loadDataTimes)).on('click', function (e) {
      e.preventDefault();
      var $el = $(this);
      processUnSubEvent($el);
    });
  }

  function bindToogleUnSubClick() {
    $('li.un-sub-group .follow', $('div.list_item_' + loadDataTimes)).on('click', function (e) {
      e.preventDefault();
      $('div.unfollow', $(this).parent()).toggle();
    });
  }

  var _optLogTemplate =
    '<div class="clearfix list_item list_item_<%this.dataMark%>" id="info_<%this.share_id%>">' +
    '<ul class="biao ">' +
    '<li class="fl">' +
    '<img src="<%this.avatar%>" class="tupian fl" onerror="this.src=\'http://static.tongshijia.com/avatar/s/default.jpg\'">' +
    '</li>' +
    '<li class="center fl">' +
    '<br>' +
    '<div class="nicheng biao-bin"><%this.proxy%></div>' +
    '<div class="jibie b biao-bin"><%this.level%></div>' +
    '<br>' +
      '<div class="time  biao-bin"><%this.readtime%><span><%this.customer%>报名了</span></div>' +
    '</li>' +
    '<%if(!this.check_relation){%>' +
    '<li class="fr">' +
    '<div class="bk-balck ta sub-user-btn" follow="<%this.check_relation%>" data-user-id="<%this.proxy_id%>">关注TA</div>' +
    '</li>' +
    '<%}else{%>' +
    '<li class="fr un-sub-group">' +
    '<div class="bk-balck ta follow" follow="<%this.check_relation%>">已关注</div>' +
    '<div class="bk-balck ta unfollow hidden un-sub-user-btn" follow="<%this.check_relation%>" data-user-id="<%this.proxy_id%>">取消关注</div>' +
    '</li>' +
    '<%}%>' +
    '</ul>' +
    '<div>' +
    '<div class="chanpin">' +
    '<a href="<%this.data_url%>"><img src="<%this.image%>"></a>' +
    '</div>' +
    '<ul>' +
    '<li class="text1"><a href="<%this.data_url%>"><%this.title%></a></li>' +
      '<li class="text2"><a href="<%this.data_url%>"><%this.description%>' +
        '<%if (this.description_more) {%>更多&gt;&gt;<%}%></a>' +
      '</li>' +
    '</ul>' +
    //'<img src="http://static.tongshijia.com/static/opt/images/fenxiang.png" class="img fl">' +
    //'<div class="fenxian fl">分享</div>' +
    '<div class="c fr">' +
    '<div class="bm bin">报名(<%this.baoming%>)</div>' +
    '<div class="pl bin">浏览(<%this.liulan%>)</div>' +
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
