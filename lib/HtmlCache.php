<?php
class HtmlCache{

	/**
	 * 写文件
	 * @param $path
	 * @param $content
	 */
	public static function writefile($path,$content){
		App::uses('File', 'Utility');
		$path = str_replace(APP_SUB_DIR,'',$path);
		$path = str_replace(array('/',"\\",DS.DS),array(DS,DS,DS),$path);
		
		if(defined('IN_SAE')){
			$path = DATA_PATH.$path;
			file_put_contents($path,$content);
		}
		else{
			$path = WWW_ROOT.$path;
			$file = new File($path,true);
			$file->write($content);
		}
	}
	
	public static function getfile($path){
		if(defined('IN_SAE')){
			$path = DATA_PATH.$path;
			if(file_exists($path)){
				return file_get_contents($path);
			}
		}
		else{
			$path = WWW_ROOT.$path;
			if(file_exists($path)){
				$file = new File($path,true);
				$file->read();
			}
		}
		return false;
	}
}