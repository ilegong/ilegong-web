<?php
/**
 * 后台钩子
 * @author arlonzou
 * @2013-1-8下午7:49:09
 */

class MiaoHookHelper extends AppHelper {
	
	public $helpers = array('Html','Session');
	
	/**
	 * section NavMemu方法中调用的钩子，可通过此钩子修改后台顶部菜单内容
	 * @param unknown_type $channels
	 * @param unknown_type $modelClass
	 * @param unknown_type $options
	 * @param unknown_type $parent_id
	 * @param unknown_type $with_child
	 */
	function NavMemu(&$channels,$modelClass,&$options, $parent_id, $with_child){
		if(is_array($GLOBALS['hookvars']['navmenu'])){
			$channels = array_merge($channels,$GLOBALS['hookvars']['navmenu']);
		}
	}
	
	/**
	 * 后台左侧菜单钩子
	 * @param array $submenu
	 */
	function submenu(&$submenu){
		$roles = $this->Session->read('Auth.Staff.role_id');
		$user_id = $this->Session->read( 'Auth.Staff.id');
		if(!is_array($submenu['children'])){
			$submenu['children'] = array();
		}
		
		if(in_array(1,$roles) && $submenu['Menu']['slug']=='content'){ /* 内容类型的模块追加菜单 */
			$modelextent = loadModelObject('Modelextend');
			$content_menus = array('Menu' =>Array('name' => '内容管理','link' => '#'),'children'=>array());
			$contentmodels = $modelextent->getContentModel('all',1);
			foreach($contentmodels as $item){
				$content_menus['children'][] = array('Menu' =>Array(
						'name' => $item['Modelextend']['cname'].__('Manage'),
						'link' => array('controller'=>Inflector::tableize($item['Modelextend']['name']),'action'=>'list','admin'=>true)
				));
			}
			// 将内存的操作权限插入在第一位
			array_unshift($submenu['children'],$content_menus);
		}
		else if($submenu['Menu']['slug']=='privilege'){ // 我的权限，非系统管理员用户被授权的操作
			$modelextent = loadModelObject('Aco');
			$node = $modelextent->find('first',array('conditions'=>array('alias'=>'controllers')));
			$acos = $modelextent->find('threaded',array(
				// 'fields'=>array('id','alias','name'),		
				'conditions'=>array(
					'lft >' => $node['Aco']['lft'],
					'rght <' => $node['Aco']['rght'],
				),
			));
			// print_r($acos);exit;
			$this->_listAcos($acos,$parents,$children);

			// 取出用户所有具权限的aco_id,与每一项的所有children做交集，如果交集不为空，则显示此菜单项。
			// 否则不显示
			$user_aco_ids = Cache::read( $user_id  . "_admin_allow_aco_ids");
// 			print_r($acos);
// 			print_r($children);
			$this->_filterAcos($acos,$parents,$children,$user_aco_ids);
			// print_r($user_aco_ids);
			// print_r($acos);
			// $acos = array_shift($acos);
			// print_r($acos);
			$menus = array();
			$this->_acosToMenus($acos,$menus);
			// echo "==========";exit;
			$submenu['children']=$menus;
// 			print_r($submenu);
		}
		
		$slug = $submenu['Menu']['slug'];
		if($submenu['Menu']['slug'] && !empty($GLOBALS['hookvars']['submenu'][$slug]) && is_array($GLOBALS['hookvars']['submenu'][$slug])){
			$submenu['children'] = array_merge($submenu['children'],$GLOBALS['hookvars']['submenu'][$slug]);
		}
	}
	
	/**
	 *	将aco数组转换成menu数组
	 */
	private function _acosToMenus($acos,&$menu,$alias=''){
		foreach($acos as $k => $val){
			if(strpos($val['Aco']['alias'],'admin_')!==false){
				$item_alias = str_replace('admin_','',$val['Aco']['alias']);
				// 必需操作具体数据的操作直接跳过，显示在list列表中
				if(in_array($item_alias,array('edit','publish','delete','trash','restore','view','batchEdit'))){
					continue;
				}
				// Controller名称转换成小写字母。
				$alias = Inflector::underscore($alias);
			}
			else{
				$alias='';
			}
			$item = array(
					'Menu' =>Array(
							'name' => $val['Aco']['name'],
							'slug' => $val['Aco']['alias'],
							'id' => $val['Aco']['alias'],
							//'link' => $alias ? Router::url(array('controller'=>$alias,'action'=>$item_alias)) : '#'
							'link' => $alias ? '/admin/'.$alias.'/'.$item_alias : '#'
					),
					'children'=>array()
			);
			if(!empty($val['children'])){
				$this->_acosToMenus($val['children'],$item['children'],$val['Aco']['alias']);
			}
			$menu[] = $item;
		}
	}
	
	/**
	 * 取出用户所有具权限的aco_id,与每一项的所有children做交集得出是否具子操作的权限，如果具有aco_id的操作权限或其某子操作权限，则显示此菜单项。
	 * @param array $threaded_acos
	 * @param array $parents
	 * @param array $children
	 * @param array $user_aco_ids
	 */
	private function _filterAcos(&$threaded_acos,$parents,$children,$user_aco_ids){
		foreach ($threaded_acos as $key => &$value) {
			$aco_id = $value['Aco']['id'];
			$intersect = array();
			if(is_array($user_aco_ids) && is_array($children[$aco_id]) && !empty($children[$aco_id])){
				$intersect = array_intersect($user_aco_ids,$children[$aco_id]);
			}
			
			if(is_array($user_aco_ids) && in_array($aco_id,$user_aco_ids) || !empty($intersect)){
				if(!empty($value['children'])){
					$this->_filterAcos($value['children'],$parents,$children,$user_aco_ids);
				}
			}
			else{
				unset($threaded_acos[$key]); //没有权限的项，移除
			}
		}
	}
	
	/**
	 * 建立所有id->parent_id, id->children(array)的关系
	 * 方便根据id取父级id，根据id取所有的子类别id
	 * @param array $threaded_acos  find('threaded') 查询得出的所有aco
	 * @param array $parents 根据id取父级id
	 * @param array $children 根据id取所有的子类别id
	 */
	private function _listAcos($threaded_acos,&$parents,&$children){
		foreach($threaded_acos as $item){
			$parent_id = $item['Aco']['parent_id'];
			$parents[$item['Aco']['id']] = $parent_id;
			// 当前的aco_id 加入到所有的父类的children数组中
			while($parent_id>0){
				$children[$parent_id][] = $item['Aco']['id'];
				$parent_id = $parents[$parent_id];
			}
			if(!empty($item['children'])){
				$this->_listAcos($item['children'],$parents,$children);
			}
		}
	}
	
	/**
	 * 模块数据列表页中，工具栏钩子，可增加工具栏操作按钮
	 * @param unknown_type $target_model
	 * @param unknown_type $pageID
	 * @return string
	 */
	function listToolbar($param){
		if($param['model']=='CrawlTitleList'){
			return  '<li>'.$this->Html->link(__('Batch Edit'), '#',array('onclick'=>'return batchAction_'.$param['pageid'].'(\'batchEdit\',{\'fields\':\'allow_crawl\'});','escape'=>false)).'</li>'
				.'<li><a target="_blank" href="'.Router::url(array('controller' => 'crawl_title_lists','action'=>'publishlist')).'">'.__('按分类发布.').'</a></li>';
		}
		elseif($param['model']=='I18nfield'){
			return  '<li class="NewDocument" id="BulkNew">'.$this->Html->link(__('Bulk New ').$param['cname'].__(' Field'), array('controller' => 'i18nfields','action'=>'bulkadd','?'=>$this->request->query),array(''=>'','data-title'=>__('Bulk New '),'escape'=>false)).'</li>'
				.'<li class="SortDocument">'.$this->Html->link($param['cname'].__(' Field Sort'), array('controller' => 'i18nfields','action'=>'sortfield','?'=>$this->request->query),array('title'=>$param['cname'].__(' Field Sort',true),'escape'=>false)).'</li>';
		}
		elseif($param['model']=='I18nfield'){
			return  '<li class="NewDocument" id="BulkNew">'.$this->Html->link(__('Bulk New ').$param['cname'].__(' Field'), array('controller' => 'i18nfields','action'=>'bulkadd','?'=>$this->request->query),array(''=>'','data-title'=>__('Bulk New '),'escape'=>false)).'</li>'
			.'<li class="SortDocument">'.$this->Html->link($param['cname'].__(' Field Sort'), array('controller' => 'i18nfields','action'=>'sortfield','?'=>$this->request->query),array('title'=>$param['cname'].__(' Field Sort',true),'escape'=>false)).'</li>';
		}
		
	}
}