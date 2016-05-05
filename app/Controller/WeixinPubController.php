<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/25/14
 * Time: 8:57 PM
 */

class WeixinPubController extends AppController {

    static public $appids = array('xirui' => 'wxf70ebdb3150f2d3c');
    static public $keys = array('xirui' => '7dc69f2a8bbe72e16962b9f8fd523820');
    static public $guanzhu_urls = array('xirui' => 'http://mp.weixin.qq.com/s?__biz=MzA3NTU5NjAzOA==&mid=202192254&idx=1&sn=11818de36910d1c3de718197fd33b967&from=pengyoushuo#rd');

    public function login($exId, $currId) {
        $ref = Router::url($_SERVER['REQUEST_URI']);
        if ($this->is_weixin()) {
            $return_uri = urlencode('http://'.WX_HOST.'/weixin_pub/auth?exid='.$exId.'&uid='.$currId);
            $login_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$appids[$exId] . '&redirect_uri=' . $return_uri . "&response_type=code&scope=".WX_OAUTH_USERINFO."&state=0#wechat_redirect";
            $this->redirect($login_url);
        }
    }


    public function follow($exId) {
        $this->autoRender = false;
        $id = $this->currentUser['id'];
        $key = $this->gen_key($exId, $id);
        Cache::write($key, time());
        $this->redirect(self::$guanzhu_urls[$exId]);
    }


    public function followed($exId) {
        $this->autoRender = false;
        $read = Cache::read($this->gen_key($exId, $this->currentUser['id']));
        echo $read > 1;
    }

    /**
     * @param $exId
     * @param $id
     * @return string
     */
    private function gen_key($exId, $id) {
        return key_follow_brand_time($exId, $id);
    }


}