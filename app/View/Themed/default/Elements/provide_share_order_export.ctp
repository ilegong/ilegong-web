<?php

$exportName = empty($exportName) ? 'export' : $exportName;

// create new empty worksheet and set default font
$this->PhpExcel->createWorksheet()
    ->setDefaultFont('宋体', 14);
// define table cells
$table = array(
    array('label' => __('订单号')),
    array('label' => __('客户姓名')),
    array('label' => __('付款时间')),
    array('label' => __('商品信息')),
    array('label' => __('总价(含运费)')),
    array('label' => __('运费')),
    array('label' => __('使用红包')),
    array('label' => __('状态')),
    array('label' => __('联系电话')),
    array('label' => __('收货地址')),
);

// add heading with different font and bold text
$this->PhpExcel->addTableHeader($table, array('name' => '宋体', 'bold' => true, 'size' => '16'));

$add_header_flag = false;
$fields = array('id', 'consignee_name', 'pay_time', 'goods', 'total_all_price', 'ship_fee', 'coupon_total', 'status', 'consignee_mobilephone', 'consignee_address');
$header = array('订单号', '客户姓名', '支付时间', '商品信息',  '总价', '运费', '使用红包', '状态', '联系电话', '收货地址');
$order_status = array('待确认', '已支付', '已发货', '已收货', '已退款', '', '', '', '', '已完成', '已做废', '已确认', '已投诉', '', '退款中');
$ship_mark = array('kuai_di' => '快递', 'self_ziti' => '自提', 'pys_ziti' => '好邻居', 'pin_tuan' => '拼团');
$rows = count($orders);

foreach ($orders as $item) {
    $row = array();
    foreach ($fields as $fieldName) {
        if ($fieldName == 'goods') {
            $value = implode (',' , $item['carts']);
        } else {
            if ($fieldName == 'status') {
                $value = $order_status[$item['status']];
            } else if ($fieldName == 'coupon_total') {
                $value = $item['coupon_total'] / 100.0;
            } else if ($fieldName == 'ship_mark') {
                $value = $ship_mark[$item['ship_mark']];
            }else if ($fieldName == 'nickname'){
                $value = $users[$item['creator']]['nickname'];
            }else if($fieldName == 'ship_fee'){
                $value = $item['ship_fee'] / 100.0;
            }else if($fieldName == 'consignee_mobilephone'){
                $value = '手机:'.$item['consignee_mobilephone'];
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