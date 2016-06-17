<?php
if($isMobile){
echo $this->element('simple_share_order_export', array('exportName' => '订单导出'));
}else{
echo $this->element('share_order_export', array('exportName' => '订单导出'));
}