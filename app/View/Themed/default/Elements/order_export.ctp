<?php

$exportName = empty($exportName)? 'export' : $exportName;

// create new empty worksheet and set default font
$this->PhpExcel->createWorksheet()
    ->setDefaultFont('宋体', 14);
// define table cells
$table = array(
    array('label' => __('订单号'),  'width' => 10,  'filter' => true),
    array('label' => __('团购'),  'width' => 10,  'filter' => true),
    array('label' => __('客户姓名'), 'width' => 10, 'filter' => true),
    array('label' => __('下单时间'), 'width' => 22),
    array('label' => __('付款时间'), 'width' => 22),
    array('label' => __('商品'), 'width' => 40, 'wrap' => true),
    array('label' => __('件数'), 'width' => 6),
    array('label' => __('规格'), 'width' => 8),
    array('label' => __('排期'), 'width' => 8),
    array('label' => __('总价(含运费)'), 'width' => 6),
    array('label' => __('运费'), 'width' => 6),
    array('label' => __('状态'), 'width' => 8),
    array('label' => __('联系电话'), 'width' => 12),
    array('label' => __('收货地址'), 'width' => 40, 'wrap' => true),
    array('label' => __('订单备注'), 'width' => 30),
    array('label' => __('商家备注'), 'width' => 30),
);

// add heading with different font and bold text
$this->PhpExcel->addTableHeader($table, array('name' => '宋体', 'bold' => true, 'size' => '16'));

$add_header_flag = false;
$fields = array('id', 'type', 'consignee_name', 'created','pay_time', 'goods','num', 'spec', 'consign_date','total_all_price', 'ship_fee', 'status', 'consignee_mobilephone', 'consignee_address', 'remark', 'business_remark');
$header = array('订单号', '团购', '客户姓名', '下单时间', '支付时间', '商品', '件数', '规格', '排期', '总价', '运费', '状态', '联系电话', '收货地址', '订单备注','商家备注');
$order_status = array('待确认', '已支付', '已发货', '已收货', '已退款', '', '', '', '', '已完成', '已做废', '已确认', '已投诉');
$page = 1;
$pagesize = 500;
/**
 * @param $item
 * @param $order_carts
 * @return array $orderAbs, $count
 */
function countGoodsAndNums($item, $order_carts) {
    $orderId = $item['Order']['id'];
    $goods = $order_carts[$orderId];
    $orderAbs = '';
    $count = 0;
    if (is_array($goods)) {
        foreach ($goods as $k => $good) {
            $orderAbs .= $good['Cart']['name'] . '*' . $good['Cart']['num'];
            if ($k != count($goods) - 1) {
                $orderAbs .= '
';
            }
            $count += $good['Cart']['num'];
        }
    }
    return array($orderAbs, $count);
}

do {
    $rows = count($orders);
    foreach ($orders as $item) {
//        list($orderAbs, $itemsCount) = countGoodsAndNums($item, $order_carts);
        foreach($order_carts[$item['Order']['id']] as $index => $cart){
            $row = array();
            foreach ($fields as $fieldName) {
                if ($fieldName == 'goods') {
                    $value =  $cart['Cart']['name'];
                } else if($fieldName == 'num') {
                    $value =  $cart['Cart']['num'];
                } else if($fieldName == 'type') {
                    $value = $item['Order']['type'] == 5 ? '是' : '否';
                }else if($fieldName == 'spec') {
                    $value = $spec_groups[$cart['Cart']['specId']];
                }else if($fieldName == 'consign_date'){
                    if ($cart['Cart']['type'] == 5){
                        $date = $tuan_consign_times[$item['Order']['member_id']];
                        $value = empty($date)?'': date('m-d', strtotime($date));
                    } else if (!empty($cart['Cart']['consignment_date'])){
                        $date = $consign_dates[$cart['Cart']['consignment_date']];
                        $value = empty($date)?'': date('m-d', strtotime($date));
                    }
                } else if($fieldName == 'status') {
                    $value = $order_status[$item['Order'][$fieldName]];
                } else {
                    if($index == 0){
                        $value = $item['Order'][$fieldName];
                        if ($fieldName == 'consignee_address'){
                            $value = $item['Order']['consignee_area'].$item['Order']['consignee_address'];
                        }
                    }else{
                        $value = '';
                    }
                }
                $row[] = $value;
            }
            $this->PhpExcel->addTableRow($row);
        }
    }
    ++$page;
} while ($rows == $pagesize);
// close table and output
$this->PhpExcel->addTableFooter()
    ->output('Pyshuo.com-'.$exportName.'-'.$this->Time->format(time(), '%Y%m%d%H%M').'.xls', 'Excel5');