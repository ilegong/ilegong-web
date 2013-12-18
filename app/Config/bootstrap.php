<?php
define('COMMON_PATH', ROOT . DS . 'lib' . DS);
include_once COMMON_PATH.'bootstrap.php';

global $page_style;
global $pages_tpl;
/*  分页样式    */
//style=1 共2991条 200页 当前第1页 [ 1 2 3 4 5 6 7 8 9 10 ... 200 ] 
//style=2 共118条 | 首页 | 上一页 | 下一页 | 尾页 | 65条/页 | 共2页  <select>第1页</select>
$page_style = 1;
$pages_tpl = array(
	'total' => '共%d条',
	'pages' => '共%d页',
	'current_page' => '当前第%d页',
	'first' => '首页',
	'last' => '尾页',
	'pagesize' => '%d条/页',
	'pre_page' => '上一页',
	'next_page' => '下一页',
	'template' => '{total} {pages} {current_page}'
);
