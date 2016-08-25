<?php

/**
 * Created by PhpStorm.
 * User: ellipsis
 * Date: 16/8/24
 * Time: 下午5:11
 */
class Oauth2Controller extends Controller
{
    var $components = array('ApiOauth');

    public function test()
    {
        var_dump($this->ApiOauth->getBaseAccessToken());
        die;
    }

    public function authorize()
    {
        $uid = 123456;
        $res = $this->ApiOauth->authorize($uid);
        var_dump($res);
        die;
    }

    public function refreshtoken()
    {
        $res = $this->ApiOauth->refreshToken("70681cae643e0148c5f100ca93798300594906bf");
        var_dump($res);
        die;
    }


}