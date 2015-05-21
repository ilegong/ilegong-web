/**
 * Created by shichaopeng on 5/20/15.
 */

$(document).ready(function () {
    var $el = $('#pys_order_countly');
    var navItems = $('.admin-menu li > a');
    var navListItems = $('.admin-menu li');
    var allWells = $('.admin-content');
    var allWellsExceptFirst = $('.admin-content:not(:first)');
    var activeTabVal = $('#active-tab-val',$el).val();
    allWellsExceptFirst.hide();
    navItems.click(function (e) {
        e.preventDefault();
        navListItems.removeClass('active');
        $(this).closest('li').addClass('active');
        allWells.hide();
        var target = $(this).attr('data-target-id');
        $('#' + target).show();
    });
    $('a[data-target-id="'+activeTabVal+'"]').trigger('click');
    var $gen_last_week_data = $('#gen_last_week_data',$el);
    var $custom_gen_data = $('#custom_gen_data',$el);
    $gen_last_week_data.on('click',function(){
        $.post('/manage/admin/countly/gen_data',function(data){
            console.log(data);
            if(data['success']){
                alert('数据生成成功');
            }
        },'json');
    });

    $custom_gen_data.on('click',function(){
        var startDate = $('#gen_start_date').val();
        var endDate = $('#gen_end_date').val();
        $.post('/manage/admin/countly/gen_data',{"start_date":startDate,"end_date":endDate},function(data){
            console.log(data);
            if(data['success']){
                alert('数据生成成功');
            }
        },'json');

    });

});