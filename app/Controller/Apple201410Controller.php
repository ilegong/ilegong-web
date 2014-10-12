<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */

class Apple201410Controller extends AppController {
    var $name = "Apple201410";

    public function beforeFilter() {
        parent::beforeFilter();
        $this->set('pageTitle', __('分享送苹果'));
    }

    public function award() {

    }

} 