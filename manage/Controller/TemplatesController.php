<?php
class TemplatesController extends AppController{
	
	var $name = 'Templates';
	
// 生成记录，将模板文件夹中的数据，导入数据库

	function admin_import($app='app',$theme='default') {		
		
		$path = ROOT.DS.$app.DS.'View'.DS.'Themed'.DS.$theme.DS;
		$this->_importfolder($path,$app,$theme);
		echo 'over';exit;
    }
    
    
    function  _importfolder($path,$app,$theme){
		$uploadfileurl=array();
		$foldertree=array();
		$handle  = opendir($path);
		$i=0;
		while($file = readdir($handle)){
			$newpath=$path.DS.$file;
			echo $newpath.'<br/>';
			if(is_dir($newpath)){
				if($file!=".." && $file!="." && substr($file,0,1)!='.' ){					
					$this->_importfolder($newpath,$app,$theme);
				}
			}
			else{				
				$relatepath = str_replace( ROOT.DS.$app.DS.'View'.DS.'Themed'.DS.$theme.DS,'',$newpath);
				//$relatepath = dirname($relatepath);
				$this->_importFile($newpath,$app,$theme,$relatepath);			
			}
		}
	}
	
	/**
	 * 导入模板文件到数据库
	 * @param unknown_type $file  文件路径
	 * @param unknown_type $appname  app名
	 * @param unknown_type $theme    主题名
	 * @param unknown_type $relatepath  相对路径
	 */
	function _importFile($file,$appname,$theme,$relatepath){
		$data  = array();
		$this->Template->create();
		//App::uses('File', 'Utility');
		$relatepath = str_replace('\\','/',$relatepath);
		// 去除开始的“/”
		$relatepath = preg_replace('|(^/+)|','',$relatepath);
		
		$data['Template']['content'] = file_get_contents($file);
		$data['Template']['name'] = $data['Template']['relatepath'] = $relatepath;
		$data['Template']['theme'] = $theme;
		$data['Template']['appname'] = $appname;
		$foldername = dirname($relatepath);
		$foldername = preg_replace('|(^/+)|','',$foldername);
		$data['Template']['foldername'] = $foldername;
		$this->Template->save($data);
	}
}