(function (window, angular, wx) {
    angular.module('weshares')
        .controller('VoteEventCtrl', VoteEventCtrl);

    function VoteEventCtrl($http, $log, $templateCache, $rootScope, Utils) {
        var vm = this;
        vm.vote = vote;
        vm.getVoteText = getVoteText;
        vm.isVoted = isVoted;
        active();
        function active() {
            vm.voted = [];
            vm.reasons = {
                'Not logged': '请先登录哦',
                'Not subscribed': '请先关注服务号进行投票哦',
                'more than five': '每个微信号每天投票不能超过5次哦',
                'already vote': '您已经投过票啦',
                'save wrong': '投票失败，请重试哦'
            }
        }

        function vote(eventId, candidateId) {
            $http.post('/vote/vote/' + candidateId + '/' + eventId).success(function (data) {
                if (data.success) {
                    vm.voted.push(candidateId);
                    alert('投票成功');
                    location.reload();
                    return;
                }

                if (data.reason == 'Not subscribed') {
                    alert(vm.reasons[data.reason], window.location.href = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=209556231&idx=1&sn=2a60e7f060180c9ecd0792f89694defb#rd');
                } else if (data.reason == 'Not logged') {
                    alert(vm.reasons[data.reason], window.location.href = '/users/login.html?referer=' + document.URL);
                } else {
                    alert(vm.reasons[data.reason]);
                }
            }).error(function (data) {
            });
        }

        function getVoteText(candidateId) {
            if (vm.isVoted(candidateId)) {
                return '已投票';
            }
            return ' 投票 ';
        }

        function isVoted(candidateId) {
            return vm.voted.indexOf(candidateId) >= 0;
        }
    }
})(window, window.angular, window.wx);