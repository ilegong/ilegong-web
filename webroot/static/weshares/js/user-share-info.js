$(document).ready(function () {
  //show-focus-modal-dialog
  //show-fans-modal-dialog
  //show-comment-modal-dialog
  var $userInfoPlaceHolder = $('#user-info-placeholder');
  var $showCommentLi = $('#show-comment-modal-dialog');
  var $commentModalDialog = $('#commentListModal');
  var $showFansLi = $('#show-fans-modal-dialog');
  var $fansModalDialog = $('#fansListModal');
  var $showFocusLi = $('#show-focus-modal-dialog');
  var $focusModalDialog = $('#focusListModal');
  var $showUpdateUserInfoDialog = $('#open-update-user-info-dialog');
  var $showUpdatePasswordDialog = $('#open-update-user-password-dialog');
  var $updateUserPasswordDialog = $('#updateUserPassword');
  var $saveUserPassword = $('#save-user-password');
  var $changeUserInfoDialog = $('#updateUserInfo');
  var $saveUserIntro = $('#save-user-intro');
  var $userInfoTextArea = $('#user-info-textarea');
  var $userNicknameInput = $('#user-nickname');
  var $userId = $('#user-id', $changeUserInfoDialog);
  var processSub = false;
  var processUnSub = false;
  var processSavePassword = false;
  $showUpdateUserInfoDialog.on('click', function () {
    $changeUserInfoDialog.modal({show: true, backdrop: 'static'});
  });
  $showCommentLi.on('click', function () {
    $commentModalDialog.modal({show: true, backdrop: 'static'});
  });
  $showFansLi.on('click', function () {
    $fansModalDialog.modal({show: true, backdrop: 'static'});
  });
  $showFocusLi.on('click', function () {
    $focusModalDialog.modal({show: true, backdrop: 'static'});
  });
  $showUpdatePasswordDialog.on('click', function () {
    $updateUserPasswordDialog.modal({show: true, backdrop: 'static'});
  });
  $saveUserPassword.on('click', function(e){
    e.preventDefault();
    if(processSavePassword){
      return;
    }
    processSavePassword = true;
    var $passwordE = $('#user-password', $updateUserPasswordDialog);
    var $rePasswordE = $('#re-user-password', $updateUserPasswordDialog);
    var password = $passwordE.val();
    var rePassword = $rePasswordE.val();
    if(!password||!password.trim()){
      alert('输入密码');
      return;
    }
    if(!rePassword||!rePassword.trim()){
      alert('输入确认密码');
      return;
    }
    if(password!=password){
      alert('两次输入密码不同');
      return;
    }
    $.post('/users/setpassword.json',{password:password},function(data){
      processSavePassword = false;
      if(data['success']){
        $updateUserPasswordDialog.modal('hide');
        $passwordE.val('');
        $rePasswordE.val('');
      }else{
        if(data['reason'] == 'not_login'){
          alert('当前用户不存在');
        }
        if(data['reason'] == 'password_empty'){
          alert('密码为空');
        }
        if(data['reason'] == 'server_error'){
          alert('系统出错，请联系客服。');
        }
      }
    },'json');

  });
  $saveUserIntro.on('click', function(e){
    e.preventDefault();
    var nickname = $userNicknameInput.val();
    var userIntro = $userInfoTextArea.val();
    var userId = $userId.val();
    if(!nickname||!nickname.trim()){
      alert('请输入昵称');
      return;
    }
    if(!userIntro||!userIntro.trim()){
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
  $('button.btn-sub-sharer').on('click', function(e){
    e.preventDefault();
    if(processSub){
      return;
    }
    processSub = true;
    var $me = $(this);
    var sharer_id = $me.data('sharer-id');
    var user_id  = $me.data('user-id');
    $.getJSON('/weshares/subscribe_sharer/'+sharer_id+'/'+user_id, function(data){
      if(data['success']){
        processSub = false;
        $('#unsub-sharer').show();
        $('#sub-sharer').hide();
      }else{
        if(data['reason'] = 'not_sub'){
          processSub = false;
          alert('请先关注朋友说微信公众号！');
          window.location.href = "http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400154588&idx=1&sn=5568f4566698bacbc5a1f5ffeab4ccc3";
        }
      }
    });
  });
  $('a.btn-unsub-sharer').on('click', function(e){
    e.preventDefault();
    if(processUnSub){
      return;
    }
    processUnSub = true;
    var $me = $(this);
    var sharer_id = $me.data('sharer-id');
    var user_id  = $me.data('user-id');
    $.getJSON('/weshares/unsubscribe_sharer/'+sharer_id+'/'+user_id, function(data){
      if(data['success']){
        if(data['success']){
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
    window.location.href = '/weshares/view/' + id;
  });
});