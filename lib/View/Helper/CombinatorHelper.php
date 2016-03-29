<?php
App::uses ( 'CssMin', 'Lib' );
//App::uses ( 'JSMin', 'Lib' );
App::uses ( 'JavaScriptPacker', 'Lib' );

/**
 * 将css,js文件汇总到一个文件输出,只支持数组方式传入参数,文件后缀名不能省略
 *
 * echo $this->Combinator->css(array(...)); 
 * // Output Javascript files. like $this->Html->css()
 * 
 * echo $this->Combinator->script(array(...)); 
 * // Output CSS files. like $this->Html->script()
 *
 * @author arlonzou
 *         @2012-9-11下午5:00:37
 */
App::import('Vendor', 'lessc', array('file' => 'lessphp'.DS.'lessc.inc.php'));
class CombinatorHelper extends Helper {
	
	public $helpers = array('Html');
	
	function css($url) {
		return $this->get_css( $url );
	}
	
	function script($url) {
			return $this->get_js($url);			
	}

	private function get_js($url) {
		$cachefile = substr(md5(implode('_',$url)),0,10).'.js';
		if (!Configure::read('debug') && file_exists ( WEB_VISIT_CACHE .$cachefile )) {
			return '<script src="' . WEB_VISIT_CACHE_URL . $cachefile . '" type="text/javascript"></script>';
		}
		else{			
			if(!Configure::read('debug') && (defined('IN_SAE') || is_writable(WEB_VISIT_CACHE))){
				//Get the content
				$file_content = '';
				foreach ($url as $file ) {
					if(substr($file,0,1)=='/'){
						$path = WWW_ROOT.$file; //WWW_ROOT目录下的文件
					}
					else{
						$path = JS.$file; // js目录下的文件
					}
					$content = file_get_contents ( $path );
					if(strpos($path,'.min.')===false){
						$packer = new JavaScriptPacker($content, 'None', true, true);
						$content = $packer->pack(); // packer js
// 						$content =  JSMin::minify ( $content ) ;// compress js !
					}
					$file_content .= "\n\n/*".basename($path)."*/\n" .$content ;
				}
				$file_content = trim ($file_content);
				
				$ret = file_put_contents(WEB_VISIT_CACHE.$cachefile, $file_content);
// 				var_dump($ret);
// 				App::uses ( 'File', 'Utility' );
// 				$file = new File(WEB_VISIT_CACHE.$cachefile);
// 				$file->open('w');
// 				$file->write($file_content);
// 				$file->close();
				
				return '<script src="' .WEB_VISIT_CACHE_URL.$cachefile . '" type="text/javascript"></script>';
			}
			else{
				return $this->Html->script($url);
			}
		}		
	}
	
	private function get_css($url) {
		$cachefile = substr(md5(implode('_',$url)),0,10).'.css';
		if (!Configure::read('debug') && file_exists(WEB_VISIT_CACHE. $cachefile )) {
			return '<link href="' .WEB_VISIT_CACHE_URL. $cachefile . '" rel="stylesheet" type="text/css" >';
		}
		if(!Configure::read('debug') && (defined('IN_SAE') || is_writable(WEB_VISIT_CACHE))){
			$file_content = '';
			foreach ($url as $file ) {
				if(substr($file,0,1)=='/'){
					$path = WWW_ROOT.$file;
				}
				else{
					$path = CSS.$file;
				}
				$content = file_get_contents ( $path );
				//url(../../img/desktop/gui/bar_top_link.png)
				$cur = str_replace('\\','\\\\',dirname($path)); // 转义路径中的反斜线，防止目录名含数字，如\3在正则中消失
				$content = preg_replace('/url\(["|\']?(.+?)["|\']?\)/ies',"'url('.\$this->fixurl('\\1','$cur').')'",$content);
				// $content = ... ;//处理图片的相对路径
				$file_content .= "\n\n".$content;
			}
			$file_content = str_replace(array('\\/','//'),'/',$file_content);
			// If compression is enable, compress it !
			$file_content = CssMin::minify ( $file_content );
			
			$ret = file_put_contents(WEB_VISIT_CACHE.$cachefile, $file_content);
			var_dump($ret);
			
// 			App::uses ( 'File', 'Utility' );
// 			$file = new File(WEB_VISIT_CACHE.$cachefile);
// 			$file->open('w');
// 			$file->write($file_content);
// 			$file->close();
			return '<link href="' .WEB_VISIT_CACHE_URL.$cachefile . '"  rel="stylesheet" type="text/css" ></script>';
		}
		else{
			return $this->Html->css($url);
		}
	}
	
	private function fixurl($url,$path=CSS){
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
		$url = dirname(CSS_URL).'/'.$path.'/'.$url;
		$url = str_replace(array('\\/','//'),'/',$url); // css的相对路径，计算CSS_URL所在的二级目录
		return $url;
	}
}