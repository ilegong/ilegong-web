<?php
class MultiSiteHookComponent extends Component {

/**
 * Called after the Controller::beforeFilter() and before the controller action
 */
	public function startup(Controller $controller) {
		if(intval($_GET['siteid'])>0){
			$GLOBALS['site_cate_id'] = intval($_GET['siteid']);
		}
		else{			
			$category_model = loadModelObject('Category');
			$siteinfo = $category_model->find('first',array(
				'conditions'=> array('domain'=>$_SERVER['HTTP_HOST'],'model'=>'website'),
			));
		}
		if(!isset($siteinfo) || empty($siteinfo)){
			$siteinfo = $category_model->find('first',array(
					'conditions'=> array('id'=>$GLOBALS['site_cate_id']),
			));
		}
		if(!empty($siteinfo)){
			$GLOBALS['site_cate_id'] = $siteinfo['Category']['id'];
			$GLOBALS['site_info'] = $siteinfo['Category'];
			Configure::write('Site.logo',$siteinfo['Category']['logo']);
			Configure::write('Site.logo_url',$siteinfo['Category']['link']);
			Configure::write('Site.title',$siteinfo['Category']['name']);
			Configure::write('Site.seokeywords',$siteinfo['Category']['seokeywords']);
			Configure::write('Site.seodescription',$siteinfo['Category']['seodescription']);
		}
	}
	
	public function beforeRender(Controller $controller) {
		$controller->set('site_cate_id',$GLOBALS['site_cate_id']);
		if($controller->toString()=='CategoriesController'){
			if(isset($controller->viewVars['models']) && is_array($controller->viewVars['models'])){
				$controller->viewVars['models']['website'] = __('Website');
			}
		}
	}
}

?>