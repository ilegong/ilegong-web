/**
 * Created by algdev on 15/7/1.
 */
$(function(){
    var voteBaby = $('.vote_baby');
    var reasons = {
        'Not logged':'请先登录哦',
        'Not subscribed':'请先关注[朋友说]公众号进行投票哦',
        'more than five':'每个微信号每天投票不能超过5次哦',
        'already vote':'您已经投过票啦',
        'save wrong':'投票失败，请重试哦'
    }
    $('.active').children('a').addClass('cur');
    voteBaby.on('click',function(){
        var candidateId = voteBaby.data('candidate_id');
        var eventId = voteBaby.data('event_id');
        $.post('/vote/vote/' + candidateId + '/' + eventId,function(data){
           if(data.success){
               voteBaby.text('已投票').removeClass('vote_baby').addClass('cur');
               utils.alert('投票成功');
               location.reload();
           }else{
               utils.alert(reasons[data.reason]);
           }
        },'json');

    });

});