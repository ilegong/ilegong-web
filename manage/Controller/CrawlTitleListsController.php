<?php
App::uses('Xml', 'Utility');
App::uses('CrawlUtility', 'Utility');
App::uses('File', 'Utility');
App::uses('Charset', 'Lib');
class CrawlTitleListsController extends AppController {

	var $name = 'crawl_title_lists';
	
	var $components = array('HtmlPurifier');
	
	/**
	 * 仅显示未发布的数据
	 */
	function admin_publishlist(){

		$this->loadModel('Crawl');
		$this->loadModel('Modelcate');
				
		$_GET['cate_id'] = intval($_GET['cate_id']);
		$pagesize = $this->params['named']['pagesize'] ? $this->params['named']['pagesize']:20;
		$page = $this->request->query['page'] ? $this->request->query['page']:1;
		
		
		if(!empty($_GET['crawl_id'])){
			$current_crawl = $this->Crawl->find('first',array('conditions'=>array('id'=>$_GET['crawl_id'])));
			$this->set('current_crawl',$current_crawl);
		}
		if($_GET['cate_id']){
			
			$children = $this->Modelcate->children($_GET['cate_id'],false,array('id'));
			 
			$cate_ids = array($_GET['cate_id']);
			foreach($children as $item){
				$cate_ids[] = $item['Modelcate']['id'];
			}
			
			$crawls = $this->Crawl->find('all',array('conditions'=>array('cate_id'=>$cate_ids,'deleted'=>0)));
			$this->set('crawls',$crawls);
		}
		else{
			$crawls = $this->Crawl->find('all',array('conditions'=>array('deleted'=>0)));
			$this->set('crawls',$crawls);
		}

		if($_GET['cate_id'] && empty($_GET['crawl_id'])){
			$joins = array(
						array(
	                        'table' => Inflector::tableize('Crawl'),
	                        'alias' => 'Crawl',
	                        'type' => 'inner',
	                        'conditions' => array(
	                        	'`Crawl`.`id` = `CrawlTitleList`.`crawl_id`',
								'`CrawlTitleList`.`deleted`'=>0,
	                        	'`Crawl`.`cate_id`'=> $cate_ids ),
						),
					);
			$total  = $this->CrawlTitleList->find('count',array(
                    'conditions'=>array('`CrawlTitleList`.`deleted`'=>0,),
                    'joins'=> $joins,
			));
			
			$crawl_title_lists = $this->CrawlTitleList->find('all',array(
                    'conditions'=>array('`CrawlTitleList`.`deleted`'=>0,'`CrawlTitleList`.`published`'=>0,),
                     'order' => 'CrawlTitleList.id asc',
                    'limit' => $pagesize,
                    'page' => $page,
                    'fields' => array('*'),
                    'joins'=> $joins
			));
			$this->set('crawl_title_lists',$crawl_title_lists);
		}
		else{
			$conditions = array('`CrawlTitleList`.`deleted`'=>0,'`CrawlTitleList`.`published`'=>0,);
			if(!empty($_GET['crawl_id'])){
				$conditions['CrawlTitleList.crawl_id'] = $_GET['crawl_id'];
			}
			$crawl_title_lists = $this->CrawlTitleList->find('all',array(
                    'conditions'=> $conditions,
                    'order' => 'CrawlTitleList.id asc',
                    'limit' => $pagesize,
                    'page' => $page,
                    'fields' => array('*'),
			));
			$total  = $this->CrawlTitleList->find('count',array(
                    'conditions'=>$conditions,
			));
			$this->set('crawl_title_lists',$crawl_title_lists);
		}
		
        $page_navi = getPageLinks($total, $pagesize, $this->request, $page);
        $this->set('page_navi',$page_navi);
	}
	/**
	 * 抓取一条数据的内容
	 * @param $crawl_title_id 数据id
	 */
	function admin_crawlSingleContent($crawl_title_id) {

		$this->autoRender = false;
		$replaceA = array("/", "?", "'");
		$replaceB = array('\/', '\?', "\'");
		
		$matched_data = array();
		$joinoptions = array();
		$joinoptions[] = array(
            'table' => Inflector::tableize('Crawl'),
            'alias' => 'Crawl',
            'type' => 'left',
            'conditions' => array('Crawl.`id` = CrawlTitleList.`crawl_id`'),
		);
		$rules = $this->CrawlTitleList->find('first',
		array(
                    'conditions' => array('CrawlTitleList.id' => $crawl_title_id),
                    'joins' => $joinoptions,
                    'fields' => array('CrawlTitleList.*', 'Crawl.*'),
		)
		);

		$page_url = $rules['CrawlTitleList']['remoteurl'];

		if (!empty($rules['CrawlTitleList']['coverimg'])) {
			$matched_data['coverimg'] = CrawlUtility::saveImagesByUrl($rules['CrawlTitleList']['coverimg'], $page_url, $rules['Crawl']['imgprefix']);
		}
		//    	 	$page_url = 'http://www.cnbeta.com/articles/122122.htm';
		$content = CrawlUtility::getRomoteUrlContent($page_url);
		
		if(empty($content)){
			echo  "\r\n<BR/>error. get content failed.url:$page_url<BR/>";
			return false;
		}

		if ($rules['Crawl']['targetcharset'] == 'GBK') {
			$content = Charset::gbk_utf8($content);
		} elseif ($rules['Crawl']['targetcharset'] == 'BIG5') {
			$content = Charset::big5_utf8($content);
		}

		if (!empty($rules['Crawl']['contentreplace'])) {
			// 替换内容中要替换的部分
			$contentreplace = optionstr_to_array($rules['Crawl']['contentreplace']);
			foreach ($contentreplace as $reg => $str) {
				if(strpos($reg,'[selector]')===0){ // 为选择器
					$selector =  substr($reg,10);
					$html = str_get_html($content);
					//TODO. 是否保留script，保留时，相对地址的调用改成绝对地址调用。
					//TODO. clear方法有问题，删除节点时，结尾标记删除失败,造成页面错乱。如</div>  </script>
					foreach($html->find($selector) as $item){
						$item->clear();
					}
					$content = $html->outertext;
				}
				else{
					$reg = str_replace($replaceA, $replaceB, $reg);
					// /U修正符，不要与？号的非贪婪模式一起使用，否则又变成贪婪匹配了。
					$content = preg_replace('/' . $reg . '/iUs', $str, $content);
				}
			}
		}

		//print_r($rules['Crawl']['regexp_xml']);
		$xmlarray = xml_to_array($rules['Crawl']['regexp_xml']);
// 		print_r($xmlarray);
		// 正则匹配找出需要的内容
		foreach ($xmlarray['xml'] as $field => $regular) {
			// /U修正符，不要与？号的非贪婪模式一起使用，否则又变成贪婪匹配了。
			$regular = str_replace('/', '\/', $regular);
			$regular = '/' . $regular . '/iUs';
			//echo $regular;
			if (strpos($field, 'regexp_') !== false && preg_match($regular, $content, $innermatches)) {
				$fieldname = substr($field, 7);
				if ($fieldname == 'created' && $innermatches[1]) {
					$innermatches[1] = str_replace(array('年', '月', '日', '&nbsp;'), array('-', '-', ' ', ' '), $innermatches[1]);
					$innermatches[1] = date('Y-m-d H:i:s', strtotime($innermatches[1]));
				}
				$matched_data[$fieldname] = trim($innermatches[1]);
			}
		}

		if ((empty($matched_data['name']) && empty($matched_data['title']))) { // || empty($matched_data['content'])
			echo  "\r\n<BR/>error. 标题没有匹配上.<BR/>";
			// 抓取内容失败，设置published状态为-1，（自动发布时，只处理发布状态为0的）
			$this->CrawlTitleList->update(array('published' => -1),array('id' => $rules['CrawlTitleList']['id']));
			print_r('<a href="'.$rules['CrawlTitleList']['remoteurl'].'" title="'.$rules['CrawlTitleList']['name'].'" onclick="return true;" target="_blank">'.usubstr($rules['CrawlTitleList']['name'],0,24).'</a>---');
			print_r($rules['Crawl']['title']."<BR/>");
			return false;
		}
		if(!empty($xmlarray['xml']['regexp_content']) && empty($matched_data['content'])){
			echo  "\r\n<BR/>error. 内容没有匹配上.<BR/>";
			return false;
		}
		
		// 去掉内容中的超链接。
		$data_content = trim(preg_replace('/<a[^>]+?>(.+?)<\s*\/a\s*>/is', '\\1', $matched_data['content']));
		unset($matched_data['content']);
		
		// 过滤抓取的内容
		$data_content = $this->HtmlPurifier->filter($data_content);
		if(!empty($data_content)){
			// 去掉内容中的注释。在内容匹配后去除。匹配内容时，注释文字可能作为匹配规则
			$html = str_get_html($data_content);
			$comments = $html->find('comment');
			foreach($comments as $comment){
				$data_content = str_replace((string)$comment, '', $data_content);
			}
		}


		// 摘要
		if (empty($matched_data['summary'])) {
			//删除内容中的style与script项，去除内容中的标签
			$strip_summary = preg_replace('/<style[^>]*>.+?<\/style>/is', '', $data_content);
			$strip_summary = preg_replace('/<script[^>]*>.+?<\/script>/is', '', $strip_summary);
			$strip_summary = strip_tags($strip_summary);
			$strip_summary = preg_replace('/\s+/is', ' ', $strip_summary); //替换多余的空白符
			$matched_data['summary'] = trim(usubstr($strip_summary, 0, 100));
		}
		// 来源
		if (empty($matched_data['origin'])) {
			$matched_data['origin'] = $rules['Crawl']['origin'];
		}

		// 将标题+摘要+内容合并到一起求关键字。增加主要关键字的权重
		$keywords = $this->WordSegment->segment($this->data[$targetModel]['summary'] . '=' . $this->data[$targetModel]['summary'] . '=' . $this->data[$targetModel]['content']);
		//print_r($keywords);
		$seokeywords = array();
		$mainkeywords = array();
		foreach ($keywords as $k => $v) {
			if ($k < 5) {
				$mainkeywords[] = $v;
			}
			if ($k < 20) {
				$seokeywords[] = $v;
			} else {
				break;
			}
		}
		$matched_data['seokeywords'] = implode(',', $seokeywords); // 20个词作为seokeywords
		$matched_data['keywords'] = implode(',', $mainkeywords); // 5个词作为keywords

		if ($rules['Crawl']['saveimg']) {
			$save_images = CrawlUtility::saveImagesInContent($data_content, $page_url, $rules['Crawl']['imgprefix']);
			if (empty($matched_data['coverimg'])) {
				$matched_data['coverimg'] = array_shift($save_images['coverimg']);
			}
			$data_content = $save_images['content'];
		}
		if (empty($matched_data['created'])) {
			// 发布时间设为当前采集的时间
			$matched_data['created'] = date('Y-m-d H:i:s');
		}

		if (empty($matched_data['name'])){
			$matched_data['name'] = $matched_data['title'];
		}
		$matched_data['name'] = trim(strip_tags($matched_data['name']));

		echo 'Get <a href="'.$page_url.'">' . $matched_data['name'] . "</a><BR/>\r\n";

		if (empty($matched_data['slug'])) {
			$matched_data['slug'] = String::uuid();
		}
		print_r($matched_data);

		if ($this->CrawlTitleList->update(array(
        		'crawl_content_flag' => 1,
        		'name' => $this->CrawlTitleList->escape_string($matched_data['name']),
        		'coverimg' => $this->CrawlTitleList->escape_string($matched_data['coverimg']),
        		'content' => $this->CrawlTitleList->escape_string($data_content),
        		'serialize_info' => $this->CrawlTitleList->escape_string(json_encode($matched_data)),
		),
		array('id' => $rules['CrawlTitleList']['id']))
		) {
			echo 'get success';
			return true;
		}else {
			print_r($this->{$targetModel}->validationErrors);
			return false;
		}
	}

	public function admin_view($id = null){
		$modelClass = $this->modelClass;
		$datas = $this->{$modelClass}->find('first', array('conditions'=>array('id'=>$id)));
				
		if(empty($datas['CrawlTitleList']['content'])){
			$this->admin_crawlSingleContent($id);
			$this->autoRender = true;
			$datas = $this->{$modelClass}->find('first', array('conditions'=>array('id'=>$id)));
		}
		
		if(!empty($datas[$modelClass]['serialize_info'])){
			$datas[$modelClass]['serialize_info'] = array_to_table(json_decode($datas[$modelClass]['serialize_info'],true));
		}
		$this->set('item', $datas[$modelClass]);
		$this->set('_extschema', $this->{$modelClass}->getExtSchema());
	}
	
	/*
	 * 自动发布未发布的数据
	 */
	public function admin_autopublish(){
		
		$crawl_title_lists = $this->CrawlTitleList->find('all',array(
				'conditions'=> array('published'=>0,'deleted'=>0),
				'order' => 'CrawlTitleList.id asc',
				'limit' => 20,
				'page' => 1,
				'fields' => array('id'),
		));
		foreach($crawl_title_lists as $item){
			$id = $item['CrawlTitleList']['id'];
			if(defined('IN_CLI')){
				$this->TaskQueue->add('/admin/crawl_title_lists/publishSingle/'.$id);
			}
			else{
				$this->admin_publishSingle($id);
			}
		}
		if(!defined('IN_CLI')){
			echo '<meta http-equiv="refresh" content="5; url='.Router::url(array('action'=>'autopublish')).'" />';
		}
		exit;		
	}

	/**
	 * 数据发布
	 * @see manage/Controller/AppController#admin_publish($ids)
	 */
	public function admin_publish($ids = null) {
		$this->autoRender = false;
		if (!$ids) {
			$this->redirect(array('action' => 'index'));
		}
		$id_array = explode(',', $ids);
		foreach($id_array as $id){
			if($id){
				if(defined('IN_CLI')){
					$this->TaskQueue->add(array('action' => 'publishSingle', $id,'admin'=>true));
				}
				else{
					$this->admin_publishSingle($id);
				}
			}
		}		
	}

	public function admin_publishSingle($crawl_data_id){
		$this->autoRender = false;
		 
		$data = $this->CrawlTitleList->find('first',array(
               'conditions' => array('CrawlTitleList.id' => $crawl_data_id),
			)
		);
		if(empty($data['CrawlTitleList']['content'])){
			$this->admin_crawlSingleContent($crawl_data_id);
		}
		 
		$joinoptions = array();
		$joinoptions[] = array(
            'table' => Inflector::tableize('CrawlRelease'),
            'alias' => 'CrawlRelease',
            'type' => 'inner',
            'conditions' => array('CrawlRelease.`crawl_id` = CrawlTitleList.`crawl_id`'),
		);
		$joinoptions[] = array(
            'table' => Inflector::tableize('CrawlReleaseSite'),
            'alias' => 'CrawlReleaseSite',
            'type' => 'inner',
            'conditions' => array('CrawlRelease.`siteid` = CrawlReleaseSite.`id`'),
		);
		$publishinfos = $this->CrawlTitleList->find('all',
			array(
                'conditions' => array('CrawlTitleList.id' => $crawl_data_id),
                 'joins' => $joinoptions,
                 'fields' => array('CrawlRelease.*','CrawlReleaseSite.*', 'CrawlTitleList.*'),
			)
		);
		if(empty($publishinfos)){
			print_r($data);
			echo '没有设置发布规则。请先设置发布规则<BR/>';
			return false;
		}

		foreach($publishinfos as $publishinfo){
			if(!empty($publishinfo['CrawlTitleList']['serialize_info'])){
				$publishinfo['CrawlTitleList']['serialize_info'] = json_decode($publishinfo['CrawlTitleList']['serialize_info'],true);
				$publishinfo['CrawlTitleList'] = array_merge($publishinfo['CrawlTitleList']['serialize_info'],$publishinfo['CrawlTitleList']);
				unset($publishinfo['CrawlTitleList']['serialize_info']);
			}
			 
			$sitetype = $publishinfo['CrawlReleaseSite']['site_type'];
			if(method_exists($this,'_publish_to_'.$sitetype)){
				$this->{'_publish_to_'.$sitetype}($publishinfo);
			}
			else{
				$this->_publish_to_default($publishinfo);
			}
		}
		
		parent::admin_publish($crawl_data_id);
	}

	private function _publish_to_default($data){
		$apiurl = $data['CrawlReleaseSite']['apiurl'];
		if(strpos($apiurl,'?')!==false){
			$apiurl .= '&sec='.$data['CrawlReleaseSite']['sec_code'].'&model='.$data['CrawlRelease']['model_to'];
		}
		else{
			$apiurl .= '?sec='.$data['CrawlReleaseSite']['sec_code'].'&model='.$data['CrawlRelease']['model_to'];
		}

		if(empty($data['CrawlTitleList']['content'])){
			echo 'empty content. ';
			return false;
		}

		App::uses('RequestFacade', 'Network');

		$postdata = array();
		$postdata['source_url'] = $data['CrawlTitleList']['remoteurl'];
		$postdata['cid'] = $data['CrawlRelease']['cid'];
		$postdata['created'] = $data['CrawlTitleList']['created'];
		$postdata = array_merge($postdata,$data['CrawlTitleList']);

		$response = RequestFacade::post($apiurl,$postdata);
		echo $content = $response->body();
		return true;
	}

	private function _publish_to_local($data){
		 
		$targetModel = $data['CrawlRelease']['model_to'];
		if(!empty($targetModel)){
			$this->loadmodel($targetModel);
			$extschema = $this->{$targetModel}->getExtSchema();
			$keys = array_keys($extschema);
			$targetdata = array();
			foreach($keys as $field_name){
				if($field_name=='id'){
					continue;
				}
				if(!empty($data['CrawlTitleList'][$field_name])){
					if($extschema[$field_name]['type']!='content'){ // 非长文本
						$targetdata[$field_name] = strip_tags($data['CrawlTitleList'][$field_name]);
					}
					else{
						$targetdata[$field_name] = $data['CrawlTitleList'][$field_name];
					}
				}
			}
			$targetdata['cate_id'] = $data['CrawlRelease']['cid'];
			$targetdata['published'] = 1; // 发布时，将数据发布到对应表，设置为已发布状态，前台能直接浏览。
			
			if(!empty($data['CrawlRelease']['serialize_info'])){
				/*
				 * 特殊条件设定的各分类选项的值，如房地产资讯信息（8项分类）
				 */
				$serialize_info = unserialize($data['CrawlRelease']['serialize_info']);
				$replaceA = array("/", "?", "'");
       			$replaceB = array('\/', '\?', "\'");
				foreach($serialize_info['conditions'] as $val){
					$val['field_preg'] = str_replace($replaceA, $replaceB, $val['field_preg']);
					if(preg_match('/' . $val['field_preg'] . '/iUs',$data['CrawlTitleList'][$val['source_field']])){
						$targetdata[$val['target_field']] = $val['target_value'];
					}
				}
			}
			
			$this->{$targetModel}->create();
			if(in_array('remoteurl',$keys)){
				$this->{$targetModel}->recursive = -1;
				$havegot = $this->{$targetModel}->findByRemoteurl($data['CrawlTitleList']['remoteurl']);
				if (!empty($havegot[$targetModel])) {
					$targetdata['id'] = $havegot[$targetModel]['id'];
				}
			}
			if ($this->{$targetModel}->save($targetdata)) {
				echo 'publish success.<BR/>';
			} else {
				print_r($this->{$targetModel}->validationErrors);
			}
		}
	}
}