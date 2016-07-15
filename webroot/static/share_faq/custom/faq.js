$(function () {
  //var $chatMsgListContainer = $('#chat-msg-list-container');
  var $chatMsgList = $('#chat-msg-list');
  var senderImg = $('#sender-img').val();
  var $chatInputForm = $('#chat-input-form');
  var $sender = $('#sender', $chatInputForm);
  var senderId = $sender.val();
  var $receiver = $('#receiver', $chatInputForm);
  var $shareId = $('#share-id', $chatInputForm);
  var shareId = $shareId.val();
  var $senderName = $('#senderName', $chatInputForm);
  var sendName = $senderName.val();
  var $content = $('#content', $chatInputForm);
  var $btnSubmit = $('#btn-chat-submit', $chatInputForm);
  var submit = false;
  $('#head-share-title').wordLimit(10);
  updateFaqRead();
  $btnSubmit.on('click', function (e) {
    e.preventDefault();
    if (!$content.val()) {
      alert('请输入内容');
      return;
    }
    var postData = {
      "receiver": $receiver.val(),
      "share_id": $shareId.val(),
      "msg": $content.val()
    };
    if (submit) {
      alert('正在提交');
    }
    submit = true;
    $.post('/share_faq/create_faq/', postData, function (data) {
      if (!data['success']) {
        if (data['reason'] == 'user_bad') {
          alert("你已经被封号，请联系管理员!");
          submit = false;
        }
      } else {
        $chatMsgList.append(genChatItemDom(data));
        $content.val('');
        submit = false;
      }
      //scrollBottom();
    }, 'json');
  });

  function genChatItemDom(data) {
    var domStr = '<li class="even"><a class="user" href="/weshares/user_share_info/' + senderId + '"><img class="img-responsive avatar_" src="' + senderImg + '" alt=""><span class="user-name">' + sendName + '</span></a> <div class="reply-content-box"> <span class="reply-time">' + data["created"] + '</span> <div class="reply-content pr"> <span class="arrow">&nbsp;</span>' + data['msg'] + '</div></div></li>';
    return domStr;
  }

  function updateFaqRead() {
    var updateInfoReadUrl = '/share_faq/update_faq_read/' + shareId + '/' + senderId;
    $.getJSON(updateInfoReadUrl, function (data) {

    });
  }
});
