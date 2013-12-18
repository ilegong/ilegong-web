<?php

App::import('Vendor', 'lessc', array('file' => 'lessphp'.DS.'lessc.inc.php'));

class StylevarsController extends AppController {

    var $name = 'Stylevars';
    
    /**
     * 除了后台设置的默认风格外，支持get方式传入styleid来选择风格。
     * @param string $type responsive或者bootstrap， responsive对应手机 ， bootstrap对应网页.
     */
    public function getcss($type='bootstrap'){
    	if($_GET['styleid']){
    		$styleid = intval($_GET['styleid']);
    	}
    	else{
    		$styleid = Configure::read('Site.style');
    	}
    	if(empty($styleid)){
    		$styleid=161;
    	}
    	$cachekey = 'bootstrap_style_'.$type.'_'.$styleid;
    	$css =Cache::read($cachekey);
    	if($css === false){
	    	$styles = $this->Stylevar->find('all',array('conditions'=>array('styleid'=>$styleid)));
	    	$variables = array();
	    	foreach($styles as $style){
	    		$variables[$style['Stylevar']['skey']] = $style['Stylevar']['sval'];
	    	}
	    	$less = new lessc;
	    	$less->setFormatter("compressed");//lessjs compressed classic
	    	$less->setImportDir(array(ROOT.'/data/bootstrap/less/'));
	    	$less->setVariables($variables);
	    	$css = $less->compileFile(ROOT.'/data/bootstrap/less/'.$type.'.less');
	    	$css = preg_replace('/url\(["|\']?(.+?)["|\']?\)/ies',"'url('.\$this->fixurl('\\1').')'",$css);
	    	Cache::write($cachekey,$css);
    	}
    	header('Content-Type:text/css');
    	echo $css;
    	exit;
    }
    

    private function fixurl($url,$path = CSS){
    	if(substr($url,0,4)=='/img'){ // 已/img 开头，IMAGES_URL计算img所在的二级目录，img的二级目录和程序的二级目录不一样。如manage何img共用一个img目录
    		return str_replace('//','/',dirname(IMAGES_URL).$url);
    	}
    	while(substr($url,0,3)=='../'){
    		$path = dirname($path);
    		$url = substr($url,3);
    		if($path.DS == WWW_ROOT){
    			return str_replace('//','/',dirname(IMAGES_URL).'/'.$url);
    		}
    	}
    	$path = str_replace(WWW_ROOT, '', $path);
    	$path = str_replace(array('\\','//'), '/', $path);
    	return $url = str_replace('//','/',dirname(CSS_URL).'/'.$path.'/'.$url); // css的相对路径，计算CSS_URL所在的二级目录
    }
}

?>