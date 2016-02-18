<?php

class FileUploadComponent extends Component {

    /**
     * @param $base64_data
     * @param $name
     * @return array
     */
    public function save_base64_data($base64_data, $name) {
        if (defined('SAE_MYSQL_DB')) {
            $stor = new SaeStorage();
            $stor->write(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $name, $base64_data);
            $download_url = $stor->getUrl(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $name);
            if (!$download_url) {
                //retry
                $stor->write(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $name, $base64_data);
                $download_url = $stor->getUrl(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $name);
            }
            $this->log('handle_file_upload: final file=' . $download_url);
            if (!$download_url) {
                $this->log('upload file to sae errMsg');
            }
        } else {
            $myfile = fopen(WWW_ROOT . 'files/wx-download/' . $name, "w");
            fwrite($myfile, $base64_data);
            $download_url = 'http://' . WX_HOST . '/files/wx-download/' . $name;
            //delete temp file
        }
        return array('download_url' => $download_url, 'success' => true);
    }

}