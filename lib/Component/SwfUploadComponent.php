<?php 

class SwfUploadComponent extends Component {	
	public $params = array();
	public $errorCode = null;
	public $errorMessage = null;
	
	/**
	 * 文件保存的路径（绝对路径）
	 * @var string
	 */
	public $uploadpath;
	/**
	 * 文件访问的路径（相对路径）。在保存至sae时使用
	 * @var string
	 */
	public $relativePath;
	public $overwrite = true; // 覆盖同名文件,指经过加密后的文件名相同，可能性微乎其微；$this->savename = md5($filebase.time()).'_'.date('Ymd').$fileext;
	public $filename; // 上传的文件名
	
	public $savename ; // 保存的文件名
	public $file_type = '';
	/**
	 * 图片是否生成缩略图
	 * @var boolean
	 */
	public $gen_thumb = true;//是否生成缩略图
	
	public $file_post_name='photo';
	
	public $current_dir;
	
	/**
	 * Contructor function
	 * @param Object &$controller pointer to calling controller
	 */
	public function startup($controller) {
		// uploadpath 上传文件files所在的目录
		$this->current_dir = 		date('Ym');
		
		$this->uploadpath = UPLOAD_FILE_PATH.UPLOAD_RELATIVE_PATH.$this->current_dir . DS;
		$this->relativePath = UPLOAD_RELATIVE_PATH.$this->current_dir . DS;		
		$this->relativeUrl = str_replace(DS,'/',$this->relativePath);        
		$this->params = $controller->params;		
	}
	/**
	 * 设置上传文件的保存路径
	 * @param string $path
	 */
	public function setSavePath($path=''){
		if($path){
			$this->uploadpath = UPLOAD_FILE_PATH.UPLOAD_RELATIVE_PATH.$path.DS.$this->current_dir . DS;
			$this->relativePath = UPLOAD_RELATIVE_PATH.$path.DS.$this->current_dir . DS;
			$this->relativeUrl = str_replace(DS,'/',$this->relativePath);
		}
	}
	
	public function deletefile($relativepath){
		
		$file_path = UPLOAD_FILE_PATH.$relativepath;
		$pathinfo = pathinfo($file_path);
		if(in_array(strtolower($pathinfo['extension']),array('gif','jpg','bmp'))){
			@unlink($pathinfo['dirname'].DS.'thumb_s'.DS.$pathinfo['basename']);
			@unlink($pathinfo['dirname'].DS.'thumb_m'.DS.$pathinfo['basename']);
		}
		@unlink($file_path);		
	}
	
	/**
	 * Uploads a file to location
	 * @return boolean true if upload was successful, false otherwise.
	 */
	public function upload($file_model_name=null) {
		$ok = false;
		if ($this->validate($file_model_name)) {
			$this->filename = $this->params['form'][$this->file_post_name]['name'];
			
			$fileparts = explode('.', $this->filename);
			$fileext = strtolower(array_pop($fileparts));
			$filebase = urlencode(implode('.', $fileparts));
			if(in_array($fileext,array('php','asp','exe','cgi'))){
				$fileext = '_'.$fileext;
			}
			
			$this->savename = substr(md5($filebase.time()),0,11).'_'.date('md').'.'.$fileext;
			$ok = $this->write();
			if(!$ok){ return false; }
			
			$file_type = get_mime_type($this->uploadpath . $this->savename); // mime_content_type函数在php 5.3版本里取消了
			
			$uploainfo = array(
				'fspath' => $this->relativeUrl . $this->savename,
				'file_type' => $file_type,
				'filename' => $this->filename
			);
			if($this->gen_thumb && $ok && in_array($file_type,array ("image/png", "image/gif", "image/jpeg", "image/bmp", "image/jpg"))){
				
				App::uses('ImageResize','Lib');
				//生成小图
				$image = new ImageResize($this->savename);
				$image->PicDir = $this->uploadpath.'thumb_s';
				$folder = new Folder($image->PicDir, true, 0755);				
				$image->newWidth = $image->newHeight = Configure::read('Admin.min_image_size');
				$image->TmpName = $this->uploadpath . $this->savename;
				$image->resize();
				
				//生成中图
				$image = new ImageResize($this->savename);
				$image->PicDir = $this->uploadpath.'thumb_m';
				$folder = new Folder($image->PicDir, true, 0755);				
				$image->newWidth = $image->newHeight = 400;
				$image->TmpName = $this->uploadpath . $this->savename;
				$image->resize();
				
				// 生成大图，大图会覆盖原图片。 防止图片过来造成页面加载缓慢。
				Configure::read('Admin.max_image_size');
				$image = new ImageResize($this->savename);
				$image->PicDir = $this->uploadpath;                                
				$folder = new Folder($image->PicDir, true, 0755);				
				$image->newWidth = $image->newHeight = Configure::read('Admin.max_image_size');
				$image->TmpName = $this->uploadpath . $this->savename;
				$image->resize();

				$uploainfo['thumb']=$this->relativeUrl . 'thumb_s/' . $this->savename;
				$uploainfo['mid_thumb']=$this->relativeUrl . 'thumb_m/' . $this->savename;
			}
			if ($ok) {
				return $uploainfo;
			}
		}
		
		return $ok;
	}

	/**
	 * moves the file to the desired location from the temp directory
	 * @return boolean true if the file was successfully moved from the temporary directory to the desired destination on the filesystem
	 */
	public function write() {
		// Include libraries
		if (!class_exists('Folder')) {
			App::uses ('Folder','Utility');
		}
		$moved = false;
		
		if(defined('SAE_MYSQL_DB')){
			$stor = new SaeStorage();
			$moved = $stor->upload(SAE_STORAGE_UPLOAD_DOMAIN_NAME , $this->relativePath.$this->savename , $this->params['form'][$this->file_post_name]['tmp_name']);
		}
		else{
			$folder = new Folder($this->uploadpath, true, 0755);
			if (!$folder) {
				$this->setError(1500, 'File system save failed.', 'Could not create requested directory: ' . $this->uploadpath);
			} else {
				if (!($moved = move_uploaded_file($this->params['form'][$this->file_post_name]['tmp_name'], $this->uploadpath . $this->savename))) {
					$this->setError(1000, 'File system save failed.');
				}
			}
		}
		return $moved;
	}
	
	/**
	 * validates the post data and checks receipt of the upload
	 * @return boolean true if post data is valid and file has been properly uploaded, false if not
	 */
	public function validate($file_model_name) {
		$post_ok = isset($this->params['form'][$this->file_post_name]);
		$upload_error = $this->params['form'][$this->file_post_name]['error'];
		$temp_name = $this->params['form'][$this->file_post_name]['tmp_name'];
		$got_data = (is_uploaded_file($temp_name));
		if($file_model_name=="Product"){
			list($width,$height,$type,$attr) = getimagesize($temp_name);
//            $radio =$height>0? $width/$height:0;
			if($width!=800||$height!=400){
				$this->setError(2100,'Validation faild','您上传的图片尺寸为：'.$width.'x'.$height.'上传图片尺寸必须为800x600');
				return false;
			}
		}
		if (!$post_ok){
			$this->setError(2000, 'Validation failed.', 'Expected file upload field to be named "Filedata."');
		}
		if ($upload_error){
			$this->setError(2500, 'Validation failed.', $this->getUploadErrorMessage($upload_error));
		}
		return !$upload_error && $post_ok && $got_data;
	}
	
	/**
	 * parses file upload error code into human-readable phrase.
	 * @param int $err PHP file upload error constant.
	 * @return string human-readable phrase to explain issue.
	 */
	public function getUploadErrorMessage($err) {
		$msg = null;
		switch ($err) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
				$msg = ('The uploaded file exceeds the upload_max_filesize directive ('.ini_get('upload_max_filesize').') in php.ini.');
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$msg = ('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
				break;
			case UPLOAD_ERR_PARTIAL:
				$msg = ('The uploaded file was only partially uploaded.');
				break;
			case UPLOAD_ERR_NO_FILE:
				$msg = ('No file was uploaded.');
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$msg = ('The remote server has no temporary folder for file uploads.');
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$msg = ('Failed to write file to disk.');
				break;
			default:
				$msg = ('Unknown File Error. Check php.ini settings.');
		}
		
		return $msg;
	}
	
	/**
	 * sets an error code which can be referenced if failure is detected by controller.
	 * note: the amount of info stored in message depends on debug level.
	 * @param int $code a unique error number for the message and debug info
	 * @param string $message a simple message that you would show the user
	 * @param string $debug any specific debug info to include when debug mode > 1
	 * @return bool true unless an error occurs
	 */
	private function setError($code = 1, $message = 'An unknown error occured.', $debug = '') {
		$this->errorCode = $code;
		$this->errorMessage = $message;
		if (DEBUG) {
			$this->errorMessage .= $debug;
		}
		return true;
	}
	
	/*private $mime_array =  array( 
         "ez" => "application/andrew-inset", 
         "hqx" => "application/mac-binhex40", 
         "cpt" => "application/mac-compactpro", 
         "doc" => "application/msword", 
         "bin" => "application/octet-stream", 
         "dms" => "application/octet-stream", 
         "lha" => "application/octet-stream", 
         "lzh" => "application/octet-stream", 
         "exe" => "application/octet-stream", 
         "class" => "application/octet-stream", 
         "so" => "application/octet-stream", 
         "dll" => "application/octet-stream", 
         "oda" => "application/oda", 
         "pdf" => "application/pdf", 
         "ai" => "application/postscript", 
         "eps" => "application/postscript", 
         "ps" => "application/postscript", 
         "smi" => "application/smil", 
         "smil" => "application/smil", 
         "wbxml" => "application/vnd.wap.wbxml", 
         "wmlc" => "application/vnd.wap.wmlc", 
         "wmlsc" => "application/vnd.wap.wmlscriptc", 
         "bcpio" => "application/x-bcpio", 
         "vcd" => "application/x-cdlink", 
         "pgn" => "application/x-chess-pgn", 
         "cpio" => "application/x-cpio", 
         "csh" => "application/x-csh", 
         "dcr" => "application/x-director", 
         "dir" => "application/x-director", 
         "dxr" => "application/x-director", 
         "dvi" => "application/x-dvi", 
         "spl" => "application/x-futuresplash", 
         "gtar" => "application/x-gtar", 
         "hdf" => "application/x-hdf", 
         "js" => "application/x-javascript", 
         "skp" => "application/x-koan", 
         "skd" => "application/x-koan", 
         "skt" => "application/x-koan", 
         "skm" => "application/x-koan", 
         "latex" => "application/x-latex", 
         "nc" => "application/x-netcdf", 
         "cdf" => "application/x-netcdf", 
         "sh" => "application/x-sh", 
         "shar" => "application/x-shar", 
         "swf" => "application/x-shockwave-flash", 
         "sit" => "application/x-stuffit", 
         "sv4cpio" => "application/x-sv4cpio", 
         "sv4crc" => "application/x-sv4crc", 
         "tar" => "application/x-tar", 
         "tcl" => "application/x-tcl", 
         "tex" => "application/x-tex", 
         "texinfo" => "application/x-texinfo", 
         "texi" => "application/x-texinfo", 
         "t" => "application/x-troff", 
         "tr" => "application/x-troff", 
         "roff" => "application/x-troff", 
         "man" => "application/x-troff-man", 
         "me" => "application/x-troff-me", 
         "ms" => "application/x-troff-ms", 
         "ustar" => "application/x-ustar", 
         "src" => "application/x-wais-source", 
         "xhtml" => "application/xhtml+xml", 
         "xht" => "application/xhtml+xml", 
         "zip" => "application/zip", 
         "au" => "audio/basic", 
         "snd" => "audio/basic", 
         "mid" => "audio/midi", 
         "midi" => "audio/midi", 
         "kar" => "audio/midi", 
         "mpga" => "audio/mpeg", 
         "mp2" => "audio/mpeg", 
         "mp3" => "audio/mpeg", 
         "aif" => "audio/x-aiff", 
         "aiff" => "audio/x-aiff", 
         "aifc" => "audio/x-aiff", 
         "m3u" => "audio/x-mpegurl", 
         "ram" => "audio/x-pn-realaudio", 
         "rm" => "audio/x-pn-realaudio", 
         "rpm" => "audio/x-pn-realaudio-plugin", 
         "ra" => "audio/x-realaudio", 
         "wav" => "audio/x-wav", 
         "pdb" => "chemical/x-pdb", 
         "xyz" => "chemical/x-xyz", 
         "bmp" => "image/bmp", 
         "gif" => "image/gif", 
         "ief" => "image/ief", 
         "jpeg" => "image/jpeg", 
         "jpg" => "image/jpeg", 
         "jpe" => "image/jpeg", 
         "png" => "image/png", 
         "tiff" => "image/tiff", 
         "tif" => "image/tif", 
         "djvu" => "image/vnd.djvu", 
         "djv" => "image/vnd.djvu", 
         "wbmp" => "image/vnd.wap.wbmp", 
         "ras" => "image/x-cmu-raster", 
         "pnm" => "image/x-portable-anymap", 
         "pbm" => "image/x-portable-bitmap", 
         "pgm" => "image/x-portable-graymap", 
         "ppm" => "image/x-portable-pixmap", 
         "rgb" => "image/x-rgb", 
         "xbm" => "image/x-xbitmap", 
         "xpm" => "image/x-xpixmap", 
         "xwd" => "image/x-windowdump", 
         "igs" => "model/iges", 
         "iges" => "model/iges", 
         "msh" => "model/mesh", 
         "mesh" => "model/mesh", 
         "silo" => "model/mesh", 
         "wrl" => "model/vrml", 
         "vrml" => "model/vrml", 
         "css" => "text/css", 
         "html" => "text/html", 
         "htm" => "text/html", 
         "asc" => "text/plain", 
         "txt" => "text/plain", 
         "rtx" => "text/richtext", 
         "rtf" => "text/rtf", 
         "sgml" => "text/sgml", 
         "sgm" => "text/sgml", 
         "tsv" => "text/tab-seperated-values", 
         "wml" => "text/vnd.wap.wml", 
         "wmls" => "text/vnd.wap.wmlscript", 
         "etx" => "text/x-setext", 
         "xml" => "text/xml", 
         "xsl" => "text/xml", 
         "mpeg" => "video/mpeg", 
         "mpg" => "video/mpeg", 
         "mpe" => "video/mpeg", 
         "qt" => "video/quicktime", 
         "mov" => "video/quicktime", 
         "mxu" => "video/vnd.mpegurl", 
         "avi" => "video/x-msvideo", 
         "movie" => "video/x-sgi-movie", 
         "ice" => "x-conference-xcooltalk" 
      ); */
}
?>