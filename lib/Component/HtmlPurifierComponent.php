<?php

class HtmlPurifierComponent extends Component {
	
	private $purifier;
	
	public function startup($controller){
		App::import('Vendor', 'HtmlPurifier', array('file' => 'htmlpurifier'.DS.'HTMLPurifier.auto.php'));
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Attr.EnableID', true);
		$this->purifier = new HTMLPurifier($config);
	}

    /**
     * 过滤html，去除非法的，不对称的标签。
     * @param string $html
     */
	public function filter($html){		
		return $this->purifier->purify($html);
	}
}

?>