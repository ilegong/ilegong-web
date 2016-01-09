<?php

class PysController extends AppController{


    /**
     * pc首页
     */
    public function index(){
        if($this->RequestHandler->isMobile()){
            $this->redirect('/weshares/index');
        }
        $this->layout=null;
    }

}