<?php
/**


//���Сͼ

useage:
$image = new ImageResize($newfilename); //��������ͼ�ļ���
$image->PicDir = 'data/images/thumb_s/'; // ����ͼ���Ŀ¼
$image->TmpName = 'data/images/imgname.jpg';// ԭͼ��ַ
$image->newWidth = $image->newHeight = 100; // ����ͼ���
$image->resize();

*/
class ImageResize {
	var $FileName;
	var $newWidth = 100; // new width
	var $newHeight = 100; //new height
	var $TmpName;
	var $PicDir;  //store uploaded images
	var $ImageQuality = 60;  // image compression (max value 100)

	function ImageResize($FileName='') {
		$this->FileName= $FileName;
	}
	
	function resizefile($src_img,$target_img,$width=100,$height=''){
		$ext = explode(".",$src_img);
		$ext = end($ext);
		$ext = strtolower($ext);
		if(!in_array($ext,array('jpg','gif','png'))){
			return false;
		}
		$this->newWidth = $width;
		$this->newHeight = $height;
		list($width_orig, $height_orig) = getimagesize($src_img);
		
		if(!$width_orig || !$height_orig){
			return false;
		}
		$ratio_orig = $width_orig/$height_orig;
		
		if ($this->newHeight && $this->newWidth/$this->newHeight > $ratio_orig) {
			$this->newWidth = $this->newHeight*$ratio_orig;
		} else {
			$this->newHeight = $this->newWidth/$ratio_orig;
		}
		if (defined('SAE_MYSQL_DB')) {
			$img = new SaeImage();
			$img->setData( file_get_contents($src_img) );
			$img->resize($this->newWidth,$this->newHeight);
			$new_data = $img->exec();
			file_put_contents($target_img, $new_data);
		}
		else{
			$normal  = imagecreatetruecolor($this->newWidth, $this->newHeight);
			if($ext == "jpg") {
				$source = imagecreatefromjpeg($src_img);
			}
			else if($ext == "gif") {
				$source = imagecreatefromgif ($src_img);
			}
			else if($ext == "png"){
				$this->ImageQuality = 9;
				$source = imagecreatefrompng ($src_img);
			}
		
			imagecopyresampled($normal, $source,    0, 0, 0, 0, $this->newWidth, $this->newHeight, $width_orig, $height_orig);
			if($ext == "jpg") {
				imagejpeg($normal,$target_img, "$this->ImageQuality");
			}
			else if($ext == "gif") {
				imagegif ($normal,$target_img, "$this->ImageQuality");
			}
			else if($ext == "png") {
				imagepng ($normal,$target_img, "$this->ImageQuality");
			}
			imagedestroy($source);
		}
	}
	
	function resize() {
		$ext = explode(".",$this->FileName);
		$ext = end($ext);
		$ext = strtolower($ext);
		list($width_orig, $height_orig) = getimagesize($this->TmpName);

		$ratio_orig = $width_orig/$height_orig;

		if ($this->newHeight && $this->newWidth/$this->newHeight > $ratio_orig) {
			$this->newWidth = $this->newHeight*$ratio_orig;
		} else {
			$this->newHeight = $this->newWidth/$ratio_orig;
		}
		if (defined('SAE_MYSQL_DB')) {
			$img = new SaeImage();
			$img->setData( file_get_contents($this->TmpName) );
			$img->resize($this->newWidth,$this->newHeight);
			$new_data = $img->exec();
			file_put_contents($this->PicDir.DS.$this->FileName, $new_data);
		}
		else{
			$normal  = imagecreatetruecolor($this->newWidth, $this->newHeight);
			if($ext == "jpg" || $ext == "jpeg") {
				$source = imagecreatefromjpeg($this->TmpName);
			}
			else if($ext == "gif") {
				$source = imagecreatefromgif ($this->TmpName);
			}
			else if($ext == "png"){
				$this->ImageQuality = 9;
				$source = imagecreatefrompng ($this->TmpName);
			}

			$ret = @imagecopyresampled($normal, $source,    0, 0, 0, 0, $this->newWidth, $this->newHeight, $width_orig, $height_orig);
			imagedestroy($source);
			if($ret){
				if($ext == "jpg" || $ext == "jpeg") {
					imagejpeg($normal, $this->PicDir.DS.$this->FileName, "$this->ImageQuality");
				}
				else if($ext == "gif") {
					imagegif ($normal, $this->PicDir.DS.$this->FileName, "$this->ImageQuality");
				}
				else if($ext == "png") {
					imagepng ($normal, $this->PicDir.DS.$this->FileName, "$this->ImageQuality");
				}
				else{
					return false;
				}
				return true;
			}
			else{
				return false;
			}
		}
	}
}

?>