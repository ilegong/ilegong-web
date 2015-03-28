/**
 * Created by algdev on 15/3/28.
 */
$(function(){
    var tuanName = $('#tuan_name');
    var leaderName = $('#leader_name');
    var leaderWX = $('#leader_weixin');
    var address =  $('#address');
    var tuanAddress = $('#tuan_addr');
    var longtitud = $('#location_long');
    var latitud  = $('#location_lat');

    $(".tuanTeam-form").submit(function(e){
        var invalidTuanName = tuanName.val()=='';
        tuanName.parents('.form-group').toggleClass('has-error',invalidTuanName);
        var invalidLeaderName = leaderName.val()=='';
        leaderName.parents('.form-group').toggleClass('has-error',invalidLeaderName);
        var invalidWX = leaderWX.val()=='';
        leaderWX.parents('.form-group').toggleClass('has-error',invalidWX);
        var invalidAddress = address.val()=='';
        address.parents('.form-group').toggleClass('has-error',invalidAddress);
        var invalidTuanAddress = tuanAddress.val()=='';
        tuanAddress.parents('.form-group').toggleClass('has-error',invalidTuanAddress);
        var invalidLongtitud = isNaN(Number(longtitud.val()));alert(invalidLongtitud);
        longtitud.parents('.form-group').toggleClass('has-error',invalidLongtitud);
        var invalidLatitud = isNaN(Number(latitud.val()));
        latitud.parents('.form-group').toggleClass('has-error',invalidLatitud);

        if(invalidTuanName || invalidLeaderName || invalidWX || invalidAddress || invalidTuanAddress || invalidLongtitud || invalidLatitud){
            return false;
        }
        return true;
    });
});