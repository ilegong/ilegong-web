<?php
App::uses('Xml', 'Utility');
App::uses('CrawlUtility', 'Utility');
class CrawlReleaseSitesController extends AppController {

	var $name = 'crawl_release_sites';

	/**
	 * 根据站点id，模块和模块分类一起返回。
	 * @param $siteid
	 * @return unknown_type
	 */
	public function admin_getSiteCate($siteid){
		$this->autoRender = false;
		$siteinfo = $this->CrawlReleaseSite->find('first',array('conditions'=>array('id'=>$siteid)));
		
		if($siteinfo['CrawlReleaseSite']['site_type']=='local'){
//			$modules = Configure::read("Site");
//			$modules = Configure::read("Site.contentmodels");
//			$modules = explode(',',$modules);
			
			$this->loadModel('Modelextend');
			$modules = $this->Modelextend->find('all',array('conditions'=>array('deleted'=>0),'order'=>'name asc'));
			
			$modules_array = array();
			foreach($modules as $item){
				$modules_array[$item['Modelextend']['name']] = $item['Modelextend']['name'];
			}
			echo json_encode($modules_array);
		}
		else{
			$url = $siteinfo['CrawlReleaseSite']['model_api_url'];
			if(strpos($url,'?')!==false){
				$url .= '&sec='.$siteinfo['CrawlReleaseSite']['sec_code'];
			}
			else{
				$url .= '?sec='.$siteinfo['CrawlReleaseSite']['sec_code'];
			}
			$content = CrawlUtility::getRomoteUrlContent($url);
			
			
			$array = xml_to_array($content);
			
			$modules = array();
			if($array['modules']['item']['modulename']){
				$item = $array['modules']['item'];
				foreach($item['modulecates'] as $cate){
					$new_items[$cate['cate_id']] = $cate;
				}
				$modules[$item['modulename']] = $new_items;
			}
			else{
				foreach($array['modules']['item'] as $item){
					$new_items = array();
					$cates = array();
					foreach($item['modulecates'] as $cate){
						$new_items[$cate['cate_id']] = $cate;
						$cates[$cate['parent_id']][] = $cate['cate_id'];
					}
					$items = array();
					$this->_nesd_cates($new_items,$cates,$items,0);	
					
					$modules[$item['modulename']] = $items;
				}
			}
			
			echo json_encode($modules);
		}
	}
	
	private function _nesd_cates(&$new_items,&$cates,&$items,$cid=0){		
		if(!empty($cates[$cid])){
			$current_cates = $cates[$cid];
			foreach($current_cates as $cid){
				$leval = 0;
				$item = $new_items[$cid];
				$parent_id = $item['parent_id'];
				while($item['parent_id'] && !empty($new_items[$parent_id])){
					$leval++;
					$parent_id = $new_items[$parent_id]['parent_id'];
				}
				$item['cate_name'] = str_repeat('__',$leval).$item['cate_name'];
				$items[] = $item;
				$this->_nesd_cates($new_items,$cates,$items,$cid);				
			}
		}
	}
	
	
	/**
	 * 根据站点id，站点模块名称取回类别分类信息
	 * @param $siteid
	 * @param $modelname
	 * @return unknown_type
	 */
    function admin_loadcate($siteid='',$modelname='Category') {
    	$siteinfo = $this->CrawlReleaseSite->find('first',array('conditions'=>array('id'=>$siteid)));
		
		if($siteinfo['CrawlReleaseSite']['site_type']=='local'){	        
	        $modelClass = 'Category';	
	        $this->loadModel($modelClass);
	        $catelist = array();
	        $fieldlist_str = '';
	        //$categories = $this->{$modelname}->generatetreelist();	        
	        $categories = $this->{$modelClass}->generateTreeList(array('model' => $modelname));
	        
	        foreach ($categories as $cateid => $catename) {
	            $catelist[$cateid] = $catename;
	        }
	        echo json_encode($catelist);
	        exit;
		}
    }

}