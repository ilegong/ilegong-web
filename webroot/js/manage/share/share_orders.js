$(document).ready(function () {
    var $exportBtn = $('button.export-excel');
    var mainContent = $('#mainContent');
    mainContent.height(250);
    String.prototype.Trim = function () {
        return this.replace(/(^\s*)|(\s*$)/g, "");
    };
    $exportBtn.on('click', function (e) {
        e.preventDefault();
        var tableIds = [];
        var tableNames = [];
        var ignoreRows = [0, 2, 3, 4, 5, 6, 7, 8, 9, 14, 18, 19, 20, 21, 22];
        $('table.orders').each(function (index, item) {
            tableIds.push($(item).attr('id'));
            tableNames.push($(item).data('table-name'));
        });
        tablesToExcel(tableIds, tableNames, 'order-export.xls', 'Excel', ignoreRows);
    });
    $('.toggle-orders').on('click', function(e){
        var showAll = $(this).data('show-all');
        if(showAll == 1){
            $(this).data('show-all', 0);
            $(this).text('只显示统计');
            $('.orders').removeClass("hidden");
            $('h3.ship-type').addClass("new-page");
            $('.table-collect-data').css('display', '');
        }
        else{
            $(this).data('show-all', 1);
            $(this).text('显示全部');
            $('.orders').addClass("hidden");
            $('h3.ship-type').removeClass("new-page");
            $('.table-collect-data').css('display', 'block');
        }
    });
    $('.print-orders').on('click', function(e){
        window.print();
    });
});