$(document).ready(function () {
  //show-focus-modal-dialog
  //show-fans-modal-dialog
  //show-comment-modal-dialog
  var $userInfoPlaceHolder = $('#user-info-placeholder');
  var $showCommentLi = $('#show-comment-modal-dialog');
  var $commentModalDialog = $('#commentListModal');
  var $showUpdateUserInfoDialog = $('#open-update-user-info-dialog');
  var $showUpdatePasswordDialog = $('#open-update-user-password-dialog');
  var $updateUserPasswordDialog = $('#updateUserPassword');
  var $saveUserPassword = $('#save-user-password');
  var $changeUserInfoDialog = $('#updateUserInfo');
  var $saveUserIntro = $('#save-user-intro');
  var $userInfoTextArea = $('#user-info-textarea');
  var $userNicknameInput = $('#user-nickname');
  var $userSharesFirstTab = $('#share-nav-tab li:first a');
  var $userId = $('#user-id', $changeUserInfoDialog);
  var $deleteShareBtns = $('button[name="deleteShare"]');
  var processSub = false;
  var processUnSub = false;
  var processSavePassword = false;

  $(function() {
    $("img.lazy").lazyload({
      event : "sporty"
    });
  });

  $(window).bind("load", function() {
    setTimeout(function() {
      $("img.lazy").trigger("sporty")
    }, 2000);
  });

  $userSharesFirstTab.trigger('click');
  $showUpdateUserInfoDialog.on('click', function () {
    $changeUserInfoDialog.modal({show: true, backdrop: 'static'});
  });
  $showCommentLi.on('click', function () {
    $commentModalDialog.modal({show: true, backdrop: 'static'});
  });

  $showUpdatePasswordDialog.on('click', function () {
    $updateUserPasswordDialog.modal({show: true, backdrop: 'static'});
  });
  $deleteShareBtns.on('click', function (e) {
    e.preventDefault();
    var $me = $(this);
    var shareId = $me.data('id');
    bootbox.setDefaults("locale", "zh_CN");
    bootbox.confirm({
      size: 'small',
      message: "确定删除？",
      callback: function (result) {
        if (result) {
          $.getJSON('/weshares/delete_share/' + shareId, function (data) {
            if (data['success']) {
              $me.parent().parent('div.media').remove();
            } else {
              alert('删除失败！');
            }
          });
        }
      }
    })
  });
  $saveUserPassword.on('click', function (e) {
    e.preventDefault();
    if (processSavePassword) {
      return;
    }
    processSavePassword = true;
    var $passwordE = $('#user-password', $updateUserPasswordDialog);
    var $rePasswordE = $('#re-user-password', $updateUserPasswordDialog);
    var password = $passwordE.val();
    var rePassword = $rePasswordE.val();
    if (!password || !password.trim()) {
      alert('输入密码');
      return;
    }
    if (!rePassword || !rePassword.trim()) {
      alert('输入确认密码');
      return;
    }
    if (password != password) {
      alert('两次输入密码不同');
      return;
    }
    $.post('/users/setpassword.json', {password: password}, function (data) {
      processSavePassword = false;
      if (data['success']) {
        $updateUserPasswordDialog.modal('hide');
        $passwordE.val('');
        $rePasswordE.val('');
      } else {
        if (data['reason'] == 'not_login') {
          alert('当前用户不存在');
        }
        if (data['reason'] == 'password_empty') {
          alert('密码为空');
        }
        if (data['reason'] == 'server_error') {
          alert('系统出错，请联系客服。');
        }
      }
    }, 'json');

  });
  $saveUserIntro.on('click', function (e) {
    e.preventDefault();
    var nickname = $userNicknameInput.val();
    var userIntro = $userInfoTextArea.val();
    var userId = $userId.val();
    if (!nickname || !nickname.trim()) {
      alert('请输入昵称');
      return;
    }
    if (!userIntro || !userIntro.trim()) {
      alert('请输入个人介绍');
      return false;
    }
    $.post('/users/update_user_intro', {
      'user_intro': userIntro, 'user_id': userId, 'user_nickname': nickname
    }, function (data) {
      if (data['success']) {
        $userInfoPlaceHolder.text(userIntro);
      }
      $changeUserInfoDialog.modal('hide');
    }, 'json');
  });
  $('button.btn-sub-sharer').on('click', function (e) {
    e.preventDefault();
    if (processSub) {
      return;
    }
    processSub = true;
    var $me = $(this);
    var sharer_id = $me.data('sharer-id');
    var user_id = $me.data('user-id');
    $.getJSON('/weshares/subscribe_sharer/' + sharer_id + '/' + user_id, function (data) {
      if (data['success']) {
        processSub = false;
        $('#unsub-sharer').show();
        $('#sub-sharer').hide();
      } else {
        if (data['reason'] = 'not_sub') {
          processSub = false;
          alert('请先关注朋友说微信公众号！');
          window.location.href = "https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=403992659&idx=1&sn=714a1a5f0bb4940f895e60f2f3995544";
        }
      }
    });
  });
  $('a.btn-unsub-sharer').on('click', function (e) {
    e.preventDefault();
    if (processUnSub) {
      return;
    }
    processUnSub = true;
    var $me = $(this);
    var sharer_id = $me.data('sharer-id');
    var user_id = $me.data('user-id');
    $.getJSON('/weshares/unsubscribe_sharer/' + sharer_id + '/' + user_id, function (data) {
      if (data['success']) {
        if (data['success']) {
          processUnSub = false;
          $('#unsub-sharer').hide();
          $('#sub-sharer').show();
        }
      }
    });
  });
  $('div[name="share_item"]').on('click', function () {
    var $me = $(this);
    var id = $me.data('id');
    var type = $me.data('type');
    var href = '/weshares/view/' + id;
    if(type=='order'){
      href = '/weshares/share_order_list/'+id;
    }
    window.location.href = href;
  });
  $('div[name="pintuan_item"]').on('click', function () {
    var $me = $(this);
    var id = $me.data('id');
    var tagId = $me.data('tag-id');
    window.location.href = '/pintuan/detail/' + id + '?tag_id=' + tagId;
  });
});