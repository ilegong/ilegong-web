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
    array('label' => __('商品信息'), 'width' => 30, 'wrap' => true),
    array('label' => __('总价(含运费)'), 'width' => 6),
    array('label' => __('运费'), 'width' => 6),
    array('label' => __('使用红包'), 'width' => 6),
    array('label' => __('状态'), 'width' => 8),
    array('label' => __('联系电话'), 'width' => 12),
    array('label' => __('收货地址'), 'width' => 40, 'wrap' => true),
    array('label' => __('快递方式'), 'width' => 20, 'wrap' => true),
    array('label' => __('微信昵称'), 'width' => 10, 'wrap' => true),
    array('label' => __('备注'), 'width' => 30, 'wrap' => true),
    array('label' => __('快递公司'), 'width' => 30, 'wrap' => true),
    array('label' => __('快递单号'), 'width' => 20, 'wrap' => true),
);

// add heading with different font and bold text
$this->PhpExcel->addTableHeader($table, array('name' => '宋体', 'bold' => true, 'size' => '16'));

$add_header_flag = false;
$fields = array('id', 'consignee_name', 'created', 'pay_time', 'goods', 'total_all_price', 'ship_fee', 'coupon_total', 'status', 'consignee_mobilephone', 'consignee_address', 'ship_mark', 'nickname', 'business_remark', 'ship_type_name', 'ship_code');
$header = array('订单号', '客户姓名', '下单时间', '支付时间', '商品信息',  '总价', '运费', '使用红包', '状态', '联系电话', '收货地址', '快递方式', '微信昵称', '备注');
$order_status = array('待确认', '已支付', '已发货', '已收货', '已退款', '', '', '', '', '已完成', '已做废', '已确认', '已投诉', '', '退款中');
$ship_mark = array('kuai_di' => '快递', 'self_ziti' => '自提', 'pys_ziti' => '好邻居', 'pin_tuan' => '拼团');
$rows = count($orders);
foreach ($orders as $item) {
        $row = array();
        foreach ($fields as $fieldName) {
            if ($fieldName == 'goods') {
                $value = get_share_order_cart_display_name($order_cart_map[$item['id']]);
            } else {
                if ($fieldName == 'status') {
                    $value = $order_status[$item['status']];
                } else if ($fieldName == 'coupon_total') {
                    $value = $item['coupon_total'] / 100.0;
                } else if ($fieldName == 'ship_mark') {
                    $value = $ship_mark[$item['ship_mark']];
                }else if ($fieldName == 'nickname'){
                    $value = $users[$item['creator']]['nickname'];
                }else if(){
                    $value = $item['ship_fee'] / 100.0;
                }else {
                    $value = $item[$fieldName];
                }
            }
            $row[] = $value;
        }
        $this->PhpExcel->addTableRow($row);
}

// close table and output
$this->PhpExcel->addTableFooter()
    ->output($exportName . '-' . $this->Time->format(time(), '%Y%m%d%H%M') . '.xls', 'Excel5');