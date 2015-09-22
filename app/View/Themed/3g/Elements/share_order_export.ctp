<?php

$exportName = empty($exportName) ? 'export' : $exportName;

// create new empty worksheet and set default font
$this->PhpExcel->createWorksheet()
    ->setDefaultFont('宋体', 14);
// define table cells
$table = array(
    array('label' => __('订单号'), 'width' => 10, 'filter' => true),
    array('label' => __('客户姓名'), 'width' => 10, 'filter' => true),
    array('label' => __('下单时间'), 'width' => 22),
    array('label' => __('付款时间'), 'width' => 22),
    array('label' => __('商品'), 'width' => 30, 'wrap' => true),
    array('label' => __('件数'), 'width' => 6),
    array('label' => __('总价(含运费)'), 'width' => 6),
    array('label' => __('运费'), 'width' => 6),
    array('label' => __('使用红包'), 'width' => 6),
    array('label' => __('状态'), 'width' => 8),
    array('label' => __('联系电话'), 'width' => 12),
    array('label' => __('收货地址'), 'width' => 40, 'wrap' => true),
    array('label' => __('快递方式'), 'width' => 20),
);

// add heading with different font and bold text
$this->PhpExcel->addTableHeader($table, array('name' => '宋体', 'bold' => true, 'size' => '16'));

$add_header_flag = false;
$fields = array('id', 'consignee_name', 'created', 'pay_time', 'goods', 'num', 'total_all_price', 'ship_fee', 'coupon_total', 'status', 'consignee_mobilephone', 'consignee_address', 'ship_mark');
$header = array('订单号', '客户姓名', '下单时间', '支付时间', '商品', '件数', '总价', '运费', '使用红包', '状态', '联系电话', '收货地址', '快递方式');
$order_status = array('待确认', '已支付', '已发货', '已收货', '已退款', '', '', '', '', '已完成', '已做废', '已确认', '已投诉', '', '退款中');
$ship_mark = array('kuai_di' => '快递', 'self_zi_ti' => '自提', 'pys_zi_ti' => '好邻居');
$rows = count($orders);
$order_ship_tags = array(SHARE_SHIP_SELF_ZITI_TAG, SHARE_SHIP_KUAIDI_TAG, SHARE_SHIP_PYS_ZITI_TAG);
foreach($order_ship_tags as $tag){
    if (count($orders[$tag]) > 0){
        $current_orders=$orders[$tag];
        $current_tag = $tag;
        foreach ($current_orders as $item) {
            if($item['status']!=1){
                continue;
            }
            foreach ($order_cart_map[$item['id']] as $index => $cart) {
                $row = array();
                foreach ($fields as $fieldName) {
                    if ($fieldName == 'goods') {
                        $value = $cart['name'];
                    } else if ($fieldName == 'num') {
                        $value = $cart['num'];
                    } else {
                        if ($index == 0) {
                            if ($fieldName == 'status') {
                                $value = $order_status[$item['status']];
                            } else if ($fieldName == 'coupon_total') {
                                $value = $item['coupon_total'] / 100.0;
                            } else if ($fieldName == 'ship_mark') {
                                $value = $ship_mark[$item['ship_mark']];
                            } else {
                                $value = $item[$fieldName];
                            }
                        } else {
                            $value = '';
                        }
                    }
                    $row[] = $value;
                }
                $this->PhpExcel->addTableRow($row);
            }
        }
    }
}
// close table and output
$this->PhpExcel->addTableFooter()
    ->output('Pyshuo.com-' . $exportName . '-' . $this->Time->format(time(), '%Y%m%d%H%M') . '.xls', 'Excel5');