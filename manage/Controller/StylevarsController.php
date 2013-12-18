<?php

App::import('Vendor', 'lessc', array('file' => 'lessphp'.DS.'lessc.inc.php'));

class StylevarsController extends AppController {

    var $name = 'Stylevars';
    
    public function admin_themeroller(){
    	
    }
    /**
     *
     * @param unknown_type $type  responsive for phone and bootstrap for web.
     */
    public function admin_getcss($type='bootstrap'){
    	$styleid = Configure::read('Site.style');
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

    private function _import_by_viriables($styleid,$content){
    	$vars = explode("\n",$content);
    	foreach($vars as $line){
    		$line = trim(preg_replace('/\/\/.*$/','',$line)); // 去掉注释内容
    		$line = preg_replace('/;.*$/','',$line); // 去掉“;”及其后的内容
    	
    		$var = explode(':',$line);
    		//echo $line."\n";
    		if(count($var)==2){
    			if(substr($var[0],0,1)=='@'){
    				$var[0] = substr($var[0],1);
    			}
    			$data = array();
    			$data['skey']=trim($var[0]);
    			$data['sval']=trim($var[1]);
    			$data['styleid']=$styleid;
    			$hasgot = $this->Stylevar->find('first',array('conditions'=>array('skey'=>$data['skey'],'styleid'=>$styleid)));
    			if(!empty($hasgot)){
    				$this->Stylevar->id = $hasgot['Stylevar']['id'];
    				$hasgot['Stylevar']['sval'] = $data['sval'];
    				$hasgot = $this->Stylevar->save($hasgot);
    			}
    			else{
    				$this->Stylevar->create();
    				$hasgot = $this->Stylevar->save($data);
    			}
    		}
    	}
    	Cache::delete('bootstrap_style_'.$styleid);
    }
    public function admin_import($styleid=161){
    	echo $variable_file = ROOT.'/'.'data/bootstrap/less/variables.less';
    	$content = file_get_contents($variable_file);
    	$this->_import_by_viriables($styleid,$content);
    	echo json_encode(array('success'=>'import success.'));
    	exit;
    }
	
    /**
     * 传入styleid的参数值 
     * @see AppController::admin_edit()
     */
    public function admin_edit($styleid){
    	load_lang('bootstrap');
    	if(!empty($_POST['EditByFile'])){
    		if(!empty($this->data['Stylevar']['variables'])){
    			$variable_file = $this->data['Stylevar']['variables']['tmp_name'];
    			$content = file_get_contents($variable_file);
    			$this->_import_by_viriables($styleid,$content);
    		}
    		$successinfo = array('success' => __('edit success'));
    		echo json_encode($successinfo);
    		exit;
    	}
    	elseif(!empty($_POST)){
			$this->Stylevar->saveAll($this->data['Stylevar']);
			$successinfo = array('success' => __('edit success'));
    		echo json_encode($successinfo);
			exit;
    	}
    	$styles = $this->Stylevar->find('all',array('conditions'=>array('styleid'=>$styleid)));
    	$this->set('styles',$styles);
    	$this->set('styleid',$styleid);
    }

}

?>