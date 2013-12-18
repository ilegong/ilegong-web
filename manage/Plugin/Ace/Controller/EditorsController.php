<?php
App::uses('Charset', 'Lib');
App::uses('File', 'Utility');

class EditorsController extends AceAppController {
	private $defaultlevel = 2;
	
	/**
	 * 忽略的文件类型
	 * @var unknown_type
	 */
	private $skip_extensions = array('zip','gif','jpg','png','doc','docx','rar','ico');
	private $skip_folders = array();
	private $allow_folders = array();
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->layout = 'admin_layout';
	}
	
	public function __construct($request = null, $response = null) {
		parent::__construct($request,$response);
		$this->skip_folders = array(
			ROOT.DS.'files',
			ROOT.DS.'img',
			ROOT.DS.'themeroller',				
			ROOT.DS.'lib'.DS.'Cake',
		);
		
		$this->allow_folders = array(
				'资源文件(css/img/js)' => WWW_ROOT,
				'前台模板' => ROOT.DS.'app'.DS.'View'.DS.'Themed',
				'后台模板' => ROOT.DS.'manage'.DS.'View'.DS.'Themed',
		);
	}
	
	public function admin_index(){
		$this->layout = 'admin_layout';
	}
	
	/**
	 * 加载文件内容，将内容转换为utf-8编码。
	 */
	public function admin_loadfile(){
		$file = $_GET['file'];
		$content = file_get_contents(ROOT.$file);
		$content = Charset::convert_utf8($content);
		echo $content;
		exit;
	}
	
	/**
	 * 
	 * @param string $path 目录
	 * @param int $depth  默认为1，此参数用于递归处理下级目录，限制递归的层级
	 * @param boolean $return	默认为false，此参数用于递归下级目录时，返回结果，否则无返回。
	 */
	public function admin_listfolder($path = ROOT,$depth=1,$return = false){
		$foldertree=array();
		if($depth==1 && $_GET['key']){
			$path = ROOT.$_GET['key'];
		}
		$json = $jsonfiles = array();
		if($path == ROOT || $path==''){
			foreach($this->allow_folders as $key=>$val){
				$json[] = array('isLazy'=>true,'title'=> $key,"isFolder"=> true, "key"=> str_replace(ROOT,'',$val));
					
			}
			$this->set('json',$json);
			$this->set('_serialize','json');
			return ;
		}
		$handle  = opendir($path);
		$i=0;		
		//print_r($this->allow_folders);
		$path = Charset::convert_utf8($path);
		while($file = readdir($handle)){
			$file = Charset::convert_utf8($file);			
			$newpath=$path.DS.$file;
			if(is_dir($newpath)){
				if($file!=".." && $file!="."){
					if(in_array($newpath,$this->skip_folders)){
						continue;
					}
					
					$tmp = array('title'=> $file,"isFolder"=> true, "key"=> str_replace(ROOT,'',$newpath));
					if($depth < $this->defaultlevel){
						$inerprefix = $prefix.$file.'/';
						$tmp['children'] = $this->admin_listfolder($newpath,$depth+1,true);
					}
					else{
						$tmp['isLazy'] = true;	
					}
					$json[] = $tmp; 
				}
			}
			else{
				//echo $path."=======\n";
				
// 				if(!in_array(ROOT.$path,$this->allow_folders)){
// 					continue;
// 				}
				$fileobj = new File($file);
				if(in_array(strtolower($fileobj->ext()),$this->skip_extensions)){
					continue;
				}
				$jsonfiles[] = array('title'=> $file,"key"=> str_replace(ROOT,'',$newpath));
				//$folderfiles[]=$newpath;
				$i++;
			}
		}
		// $json为目录，$jsonfiles 为文件
		$json = array_merge($json,$jsonfiles); // folder文件夹排在前面 
		if($return){
			return $json;
		}
		else{
			$this->set('json',$json);
			$this->set('_serialize','json');
		}
	}
	public function admin_savefile(){
		
		$filename = ROOT.$this->data['name'];
		$allow = false;
		foreach($this->allow_folders as $af){
			if(strpos($filename,$af)!==false){
				$allow = true;
			}
		}
		$info = array('msg'=>'error');
		
		$file = new File($filename, true);
		if ($allow && !empty($this->data['name']) ) {
			if ($file->write($this->data['content'])) {
				$info = array('msg'=>'save success');
			}
			$file->close();
		}
		echo json_encode($info);
		exit;
	}
	
	
	/**
	 * 修改文件，获取内容
	 * @param unknown_type $filename
	 */
	public function admin_editfile($filename='index.php'){
		$filename = ROOT.$filename;
		$allow = false;
		foreach($this->allow_folders as $af){
			if(strpos($filename,$af)!==false){
				$allow = true;
			}
		}
		
		if(file_exists($filename) && $allow){
			$filecontent = file_get_contents($filename);
		}
		else{
			$filecontent = '';
		}
		$this->set('filecontent',$filecontent);
	}
}
?>