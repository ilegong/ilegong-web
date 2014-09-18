<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 9/17/14
 * Time: 10:48 PM
 */
class UploadController extends AppController {
    var $name = 'Uploadfiles';
    var $helpers = array(
        'Html'
    );
    var $components = array(
        'Session',
        'SwfUpload'
    );

    function upload() {
        $this->autoRender = false;
        App::import('Vendor', 'UploadHandler', array('file' => 'file.upload/UploadHandler.php'));
        $options = array(
            'upload_dir' => '/tmp/',
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i'
        );

        $upload_handler = new UploadHandler($options);
    }

}