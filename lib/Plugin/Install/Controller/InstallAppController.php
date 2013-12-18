<?php
class InstallAppController extends Controller { 
	public $viewClass = 'Miao';
	
	var $helpers = array(
        'Html', 'Session', 'Form', 'Js', 
    );
    
    var $components = array('Session',);
    
	function beforeFilter() { 
		if (!empty($this->request->params['locale']) && $this->request->params['locale']!=DEFAULT_LANGUAGE) {
			//当含语言的参数时，设置base追加locale的内容，
			$this->request->base = $this->request->base.'/'.$this->request->params['locale'];
			define('APPEND_LOCALE_BASE', true);
			if($this->request->params['locale']=='zh-tw' && DEFAULT_LANGUAGE=='zh-cn'){
				$this->convertCode = 'g2b';//默认版本为简体，看繁体版本时，需要将简体转繁体显示，
			}
			elseif($this->request->params['locale']=='zh-cn' && DEFAULT_LANGUAGE=='zh-tw'){
				$this->convertCode = 'b2g';//默认版本为繁体，看简体版本时，需要将繁体转简体显示，
			}
			else{//非中文时，才修改语言版本。
				Configure::write('Config.language', $this->request->params['locale']);
			}
		}
		elseif (!empty($this->request->params['locale'])) {
			// 当传入locale为默认内容时，跳转去除locale参数。
			// [REQUEST_URI] => /saecms/trunk/zh-cn/?helo
			$url = str_replace($this->request->params['locale'],'',$this->request->url);
			$url = str_replace('//', '/', $url);
			$this->redirect($url);
		}
		
// 		$this->Auth->allowedActions = array('*');
		parent::beforeFilter(); 
	} 
}
?>