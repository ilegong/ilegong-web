<?php
/**
 * 二维码服务.
 *
 * @author 979137@qq.com
 * @copyright ©2015, Sina App Engine.
 * @version $Id$
 */
class SaeQRcode {
    private $errMsg = 'success';
    private $errNum = 0;

    //二维码配置参数
    public $data    = '';
    public $level   = 'M';
    public $width   = 200;
    public $height  = 200;
    public $margin  = 0;
    public $icon    = '';
    public $saveUrl = '';

    //生成的二维码文件
    private $code   = '';

    /**
     * 生成二维码图片
     *
     * @desc
     *
     * @access public
     * @return void
     * @exception none
     */
    public function build() {
        static $qrcode = false;
        if (!$qrcode) {
            include(__DIR__.'/phpqrcode.php');
            $qrcode = true;
        }
        if (trim($this->data) == '') {
            $this->errNum = -1;
            $this->errMsg = 'data cannot be empty!';
            return false;
        } elseif (!in_array($this->level, array('L','M','Q','H'))) {
            $this->errNum = -2;
            $this->errMsg = 'level optional values: L, M, Q, H';
            return false;
        } elseif (!is_numeric($this->width) || !is_numeric($this->height)) {
            $this->errNum = -3;
            $this->errMsg = 'width and height parameter error';
            return false;
        }
        $this->code = $this->$saveUrl . md5((microtime(true)*10000).uniqid(time())) . '.png';
        try {
            defined('QRCODE_IMG_W') or define('QRCODE_IMG_W', $this->width);
            defined('QRCODE_IMG_H') or define('QRCODE_IMG_H', $this->height);
            QRcode::png($this->data, $this->code, $this->level, 3, $this->margin);
        } catch(Exception $e) {
            $this->errNum = -4;
            $this->errMsg = $e->getMessage();
            return false;
        }
        if (trim($this->icon) != '') {
            return $this->iconCover() ? $this->code : false;
        }
        return $this->code;
    }

    /**
     * icon覆盖
     *
     * @desc
     *
     * @access public
     * @return boolean
     * @exception none
     */
    public function iconCover() {
        if (!is_file($this->code) || $this->fileType($this->code) != 'png') {
            $this->errNum = -10;
            $this->errMsg = 'QRcode file does not exist or file type is not supported(Only allow PNG)';
            return false;
        }
        //远程icon，先下载到本地
        if (filter_var($this->icon, FILTER_VALIDATE_URL)) {
            //TODO..
        }
        if (!is_file($this->icon) || !in_array($this->fileType($this->icon), array('png','jpg','gif'))) {
            $this->errNum = -11;
            $this->errMsg = 'icon file does not exist or file type is not supported(Only allow PNG,JPG,GIF)';
            return false;
        }
        $codeData = file_get_contents($this->code);
        $iconData = file_get_contents($this->icon);
        $code = imagecreatefromstring($codeData);
        $icon = imagecreatefromstring($iconData);
        list($code_w, $code_h) = array(imagesx($code), imagesy($code));
        list($icon_w, $icon_h) = array(imagesx($icon), imagesy($icon));
        //目标宽高（等比例缩小）
        $icon_code_w = $code_w / 5;
        $scale = $icon_w / $icon_code_w;
        $icon_code_h = $icon_h / $scale;
        //目标XY坐标（将icon置于二维码正中间）
        $dst_x = ($code_w - $icon_code_w) / 2;
        $dst_y = ($code_h - $icon_code_h) / 2;
        imagecopyresampled($code, $icon, $dst_x, $dst_y, 0, 0, $icon_code_w, $icon_code_h, $icon_w, $icon_h);
        return imagepng($code, $this->code);
    }

    /**
     * 取二进制文件头快速准确判断文件类型
     *
     * @desc
     *
     * @access public
     * @params $file 要判断的文件，支持相对和绝对路径
     * @return void
     * @exception none
     */
    public function fileType($file) {
        $filepath = realpath($file);
        $filetype = array(
            7790=>'exe', 7784=>'midi',
            8075=>'zip', 8297=>'rar',
            7173=>'gif', 6677=>'bmp', 13780=>'png', 255216=>'jpg'
        );
        if (!($fp = @fopen($filepath, 'rb'))) return false;
        $bin = fread($fp, 2);
        fclose($fp);
        $str_info = @unpack('C2chars', $bin);
        $str_code = intval($str_info['chars1'].$str_info['chars2']);
        return isset($filetype[$str_code]) ? $filetype[$str_code] : false;
    }

    /**
     * 获取错误信息
     *
     * @desc
     *
     * @access public
     * @return string
     * @exception none
     */
    public function errmsg() {
        $ret = $this->errMsg;
        $this->errMsg = 'Success';
        return $ret;
    }

    /**
     * 获取错误码
     *
     * @desc
     *
     * @access public
     * @return int
     * @exception none
     */
    public function errno() {
        $ret = $this->errNum;
        $this->errNum = 0;
        return $ret;
    }
}