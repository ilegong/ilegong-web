<?php
class ajaxesController extends AppController{
	
	var $name = 'ajaxes';
	
	var $json = array();
	
	function beforeFilter(){
		parent::beforeFilter();
		$this->autoRender = false;
//		header("Content-type: application/json");
	}
	function getfaceurl(){
		$this->autoRender = false;
		echo $this->_getRomoteUrlContent('http://t.sina.com.cn/face/aj_face.php?type=face');
		
	}
	
	function _getRomoteUrlContent($url,$referer='')
    {
    	App::import('Core', 'HttpSocket');
    	 $httpsocket = new HttpSocket();
    	 if(empty($referer)) $referer = $url;
    	 $request = array('header'=>array('Referer'=> $referer));
    	 $i=0;
    	 // 同一个地址，如果获取内容失败为空时，最多重复5次
    	 do{  	 
    	 	$content = $httpsocket->get($url,array(),$request);
    	 	$i++; 
    	 	echo "--times---$i\r\n";
    	 }while($i<5 && empty($content));
    	 
    	 return $content;
    }
	
	

}