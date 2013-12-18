<?php


class MiaoHookComponent extends Component {
	
	public $components = array('Auth');
	
	/*******重要： action的li之间要一个紧连着一个 ，不要有换行或空格，否则会由于空格符引起显示错位。*************************/
	function gridDataAction($controller,$model,$data){
		
		if($model=='Crawl'){
			return  '<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/crawls/crawl/'.$data['id']).'" data-dialogtype="iframe" title="'.__('Crawl').'" ><span >'.__('Crawl').'</span></a></li>'.
			'<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/crawls/crawl/'.$data['id'].'?test=1').'" data-dialogtype="iframe" title="'.__('Test').'" ><span >'.__('Test').'</span></a></li>';
		}
		elseif($model=='Category'){
			$toolbars = '';
			if($data['id'] == Configure::read('Site.index_page')){
				$toolbars .= '<li class="grid-row-action"><a href="#" class="btn btn-small btn-success" onclick="return ajaxAction(\''.Router::url('/admin/settings/ajaxesave').'\',{\'setting[Site][index_page]\':'.$data['id'].'},null,\'set_index_page\',this);"  data-url="#" title="'.__('Is home page').'" ><i class="glyphicon glyphicon-ok"></i></a></li>';
			}
			else{
				$toolbars .= '<li class="grid-row-action"><a href="#" class="btn btn-small" onclick="return ajaxAction(\''.Router::url('/admin/settings/ajaxesave').'\',{\'setting[Site][index_page]\':'.$data['id'].'},null,\'set_index_page\',this);"  data-url="#" title="'.__('Set as home page').'" ><i class="glyphicon glyphicon-home"></i></a></li>';
			}
			$toolbars .=  '<li class="ui-state-default grid-row-action" id="categories_up_'.$data['id'].'" onclick="ajaxAction(\''.Router::url('/admin/categories/treesort').'\',{id:'.$data['id'].',\'type\':\'up\'},null,function(){$(\'#categories_up_'.$data['id'].'\').parent(\'tr\');})" title="'.__('Tree Sort Up').'"><span class="glyphicon glyphicon-arrow-up"></span></li>';
			$toolbars .=  '<li class="ui-state-default grid-row-action" onclick="ajaxAction(\''.Router::url('/admin/categories/treesort').'\',{id:'.$data['id'].',\'type\':\'down\'})"  title="'.__('Tree Sort Down').'"><span class="glyphicon glyphicon-arrow-down"></span></li>';
			return $toolbars;
		}
		elseif($model=='CrawlTitleList'){
			return  '<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/crawl_title_lists/crawlSingleContent/'.$data['id']).'" title="'.__('Crawl article content.').'"><span class="glyphicon glyphicon-circle-arrow-down"></span></a></li>'.
					'<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/crawl_title_lists/publishSingle/'.$data['id']).'" title="'.__('Publish article.').'"><span class="glyphicon glyphicon-ok-circle"></span></a></li>';
		}
		elseif($model=='Menu'){
// 			$toolbars =  '<li class="ui-state-default grid-row-action" onclick="ajaxAction(\''.Router::url('/admin/menus/treesort').'\',{id:'.$data['id'].',\'type\':\'up\'})" title="'.__('Tree Sort Up').'"><span class="ui-icon ui-icon-arrowthick-1-n"></span></li>';
// 			$toolbars .=  '<li class="ui-state-default grid-row-action" onclick="ajaxAction(\''.Router::url('/admin/menus/treesort').'\',{id:'.$data['id'].',\'type\':\'down\'})"  title="'.__('Tree Sort Down').'"><span class="ui-icon ui-icon-arrowthick-1-s"></span></li>';
// 			$toolbars .=  '<li class="ui-state-default grid-row-action" data-action_url="'.Router::url('/admin/menus/add/parent_id:'.$data['id']).'" title="'.__('Add Sub Menu').'"><span class="ui-icon ui-icon-document"></span></li>';
// 			return $toolbars;
		}
		elseif($model=='Modelcate'){
			return  '<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/modelextends/add/'.$data['model'].'Split'.$data['id']).'" title="'.__('Category Extend Model,one-to-one').'"><span class="glyphicon glyphicon-transfer"></span></a></li>'.
					'<li class="ui-state-default" title="'.__('Model datas').'"><a href="'.Router::url('/admin/'.$data['model'].'/list?cate_id='.$data['id']).'"><span class="glyphicon glyphicon-list"></span></a></li>';
		}
		elseif($model=='Staff'){
			return  '<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/aros_acos/set/'.$model.'/'.$data['id']).'" title="'.__('Permission').'"><span class="glyphicon glyphicon-lock"></span></a></li>';
		}
	}
	
	public function beforeRender(Controller $controller) {
		if($controller->params['controller']!='menus' && $this->Auth->user('id')){
			/**
			 * 不为菜单页时且为登录用户，获取菜单项。
			 */
			$cachekey = 'admin_site_left_menus';
			$left_menus = Cache::read($cachekey);
			if(empty($left_menus)||$left_menus=='null'){
				// requestAction可能获取到的内容不正确，页面显示为null，不能用===false来判断
				$left_menus =  $this->requestAction('/admin/menus/menu');
				Cache::write($cachekey,$left_menus);
			}
			$controller->set('left_menus', $left_menus);
		}
		/* 可选语言 */
		$cachekey = 'admin_select_lan';
		$selectlans = Cache::read($cachekey);
		if($selectlans===false){
			$controller->loadModel('Language');
			$lans = $controller->Language->find('all');
			$selectlans = array();
			foreach ($lans as $lang) {
				$selectlans[$lang['Language']['alias']] = $lang['Language']['native'];
			}
			Cache::write($cachekey,$selectlans);
		}
		$controller->set('selectlans', $selectlans);
		
		/* 内容模型列表 */
		$cachekey = 'admin_content_models';
		$contentmodels = Cache::read($cachekey);
		if($contentmodels===false){
			$content_cate_id=1;
			$controller->loadModel('Modelextend');
			$contentmodels = $controller->Modelextend->find('all',array('conditions'=>array('cate_id'=>$content_cate_id)));
			Cache::write($cachekey,$contentmodels);
		}
		
		$content_model_options = array();
		foreach ($contentmodels as $item) {
            $content_model_options[$item['Modelextend']['name']] = $item['Modelextend']['cname'];
        }
        $controller->set('content_model_options', $content_model_options);
        return true;
	}
}