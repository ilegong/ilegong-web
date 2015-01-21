<?php
/* 不能改成file，名字与lib/file.php库文件发生冲突 V1.3 */
class UploadfilesController extends AppController {
	var $name = 'Uploadfiles';
	var $helpers = array (
			'Html' 
	);
	var $components = array (
			'Session',
			'SwfUpload' 
	);
	
	function beforeFilter() {
		// print_r($_POST);print_r($_COOKIE);print_r($_FILES);exit;
		// flash传输过来的，只使用session验证
		if (isset ( $_POST ['PHP_SESSION_ID'] ) && ! empty ( $_POST ['PHP_SESSION_ID'] )) {
			$this->Session->id ( $_POST ['PHP_SESSION_ID'] );
			$_COOKIE ['PHPSESSID'] = $_POST ['PHP_SESSION_ID'];
			$_COOKIE ['SAECMS'] ['Auth'] ['Staff'] = $_POST ['SAECMS'] ['Auth'] ['Staff'];
		}
		$this->autoRender = false;
		parent::beforeFilter ();
	}
	
	function admin_update() {
		if (! empty ( $this->data )) {
			if (isset ( $this->data ['Uploadfile'] ) && is_array ( $this->data ['Uploadfile'] )) {
				foreach ( $this->data ['Uploadfile'] as $fk => $file ) {
					$this->Uploadfile->create ();
					$fileinfo = array ();
					$fileinfo ['id'] = $file ['id'];
					$fileinfo ['name'] = $file ['name'];
					// print_r($fileinfo);
					$this->Uploadfile->save ( $fileinfo, true, array (
							'name' 
					) );
				}
			}
		}
	}
	function admin_listfile($uploadmodel = 'Article', $listmodel = 'all', $field = 'ckeditor', $page = 1) {
		$this->autoRender = true;
		$this->set ( 'uploadmodel', $uploadmodel );
		$conditions = array ();
		if ($listmodel != 'all') {
			$conditions ['modelclass'] = $listmodel;
		}
		if ($field == 'ckeditor') {
			$conditions ['fieldname'] = 'ckeditor';
		}
		$pagenum = 20;
		$uploadfiles = $this->Uploadfile->find ( 'all', array (
				'conditions' => $conditions,
				'limit' => $pagenum,
				'page' => $page 
		) );
		$totalnum = $this->Uploadfile->find ( 'count', array (
				'conditions' => $conditions 
		) );
		
		$this->set ( 'uploadfiles', $uploadfiles );
		// print_r($uploadfiles);
		App::uses ( 'Page', 'Lib' );
		$pagelinks = new Page ( $totalnum, $pagenum, "/admin/uploadfiles/listfile/$uploadmodel/$listmodel/$field/", $page );
		$page_navi = $pagelinks->renderNav ( 10 );
		
		$this->set ( 'page_navi', $page_navi );
	}	
	/**
	 * 文件管理 for ckeditor
	 * 此页面不调用layout.
	 * 参考 https://github.com/simogeo/Filemanager
	 * 大量的个性化修改，无法复用升级
	 */
	function admin_filemanage(){
		$this->layout = null;
		App::import('Vendor', 'Filemanager', array('file' => 'filemanage'.DS.'filemanager.class.php'));
		$config = array();
		$config['culture'] = 'zh-cn';
		$config['date'] = 'd M Y H:i';
		$config['icons']['path'] = (APP_SUB_DIR).'/img/icons/fileicon/';
		$config['icons']['directory'] = '_Open.png';
		$config['icons']['default'] = 'default.png';
		$config['upload']['overwrite'] = false; // true or false; Check if filename exists. If false, index will be added
		$config['upload']['size'] = false; // integer or false; maximum file size in Mb; please note that every server has got a maximum file upload size as well.
		$config['upload']['imagesonly'] = false; // true or false; Only allow images (jpg, gif & png) upload?
		$config['images'] = array('jpg', 'jpeg','gif','png');
		$config['unallowed_files']= array('.htaccess','.php');
		$config['unallowed_dirs']= array();
		$config['doc_root'] = UPLOAD_FILE_PATH; // No end slash
		$config['plugin'] = null;
		$fm = new Filemanager($config);
		/* 无参数传入时,调用模板生成页面，此页面不调用layout. */
		if(!empty($_REQUEST['mode'])) {
			if(!empty($_GET['mode'])) {
				switch($_GET['mode']) {	
					case 'getinfo':		
						if($fm->getvar('path')) {
							$response = $fm->getinfo();
						}
						break;
					case 'getfiles':
						if($fm->getvar('path')) {
							$response = $fm->getfiles();
						}
						break;
					case 'getfolder':						 
						if($fm->getvar('path')) {
							$response = $fm->getfolder();
						}
						break;		
					case 'rename':		
						if($fm->getvar('old') && $fm->getvar('new')) {
							$response = $fm->rename();
						}
						break;		
					case 'delete':		
						if($fm->getvar('path')) {
							$response = $fm->delete();
						}
						break;
					case 'addfolder':		
						if($fm->getvar('path') && $fm->getvar('name')) {
							$response = $fm->addfolder();
						}
						break;		
					case 'download':
						if($fm->getvar('path')) {
							$fm->download();
						}
						break;
					case 'preview':
						if($fm->getvar('path')) {
							$fm->preview();
						}
						break;
					default:
						$fm->error($fm->lang('MODE_ERROR'));
						break;		
				}
		
			} else if(isset($_POST['mode']) && $_POST['mode']!='') {		
				switch($_POST['mode']) {
					case 'add':		
						if($fm->postvar('currentpath')) {
							$fm->add();
						}
						break;
					default:						
						$fm->error($fm->lang('MODE_ERROR'));
						break;
				}
		
			}
			echo json_encode($response);
			exit();
		}
	}
	
	function admin_uploadtest() {
		/* 无操作，仅调用admin_uploadtest模版，供开发测试使用*/
	}
	/**
	 * 保存swfupload上传的文件。
	 * 
	 * post值选项：
	 * 
	 * "no_db" : 1, //不保存到数据库，
	 * "no_thumb" : 1, // 不生成缩略图，
	 * "save_folder":'/', //传入要保存的目标文件夹，相对于webroot目录，
	 */
	function admin_upload() {
		/* $file_post_name 默认为upload，
		ckeditor传入的文件字段名为upload。但无$_POST ['file_post_name']
		*/
		$file_post_name = $_POST['file_post_name']?$_POST['file_post_name']:'upload';
		$info = array ();
		$info ['status'] = '0';
		$info ['fieldname'] = $file_post_name;
		if (isset ( $this->params['form'] [$file_post_name] )) {
			// upload the file
			if(!empty($_REQUEST['no_thumb'])){ //图片不自动生成缩略图
				$this->SwfUpload->gen_thumb = false;
			}
			if(!empty($_REQUEST['save_folder'])){ //指定保存位置
				$this->SwfUpload->uploadpath = UPLOAD_FILE_PATH.$_POST['save_folder'];
			}
			elseif(!empty($_REQUEST['type'])){ //ckeditor传入了指定的类型，如images,flashes,videos
				$this->SwfUpload->setSavePath($_REQUEST['type']);
			}
			$this->SwfUpload->file_post_name = $file_post_name;
			
			if ($fileifo = $this->SwfUpload->upload ()) {				
				if($_REQUEST['no_db']){// 不保存到数据库，在ckeditor中上传文件的场景					
					$info ['status'] = '1';
					$info =array_merge($info,$fileifo);
					$file_url = str_replace('//','/',UPLOAD_FILE_URL.$fileifo['fspath']);
					if(is_image($file_url)){
						$info['message'] = '<a href="'.$file_url.'" title="'.__( 'Preview').'" target="_blank"><img src="'.$file_url.'" style="max-height:120px"/></a>';
					}
					else{
						$info['message'] = '<a href="'.$file_url.'" target="_blank">'.__( 'Preview').'</a>';
					}
				}
				else{
					$file_model_name = $_POST['file_model_name'];
					$modelname = Inflector::classify ( $this->name );
					$this->data [$modelname] ['modelclass'] = $file_model_name;
					$this->data [$modelname] ['fieldname'] = $file_post_name;
					$this->data [$modelname] ['name'] = $fileifo['filename'];
					$this->data [$modelname] ['size'] = $this->params ['form'] [$file_post_name] ['size'];
					$this->data [$modelname] ['fspath'] = $fileifo['fspath'];
					$this->data [$modelname] ['type'] = $fileifo['file_type'];
					if (empty($_REQUEST['no_thumb']) && 'image' == substr ($fileifo['file_type'], 0, 5 )) {
						$this->data [$modelname] ['thumb'] = $fileifo['thumb'];
						$this->data [$modelname] ['mid_thumb'] = $fileifo['mid_thumb'];
					}
					if (! ($file = $this->Uploadfile->save ( $this->data ))) {
						$this->Session->setFlash ( 'Database save failed' );
						$info ['message'] = $this->SwfUpload->filename . ' Database save failed'; // 保存记录时失败
					} else {
						$info ['status'] = '1';
						$file_id = $this->Uploadfile->getLastInsertId ();
						$info ['message'] = '<li class="upload-fileitem clearfix" id="upload-file-'.$file_id.'">';
						
						if (substr ( $this->data [$modelname] ['thumb'], 0, 7 ) != 'http://') {
							$file_url = UPLOAD_FILE_URL.str_replace ( '//', '/', ($this->data [$modelname] ['thumb']) );
						} else {
							$file_url = $this->data [$modelname] ['thumb'];
						}
						
						//$info ['message'] .= var_dump($this->SwfUpload,true).$this->data [$modelname] ['type'].'---'.$this->data [$modelname] ['thumb'];
						
						if ('image' == substr ( $this->data [$modelname] ['type'], 0, 5 )) {
							$info ['message'] .= '<img src="' . $file_url . '"/>';
						}
						$info ['message'] .= '<input type="hidden" name="data[Uploadfile][' . $file_id . '][id]" value="' . $file_id . '">
						<p>
						<label>'.__ ( 'Uploadfile Name').'</label>: <input type="text" name="data[Uploadfile]['.$file_id.'][name]" value="'.urldecode($this->SwfUpload->filename).'"/>
	        			<label>'.__ ( 'Sort Order').'</label>: <input style="width:30px;" type="text" name="data[Uploadfile]['.$file_id.'][sortorder]" value="0"/>
	        			<label>'.__ ( 'File Version').'</label>: <input style="width:60px;" type="text" name="data[Uploadfile]['.$file_id.'][version]" value=""/>
						</p>
						<p><label>'.__ ( 'Uploadfile Comment').'</label>:<textarea name="data[Uploadfile]['.$file_id.'][comment]" row="2" cols="60"></textarea></p>
						';
						
						// 使用 $this->request->webroot 连接在图片前面，而不用Router::url，避免后台添加图片时，文件都带上了 /manage/路径  
						
						if ('image' == substr ( $this->data [$modelname] ['type'], 0, 5 )) {
							$mid_thumb_url = str_replace ( '\\', '/', $this->data [$modelname] ['mid_thumb'] );
							$mid_thumb_url = UPLOAD_FILE_URL.str_replace ( '//', '/',$mid_thumb_url);
							if (substr ( $this->data [$modelname] ['fspath'], 0, 7 ) != 'http://') {
								$src_url = UPLOAD_FILE_URL.str_replace ( '//', '/', ($this->data [$modelname] ['fspath']) );
							} else {
								$src_url = $this->data[$modelname]['fspath'];
							}
							
							$info ['message'] .= '<p>
							<a href="' . $src_url . '" target="_blank">' . __ ( 'Preview') . '</a>
			        		<a href="javascript:void(0);" onclick="insertHTML(\'&lt;img id=&#34;file_' . $file_id . '&#34; src=&#34;' . $file_url . '&#34; >\')">' . __ ( 'Insert') . '</a>
			        		
			        		<a class="upload-file-delete" rel="'.$file_id.'" href="#" data-url="' . Router::url ( '/admin/uploadfiles/delete/' . $file_id.'.json') . '">' . __ ( 'Delete') . '</a> 
			        		<a href="javascript:void(0);" onclick="setCoverImg(\'' . $this->data [$modelname] ['modelclass'] . '\',\'' . $mid_thumb_url . '\');">' . __ ( 'Set as title img') . '</a></p>
							
							';
						}
						$info ['message'] .= '</li>';
					}
				}
			} else {
				$info ['message'] = $this->SwfUpload->errorMessage;
				$this->Session->setFlash ( $this->SwfUpload->errorMessage );
			}
		} else {
			$info ['message'] = 'empty field name';
		}
		if($_REQUEST['return']=='ckeditor'){
			if($info ['status']){
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(2, "'.(UPLOAD_FILE_URL.$fileifo['fspath']).'", "");</script>';
			}
			else{
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(2, "", "'.$info ['message'].'");</script>';
			}
		}
		else{
			$result = json_encode ( $info );
			echo $result;
		}
		exit ();
	}
	
	function open($id) {
		$file = $this->get ( $id );
		if (isset ( $file )) {
			$this->redirect ( $file ['Uploadfile'] ['path'] . $file ['Uploadfile'] ['name'] );
			exit ();
		}
	}
	function admin_delete($id) {
		$file = $this->Uploadfile->findById ( $id );

        $this->log("prevent trying to delete a Upload file $id, values:".var_export($file, true));


		// 删除文件
		//$this->SwfUpload->deletefile ( $file ['Uploadfile'] ['fspath'] );
		// 从数据库删除
		parent::admin_delete($id);
	}
	
	function get($id) {
		// get file info
		$file = $this->Uploadfile->findById ( $id );
		return $file;
	}
}
?>