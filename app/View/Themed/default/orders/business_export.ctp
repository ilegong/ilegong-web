<?php
// create new empty worksheet and set default font
$this->PhpExcel->createWorksheet()
    ->setDefaultFont('宋体', 14);

// define table cells
$table = array(
    array('label' => __('订单号'),  'width' => 10,  'filter' => true),
    array('label' => __('客户姓名'), 'width' => 10, 'filter' => true),
    array('label' => __('下单时间'), 'width' => 22),
    array('label' => __('商品'), 'width' => 40, 'wrap' => true),
    array('label' => __('总价'), 'width' => 6),
    array('label' => __('状态'), 'width' => 8),
    array('label' => __('联系电话'), 'width' => 15),
    array('label' => __('收货地址'), 'width' => 40, 'wrap' => true)
);

// add heading with different font and bold text
$this->PhpExcel->addTableHeader($table, array('name' => '宋体', 'bold' => true, 'size' => '16'));

$add_header_flag = false;
$fields = array('id', 'consignee_name', 'created', 'goods', 'total_price', 'status', 'consignee_mobilephone', 'consignee_address');
$header = array('订单号', '客户姓名', '下单时间', '商品', '总价', '状态', '联系电话', '收货地址');
$order_status = array('待确认', '已支付', '已发货', '已收货', '已退款', '', '', '', '', '已完成', '已做废', '已确认', '已投诉');
$page = 1;
$pagesize = 500;
do {
    $rows = count($orders);
    foreach ($orders as $item) {
        $row = array();
        foreach ($fields as $fieldName) {
            if ($fieldName == 'goods') {
                $orderId = $item['Order']['id'];
                $goods = $order_carts[$orderId];
                $value = '';
                if (is_array($goods)) {
                    foreach ($goods as $k => $good) {
                        $value .= $good['Cart']['name'] . '*' . $good['Cart']['num'];
                        if ($k != count($goods)-1) { $value.= '
';
                        }
                    }
                }

            } else {
                $value = $item['Order'][$fieldName];
                if ($fieldName == 'status') {
                    $value = $order_status[$value];
                }
            }

            $row[] = $value;
        }
        $this->PhpExcel->addTableRow($row);
    }
    ++$page;
} while ($rows == $pagesize);
// close table and output
$this->PhpExcel->addTableFooter()
    ->output('Pyshuo.com-export-'.$this->Time->format(time(), '%Y%m%d%H%M').'.xlsx');