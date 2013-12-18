<?php

/*后台顶层菜单*/
$GLOBALS['hookvars']['navmenu'][] = array('Menu'=>Array('name' => '工作流','slug' => 'oa'));
//Configure::write('Hook.helpers.Taobaoke','Taobao.TaobaokeHook');
// Configure::write('Hook.components.Oa','Taobao.OaHook');
/*后台左侧二级菜单，第二位数组的索引与顶层菜单的slug对应 */
$GLOBALS['hookvars']['submenu']['oa'] = Array(Array(
		'Menu' =>Array('name' => '工作流设置','link' => '#'),
		'children' => Array(
				Array('Menu' => Array('name' => '流程管理','link' => '/admin/oa/flows/list')),
				Array('Menu' => Array('name' => '流程步骤管理','link' => '/admin/oa/flowsteps/list')),
		)
));
