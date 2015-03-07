<?php

class DownloadsController extends AppController{
	public $name = 'Downloads';

	public function download_wx_img(){
		$this->autoRender=false;
		App::uses('CurlDownloader','Lib');
		$wxOauthM = ClassRegistry::init('WxOauth');
		$media_id = $_REQUEST['media_id'];
		$access_token = $wxOauthM->get_base_access_token();
		$source = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$media_id;
		$this->log("download file from wx url is :".$source);
		//download as a temp file
		$dl = new CurlDownloader($source);
		$size = $dl->download();
		//is not error output
		if($dl->getFileName()!='remote.out'){
			if(defined('SAE_MYSQL_DB')){
				$stor = new SaeStorage();
				$download_url = $stor->upload(SAE_STORAGE_UPLOAD_DOMAIN_NAME , $dl->getUploadFileName(), $dl->getFileName(),array(),true);
                if(!$download_url){
                    //retry
                    $download_url = $stor->upload(SAE_STORAGE_UPLOAD_DOMAIN_NAME , $dl->getUploadFileName(), $dl->getFileName());
                }
                unlink($dl->getFileName());
                $this->log('handle_file_upload: final file='. $download_url .', $file-path='. $dl->getFileName() .', $uploaded_file='. $dl->getFileName());
                if($download_url){
                    $this->log('upload file to sae errMsg'.$stor->errMsg.' errNum '.$stor->errNum);
                }
			} else {
				copy($dl->getFileName(),WWW_ROOT.'files/wx-download/'.$dl->getFileName());
				$download_url = '/files/wx-download/'.$dl->getFileName();
				//delete temp file
				unlink($dl->getFileName());
			}
			echo json_encode(array(
				'success'=>true,
				'download_url'=>$download_url
			));
		}else{
			//can't download file from weixin server
            $this->log('upload file fail '.$dl->getResponseStr());
            unlink($dl->getFileName());
			echo json_encode(array(
				'success'=>false,
				'download_url'=>''
			));
		}
	}

}