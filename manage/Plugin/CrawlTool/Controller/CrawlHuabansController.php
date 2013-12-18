<?php
App::uses('Xml', 'Utility');
App::uses('CrawlUtility', 'Utility');
App::uses('File', 'Utility');
App::uses('Charset', 'Lib');
App::uses('ImageResize','Lib');

class CrawlHuabansController extends CrawlToolAppController {
	
	/**
	 * /manage/admin/crawl_tool/CrawlHuabans
	 */
	
	public function admin_index(){
		set_time_limit(0);
		$url = 'http://huaban.com/';
		$content = CrawlUtility::getRomoteUrlContent($url);
		preg_match('/app\.page\["pins"\] = (\[.+?\]);/',$content,$matches);
		$array = json_decode($matches[1],true);
		$this->loadModel('Photo');
		if(!empty($array)){
			foreach($array as $item){
				$ext = explode(".",$item['orig_source']);
				$ext = end($ext);
				$ext = strtolower($ext);
				if(!in_array($ext,array('jpg','gif','png'))){
					continue;
				}
				
				$imagefile = CrawlUtility::saveImagesByUrl($item['orig_source'],$item['orig_source'],'hb_cover_');
				
				$coverimg = $imagefile.'cover.'.$ext;
				//生成小图,覆盖原图。
				$image = new ImageResize();
				$image->resizefile(WWW_ROOT.$imagefile,WWW_ROOT.$coverimg,192);
				$data = array();
				$data['coverimg'] = $coverimg;
				$data['content'] = "<img src=\"".Router::url($imagefile)."\"><br/>".$item['raw_text'];
				
				$data['name']= usubstr($item['raw_text'],0,12);
				$data['published'] = 1;
				$data['cate_id'] = 109;
				$this->Photo->id=null;
				$this->Photo->create();
				$this->Photo->save($data);
				$id = $this->Photo->getLastInsertID();
				$this->Photo->save(array('slug'=>$id));
				//$content_url = 'http://huaban.com/pins/'.$item['pin_id'].'/';
				print_r($data);
				//$inner_content = CrawlUtility::getRomoteUrlContent($url);
			}
		}
		echo "over";
		exit;
	}
}
?>