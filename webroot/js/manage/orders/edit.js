$(document).ready(function () {
    var $form = $('.order-form');
    var $saveBtn = $('.save-btn');
    var $orderStatus = $('.order-status');
    var $orderStatusSelect = $('select', $orderStatus);
    var $shipMark = $('.ship-mark');
    var $shipMarkSelect = $('select', $shipMark);
    var $consigneeMobilephone = $('.consignee-mobilephone');
    var $consigneeMobilephoneInput = $('input', $consigneeMobilephone);

    $('select', $orderStatus).on('change', function () {
        var oldValue = $orderStatus.data('value');
        var setToPaid = oldValue == 0 && $(this).val() == 1;
        $orderStatus.toggleClass('has-error', !setToPaid);
    });

    $shipMark.on('change', function () {
        var oldValue = $shipMark.data('value');
        if (!_.isEmpty(oldValue) && $shipMark.val() == '') {
            return onChanged(false, $shipMark, "请选择物流方式");
        }

        onChanged(true, $shipMark, oldValue + '将修改为' + $shipMark.val());
    });

    $('input', $consigneeMobilephone).on('change', function () {
        $consigneeMobilephone.toggleClass('has-error', !iUtils.isMobileValid($(this).val()));
    })

    $saveBtn.on('click', function(){
        var oldValue = $orderStatus.data('value');
        var setToPaid = oldValue == 0 && $orderStatusSelect.val() == 1;
        $orderStatus.toggleClass('has-error', !setToPaid);

    })
});