<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/9/15
 * Time: 10:27
 */

class PromotionCodeController extends AppController{

    var $name = 'PromotionCode';
    var $uses = array('PromotionCode');

    public function admin_gen_promotion_code(){
        $this->autoRender = false;
        $type = $_REQUEST['code_type'];
        $num = $_REQUEST['num'];
        $product_id= $_REQUEST['pid'];
        $price = $_REQUEST['price'];
        $saveData = array();
        for($i=0; $i<$num; $i++){
            $code = $this->getToken(8);
            $saveData[] = array('code_type' => $type, 'code' => $code, 'gen_datetime' => date('Y-m-d H:i:s'),'product_id' => $product_id,'price'=>$price);
        }
        $this->PromotionCode->saveAll($saveData);
        echo json_encode(array('success'=>true));
        return;
    }


    private function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

   private function getToken($length){
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i=0;$i<$length;$i++){
            $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
        }
        return $token;
    }

}