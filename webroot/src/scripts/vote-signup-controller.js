(function (window, angular, wx) {
  angular.module('weshares')
    .controller('VoteSignupCtrl', VoteSignupCtrl);

  function VoteSignupCtrl($http, $log, $templateCache, $rootScope, Utils) {
    var vm = this;
    vm.signup = signup;
    vm.chooseAndUploadImage = chooseAndUploadImage;
    vm.uploadImage = uploadImage;
    vm.deleteImage = deleteImage;
    vm.thumbImage = thumbImage;
    vm.viewImage = viewImage;
    active();
    function active() {
      vm.candidate = {
        title: '',
        mobileNum: '',
        images: ['http://static.tongshijia.com/images/index/2016/07/19/8d65ff6e-4d87-11e6-b8d8-00163e1600b6.jpg'],
        description: ''
      };
    }

    function signup(eventId) {
      if (Utils.isBlank(vm.candidate.title)) {
        alert('请输入作品名称');
        return false;
      }
      if (Utils.isBlank(vm.candidate.mobileNum)) {
        alert('请输入手机号码');
        return false;
      }
      if (!Utils.isMobileValid(vm.candidate.mobileNum)) {
        alert('手机号码无效');
        return false;
      }
      if (vm.candidate.images.length == 0) {
        alert('请上传图片');
        return false;
      }
      if (Utils.isBlank(vm.candidate.description)) {
        alert('请输入作品描述');
        return false;
      }

      if (vm.processing) {
        return;
      }

      vm.processing = true;
      var data = {
        "title": vm.candidate.title,
        "mobileNum": vm.candidate.mobileNum,
        "description": vm.candidate.description,
        'images': vm.candidate.images.join('|')
      };
      $http.post('/vote/upload_candidate/' + eventId + '.json', data).success(function (data) {
        vm.processing = false;
        if (data['success']) {
          alert('报名成功', window.location.href = '/vote/vote_event_view/' + eventId);
          return;
        }

        if (data['reason'] == 'not login') {
          alert('请登录', window.location.href = '/users/login.html?referer=' + encodeURIComponent("/vote/sign_up/" + eventId));
        }
        if (data['reason'] == 'server error') {
          alert('上传失败请联系客服');
        }
        if (data['reason'] == 'not subscribed') {
          alert('请先关注微信服务号', window.location.href = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=209556231&idx=1&sn=2a60e7f060180c9ecd0792f89694defb#rd');
        }
        if (data['reason'] == 'has sign') {
          var candidateId = data['candidate_id'];
          alert('已经报过名了', window.location.href = '/vote/candidate_detail/' + candidateId + '/' + eventId);
        }
      }).error(function () {
        vm.processing = false;
        alert('上传失败请联系客服');
      });
    }

    function chooseAndUploadImage() {
      wx.chooseImage({
        success: function (res) {
          vm.uploadImage(res.localIds);
        },
        fail: function (res) {
          alert('选择照片错误，请重试');
        }
      });
    }

    function uploadImage(localIds) {
      function upload(i) {
        wx.uploadImage({
          localId: localIds[i],
          isShowProgressTips: 1,
          success: function (res) {
            $http.get('/downloads/download_wx_img?media_id=' + res.serverId).success(function (data, status, headers, config) {
              var imageUrl = data['download_url'];
              if (!imageUrl || imageUrl == 'false') {
                return;
              }
              alert(imageUrl);
              vm.candidate.images.push(imageUrl);
            }).error(function (data) {
              alert('下载图片失败');
            });
            if (i < localIds.length) {
              upload(i+1);
            }
          },
          fail: function (res) {
            if (i < localIds.length) {
              upload(i+1);
            }
          }
        });
      }

      if(localIds.length > 0){
        upload(0);
      }
    }

    function deleteImage(image) {
      vm.candidate.images = _.without(vm.candidate.images, image);
    }

    function thumbImage(image){
      return image.replace('images/', 'images/s/');
    }
    function viewImage(imageUrl) {
      wx.previewImage({
        current: imageUrl,
        urls: vm.candidate.images
      });
    }
  }
})(window, window.angular, window.wx);