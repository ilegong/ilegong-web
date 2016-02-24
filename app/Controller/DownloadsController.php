<?php

class DownloadsController extends AppController{
	public $name = 'Downloads';

    private function download_wx_img_aliyun($access_token, $media_id)
    {
        $url = SAE_IMAGES_FILE_PATH . '/download_wx_image?media_id=' . $media_id . '&access_token=' . $access_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if ($result['result']) {
            $image_url = SAE_STATIC_FILE_PATH . '/' . $result['url'];
            //return $result['url'];
            return $image_url;
        }
        $this->log('upload image aliyun fail ' . $response);
        return false;
    }

    public function download_wx_img()
    {
        $this->autoRender = false;
        App::uses('CurlDownloader', 'Lib');
        $wxOauthM = ClassRegistry::init('WxOauth');
        $media_id = $_REQUEST['media_id'];
        $access_token = $wxOauthM->get_base_access_token();
        $aliyun_image_url = $this->download_wx_img_aliyun($access_token, $media_id);
        if ($aliyun_image_url) {
            echo json_encode(array('success' => true, 'download_url' => $aliyun_image_url));
            return;
        }
        $source = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=' . $access_token . '&media_id=' . $media_id;
        $this->log("download file from wx url is :" . $source);
        //download as a temp file
        $dl = new CurlDownloader($source);
        $size = $dl->download();
        //is not error output
        if ($dl->getFileName() != 'remote.out') {
            if (defined('SAE_MYSQL_DB')) {
                $stor = new SaeStorage();
                $download_url = $stor->upload(SAE_STORAGE_UPLOAD_DOMAIN_NAME, $dl->getUploadFileName(), $dl->getFileName());
                if (!$download_url) {
                    //retry
                    $download_url = $stor->upload(SAE_STORAGE_UPLOAD_DOMAIN_NAME, $dl->getUploadFileName(), $dl->getFileName());
                }
                unlink($dl->getFileName());
                $this->log('handle_file_upload: final file=' . $download_url . ', $file-path=' . $dl->getFileName() . ', $uploaded_file=' . $dl->getFileName());
                if (!$download_url) {
                    $this->log('upload file to sae errMsg');
                }
            } else {
                //todo fix bug
                copy($dl->getFileName(), WWW_ROOT . 'files/wx-download/' . $dl->getFileName());
                $download_url = '/files/wx-download/' . $dl->getFileName();
                //delete temp file
                unlink($dl->getFileName());
            }
            echo json_encode(array(
                'success' => true,
                'download_url' => $download_url
            ));
        } else {
            //can't download file from weixin server
            $this->log('upload file fail ' . $dl->getResponseStr());
            unlink($dl->getFileName());
            echo json_encode(array(
                'success' => false,
                'download_url' => ''
            ));
        }
    }



}